<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Division;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'orders' => Order::query()->with(['items', 'shipment'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10),
            'addresses' => $user->addresses()->with(['division', 'district', 'upazila'])->latest()->get(),
            'reviewableItems' => OrderItem::query()->with('order')
                ->whereHas('order', fn ($query) => $query->where('user_id', $user->id)->where('status', 'delivered'))
                ->whereDoesntHave('review')
                ->latest()
                ->limit(10)
                ->get(),
            'returns' => $user->returnRequests()->with(['order', 'orderItem'])->latest()->limit(10)->get(),
            'divisions' => Division::query()->orderBy('name')->get(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'district_id' => [
                'required',
                'integer',
                Rule::exists('districts', 'id')->where('division_id', $request->input('division_id')),
            ],
            'upazila_id' => [
                'required',
                'integer',
                Rule::exists('upazilas', 'id')->where('district_id', $request->input('district_id')),
            ],
            'address' => ['required', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $data): void {
            $user = $request->user();
            $isDefault = (bool) ($data['is_default'] ?? false) || ! $user->addresses()->exists();
            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([...$data, 'is_default' => $isDefault]);
        });

        return back()->with('status', 'ঠিকানা সংরক্ষণ করা হয়েছে।');
    }

    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->oldest()->first()?->update(['is_default' => true]);
        }

        return back()->with('status', 'ঠিকানা মুছে ফেলা হয়েছে।');
    }

    public function review(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $item = OrderItem::query()->with('order')
            ->whereKey($orderItem->id)
            ->whereDoesntHave('review')
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('status', 'delivered'))
            ->firstOrFail();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:3000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photoPath = isset($data['photo']) ? $data['photo']->store('reviews', 'public') : null;
        try {
            Review::create([
                'product_id' => $item->product_id,
                'user_id' => $request->user()->id,
                'order_item_id' => $item->id,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'],
                'photo_path' => $photoPath,
            ]);
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }

        return back()->with('status', 'রিভিউ জমা হয়েছে। অনুমোদনের পর এটি পণ্যের পাতায় দেখা যাবে।');
    }

    public function returnRequest(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $item = OrderItem::query()->with('order')
            ->whereKey($orderItem->id)
            ->whereDoesntHave('returnRequest')
            ->whereHas('order', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('status', 'delivered'))
            ->firstOrFail();

        $deliveredAt = $item->order->statusHistory()
            ->where('status', 'delivered')
            ->latest('created_at')
            ->value('created_at');
        abort_unless($deliveredAt && Carbon::parse($deliveredAt)->betweenIncluded(now()->subDays(7), now()), 422, 'ডেলিভারির ৭ দিনের মধ্যে exchange অনুরোধ করতে হবে।');

        $data = $request->validate([
            'reason' => ['required', Rule::in(['wrong_size', 'defective', 'wrong_item', 'other'])],
            'details' => ['nullable', 'string', 'max:2000'],
            'requested_action' => ['required', Rule::in(['exchange', 'return'])],
        ]);

        ReturnRequest::create([
            'order_id' => $item->order_id,
            'order_item_id' => $item->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return back()->with('status', 'আপনার exchange / return অনুরোধ জমা হয়েছে।');
    }

    public function trackForm(): View
    {
        return view('account.track');
    }

    public function track(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $order = Order::query()->with(['items', 'statusHistory', 'shipment'])
            ->where('order_number', mb_strtoupper(trim($data['order_number'])))
            ->where('customer_phone', $data['phone'])
            ->first();
        if (! $order) {
            return back()->withErrors(['order_number' => 'এই অর্ডার নম্বর ও ফোন নম্বরের কোনো অর্ডার পাওয়া যায়নি।'])->withInput();
        }

        return view('account.track-result', compact('order'));
    }
}

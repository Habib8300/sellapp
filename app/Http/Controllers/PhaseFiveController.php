<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteRequest;
use App\Support\CartSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhaseFiveController extends Controller
{
    public function teamForm(): View
    {
        return view('team-orders.create', [
            'products' => Product::query()->with(['variants' => fn ($query) => $query->where('is_active', true)])
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function teamOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rows' => ['required', 'array', 'min:1', 'max:50'],
            'rows.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'rows.*.name' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z ]*$/'],
            'rows.*.number' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $product = Product::query()
            ->whereKey($data['product_id'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();
        $rows = collect($data['rows'])->filter(fn (array $row): bool => filled($row['variant_id'] ?? null));
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['rows' => 'অন্তত একটি জার্সির সাইজ নির্বাচন করুন।']);
        }

        $variantIds = $rows->pluck('variant_id')->unique();
        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');
        if ($variants->count() !== $variantIds->count()) {
            throw ValidationException::withMessages(['rows' => 'নির্বাচিত সাইজটি এই ডিজাইনের জন্য বৈধ নয়।']);
        }

        $quantities = $rows->countBy('variant_id');
        foreach ($quantities as $variantId => $quantity) {
            $variant = $variants->get($variantId);
            $preorderAvailable = $product->is_preorder
                && (! $product->preorder_close_at || $product->preorder_close_at->isToday() || $product->preorder_close_at->isFuture());
            if (! $preorderAvailable && $variant->stock_quantity < $quantity) {
                throw ValidationException::withMessages(['rows' => "সাইজ {$variant->size} এর পর্যাপ্ত স্টক নেই।"]);
            }
        }

        $cart = CartSession::lines($request);
        foreach ($rows as $row) {
            $variant = $variants->get((int) $row['variant_id']);
            $name = mb_strtoupper(trim((string) ($row['name'] ?? '')));
            $number = $row['number'] ?? null;
            $customization = [];
            if ($name !== '' || $number !== null) {
                if (! $product->customization_enabled || $name === '' || $number === null) {
                    throw ValidationException::withMessages(['rows' => 'প্রতিটি কাস্টম প্রিন্টের জন্য নাম ও নম্বর দুটিই দিন।']);
                }
                $customization = ['name' => $name, 'number' => (int) $number, 'font' => 'classic', 'color' => 'white'];
            }

            $key = CartSession::key($variant->id, $customization);
            if (isset($cart[$key])) {
                $cart[$key]['quantity']++;
            } else {
                $cart[$key] = [
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                    'customization' => $customization,
                ];
            }
        }
        CartSession::save($request, $cart);

        return redirect()->route('cart.index')->with('status', 'টিম অর্ডার কার্টে যোগ হয়েছে। পরিমাণ অনুযায়ী বাল্ক ছাড় চেকআউটে হিসাব হবে।');
    }

    public function campaign(Request $request, string $slug): View|RedirectResponse
    {
        $campaign = Campaign::query()->with('product')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
        abort_if($campaign->deadline->isPast(), 410, 'এই ক্যাম্পেইনের সময়সীমা শেষ হয়েছে।');

        $request->session()->put('campaign_id', $campaign->id);

        return view('campaigns.show', ['campaign' => $campaign]);
    }

    public function quoteForm(): View
    {
        return view('quotes.create');
    }

    public function quote(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'department' => ['nullable', 'string', 'max:160'],
            'batch' => ['nullable', 'string', 'max:80'],
            'quantity' => ['required', 'integer', 'min:50', 'max:10000'],
            'details' => ['nullable', 'string', 'max:3000'],
        ]);

        QuoteRequest::create($data);

        return redirect()->route('quotes.create')->with('status', 'আপনার কোট অনুরোধ জমা হয়েছে। আমাদের টিম যোগাযোগ করবে।');
    }

    public function studentVerificationForm(): View
    {
        return view('student-verification.create', ['user' => Auth::user()]);
    }

    public function studentVerification(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->student_verified, 403, 'আপনার শিক্ষার্থী পরিচয় ইতোমধ্যে যাচাই করা হয়েছে।');

        $data = $request->validate([
            'student_institution' => ['required', 'string', 'max:160'],
            'student_email' => ['required', 'email', 'max:255'],
        ]);

        $user->update([
            ...$data,
            'student_verification_submitted_at' => Date::now(),
        ]);

        return redirect()->route('student-verification.create')->with('status', 'যাচাইয়ের অনুরোধ জমা হয়েছে। অ্যাডমিন অনুমোদনের পর শিক্ষার্থী ছাড় পাবেন।');
    }

    public function productionSheet(): StreamedResponse
    {
        abort_unless(Auth::user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']), 403);

        $orders = Order::query()
            ->with('items')
            ->whereIn('status', ['confirmed', 'in_production'])
            ->whereHas('items', fn ($query) => $query->whereNotNull('customization'))
            ->orderBy('created_at')
            ->get();

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Unable to open production export stream.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Order', 'Customer', 'Phone', 'Product', 'SKU', 'Size', 'Color', 'Quantity', 'Print name', 'Number', 'Font', 'Print color']);
            $safeCell = static function (mixed $value): string {
                $value = (string) $value;

                return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
            };
            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    if (! $item->customization) {
                        continue;
                    }
                    fputcsv($output, [
                        $order->order_number,
                        $safeCell($order->customer_name),
                        $safeCell($order->customer_phone),
                        $safeCell($item->product_name),
                        $safeCell($item->product_sku),
                        $safeCell($item->size),
                        $safeCell($item->color),
                        $item->quantity,
                        $safeCell($item->customization['name'] ?? ''),
                        $item->customization['number'] ?? '',
                        $safeCell($item->customization['font'] ?? ''),
                        $safeCell($item->customization['color'] ?? ''),
                    ]);
                }
            }
            fclose($output);
        }, 'production-sheet-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

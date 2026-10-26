<?php

namespace App\Http\Controllers;

use App\Models\BulkDiscountSlab;
use App\Models\District;
use App\Models\Division;
use App\Models\Order;
use App\Models\Setting;
use App\Services\PlaceOrder;
use App\Support\CartContents;
use App\Support\CartSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function create(Request $request, CartContents $contents): View|RedirectResponse
    {
        $lines = CartSession::lines($request);
        if ($lines === []) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'চেকআউটের আগে কার্টে পণ্য যোগ করুন।']);
        }

        $cart = $contents->forRequest($request);
        if ($cart['items']->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'কার্টের পণ্য আর পাওয়া যাচ্ছে না।']);
        }

        return view('checkout.create', [
            'items' => $cart['items'],
            'subtotal' => $cart['subtotal'],
            'divisions' => Division::query()->orderBy('name')->get(),
            'deliveryInsideDhaka' => (float) (Setting::query()->where('key', 'delivery_inside_dhaka')->value('value') ?: 70),
            'deliveryOutsideDhaka' => (float) (Setting::query()->where('key', 'delivery_outside_dhaka')->value('value') ?: 130),
            'dhakaDistrictId' => District::query()->where('name', 'Dhaka')->value('id'),
            'paymentInstructions' => [
                'bkash' => Setting::query()->where('key', 'payment_bkash_instructions')->value('value'),
                'nagad' => Setting::query()->where('key', 'payment_nagad_instructions')->value('value'),
                'rocket' => Setting::query()->where('key', 'payment_rocket_instructions')->value('value'),
            ],
            'advancePaymentPercent' => (float) (Setting::query()->where('key', 'advance_payment_percent')->value('value') ?: 50),
            'canPayAdvance' => $cart['items']->contains(
                fn (array $item): bool => $item['customization'] !== [] || $item['variant']->product->is_preorder,
            ),
            'bulkDiscount' => BulkDiscountSlab::query()->where('is_active', true)
                ->where('minimum_quantity', '<=', $cart['items']->sum('quantity'))
                ->orderByDesc('minimum_quantity')
                ->first(),
            'studentVerified' => Auth::user()?->student_verified ?? false,
            'studentDiscountPercent' => (float) (Setting::query()->where('key', 'student_discount_percent')->value('value') ?: 0),
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'fulfillment_method' => ['required', Rule::in(['delivery', 'campus_pickup'])],
            'division_id' => ['nullable', 'required_if:fulfillment_method,delivery', 'integer', 'exists:divisions,id'],
            'district_id' => ['nullable', 'required_if:fulfillment_method,delivery', 'integer', 'exists:districts,id'],
            'upazila_id' => ['nullable', 'required_if:fulfillment_method,delivery', 'integer', 'exists:upazilas,id'],
            'delivery_address' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:500'],
            'pickup_point' => ['nullable', 'required_if:fulfillment_method,campus_pickup', 'string', 'max:180'],
            'payment_method' => ['required', Rule::in(['cod', 'bkash', 'nagad', 'rocket'])],
            'payment_plan' => ['sometimes', Rule::in(['full', 'advance'])],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'order_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['payment_plan'] ??= 'full';
        $data['campaign_id'] = $request->session()->get('campaign_id');

        if ($data['payment_method'] === 'cod' && filled($data['transaction_reference'] ?? null)) {
            return back()->withErrors(['transaction_reference' => 'ক্যাশ অন ডেলিভারির জন্য ট্রানজেকশন আইডি প্রয়োজন নেই।'])->withInput();
        }
        if ($data['payment_method'] === 'cod' && $data['payment_plan'] === 'advance') {
            return back()->withErrors(['payment_plan' => 'ক্যাশ অন ডেলিভারিতে অগ্রিম পেমেন্ট নির্বাচন করা যাবে না।'])->withInput();
        }

        $result = $placeOrder->handle($data, CartSession::lines($request));
        $tokens = $request->session()->get('order_access_tokens', []);
        $tokens[$result['order']->order_number] = $result['access_token'];
        $request->session()->put('order_access_tokens', $tokens);
        CartSession::save($request, []);
        $request->session()->forget('campaign_id');

        return redirect()->route('orders.show', ['orderNumber' => $result['order']->order_number]);
    }

    public function show(Request $request, string $orderNumber): View
    {
        $order = Order::query()->with(['items', 'payments'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();
        $this->authorizeOrder($request, $order);

        return view('checkout.complete', [
            'order' => $order,
            'paymentInstructions' => Setting::query()
                ->where('key', 'payment_'.$order->payment_method.'_instructions')
                ->value('value'),
        ]);
    }

    public function invoice(Request $request, string $orderNumber): Response
    {
        $order = Order::query()->with(['items', 'payments'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();
        $this->authorizeOrder($request, $order);

        $fontDirectory = public_path('fonts/hind-siliguri');
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $defaultFontConfig = (new FontVariables)->getDefaults();

        File::ensureDirectoryExists(storage_path('app/mpdf'));

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf'),
            'fontDir' => [...$defaultConfig['fontDir'], $fontDirectory],
            'fontdata' => $defaultFontConfig['fontdata'] + [
                'hindsiliguri' => [
                    'R' => 'HindSiliguri-Regular.ttf',
                    'B' => 'HindSiliguri-Bold.ttf',
                    'useOTL' => 0xFF,
                ],
            ],
            'default_font' => 'hindsiliguri',
        ]);

        $pdf->WriteHTML(view('checkout.invoice', [
            'order' => $order,
            'brandName' => Setting::query()->where('key', 'brand_name')->value('value') ?: 'Dhakebir Jersey House',
            'brandNameBangla' => Setting::query()->where('key', 'brand_name_bn')->value('value') ?: 'ঢাকেবির জার্সি হাউজ',
        ])->render());

        return response($pdf->Output('invoice-'.$order->order_number.'.pdf', Destination::STRING_RETURN))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-'.$order->order_number.'.pdf"');
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        if (Auth::id() && $order->user_id === Auth::id()) {
            return;
        }

        $token = $request->session()->get('order_access_tokens.'.$order->order_number);
        abort_unless(is_string($token) && Hash::check($token, (string) $order->guest_access_token_hash), 403);
    }
}

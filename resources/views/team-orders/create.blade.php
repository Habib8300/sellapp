@extends('layouts.store')

@section('title', 'টিম অর্ডার')

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold">টিম / ব্যাচ অর্ডার</h1>
        <p class="mt-2 text-gray-600">একটি ডিজাইন বেছে নিয়ে প্রত্যেকের সাইজ, নাম ও নম্বর যোগ করুন। ৫০টির বেশি হলে কোট নিন।</p>
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('team-orders.store', absolute: false) }}" x-data="teamOrderForm()" class="mt-6 space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            <div>
                <label for="team-product" class="text-sm font-medium">ডিজাইন *</label>
                <select id="team-product" name="product_id" x-model="productId" required class="mt-1 w-full rounded-lg border-gray-300">
                    <option value="">ডিজাইন বেছে নিন</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name_bn ?: $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold">সদস্যদের তালিকা</h2>
                <div class="flex gap-2">
                    <label for="team-csv" class="cursor-pointer rounded-lg border border-gray-300 px-3 py-2 text-sm">CSV আপলোড</label>
                    <input id="team-csv" type="file" accept=".csv,text/csv" class="sr-only" @change="importCsv($event)">
                    <button type="button" @click="addRow()" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold">+ সদস্য</button>
                </div>
            </div>
            <p class="text-xs text-gray-500">CSV কলাম: name, number, variant_id. সাইজ/রঙের Variant ID অ্যাডমিন ক্যাটালগ থেকে পাওয়া যাবে।</p>
            <template x-for="(row, index) in rows" :key="row.id">
                <div class="grid gap-3 rounded-xl border border-gray-200 p-3 sm:grid-cols-[1fr_1fr_1fr_auto]">
                    <div>
                        <label class="text-xs text-gray-600">সাইজ / রঙ *</label>
                        <select :name="`rows[${index}][variant_id]`" x-model="row.variant_id" required class="mt-1 w-full rounded-lg border-gray-300 text-sm">
                            <option value="">ভ্যারিয়েন্ট বেছে নিন</option>
                            <template x-for="variant in availableVariants" :key="variant.id">
                                <option :value="variant.id" x-text="`ID ${variant.id}: ${variant.size} / ${variant.color} (${variant.stock} স্টক)`"></option>
                            </template>
                        </select>
                    </div>
                    <div><label class="text-xs text-gray-600">প্রিন্ট নাম (ঐচ্ছিক)</label><input :name="`rows[${index}][name]`" x-model="row.name" maxlength="12" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></div>
                    <div><label class="text-xs text-gray-600">প্রিন্ট নম্বর (ঐচ্ছিক)</label><input :name="`rows[${index}][number]`" x-model="row.number" type="number" min="0" max="99" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></div>
                    <button type="button" @click="removeRow(index)" :disabled="rows.length === 1" class="self-end rounded-lg px-3 py-2 text-sm text-red-700 disabled:opacity-40">সরান</button>
                </div>
            </template>
            <p class="text-xs text-gray-500">প্রিন্ট চাইলে নাম ও নম্বর দুটিই দিতে হবে; খালি রাখলে কাস্টম প্রিন্ট যোগ হবে না।</p>
            <button class="w-full rounded-xl bg-[#771115] px-5 py-3 font-bold text-white hover:bg-[#5d0d11]">টিম অর্ডার কার্টে যোগ করুন</button>
        </form>
    </section>
    <script>
        function teamOrderForm() {
            const products = @js($products->map(fn ($product) => [
                'id' => $product->id,
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'stock' => $variant->stock_quantity,
                ])->values(),
            ])->values());
            return {
                productId: @js(old('product_id', '')),
                rows: [{ id: 1, variant_id: '', name: '', number: '' }],
                nextId: 2,
                get availableVariants() {
                    return products.find((product) => String(product.id) === String(this.productId))?.variants ?? [];
                },
                addRow(values = {}) {
                    this.rows.push({ id: this.nextId++, variant_id: values.variant_id ?? '', name: values.name ?? '', number: values.number ?? '' });
                },
                removeRow(index) {
                    if (this.rows.length > 1) this.rows.splice(index, 1);
                },
                async importCsv(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const text = await file.text();
                    const rows = text.split(/\r?\n/).filter(Boolean).slice(1).map((line) => {
                        const [name, number, variant_id] = line.split(',').map((value) => value.trim());
                        return { name, number, variant_id };
                    }).filter((row) => row.variant_id);
                    if (rows.length) {
                        this.rows = [];
                        rows.slice(0, 50).forEach((row) => this.addRow(row));
                    }
                    event.target.value = '';
                },
            };
        }
    </script>
@endsection

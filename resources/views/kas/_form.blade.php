@php($transaction = $transaction ?? null)

<div class="space-y-4">
    <div>
        <x-input-label for="category" value="Kategori" />
        <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— pilih kategori —</option>
            @foreach (\App\Models\Transaction::CATEGORIES as $category)
                <option value="{{ $category }}" @selected(old('category', $transaction?->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="amount" value="Nominal (Rp)" />
        <x-text-input id="amount" name="amount" type="number" min="1" class="mt-1 block w-full"
                      :value="old('amount', $transaction?->amount)" required />
        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="transaction_date" value="Tanggal" />
        <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full"
                      :value="old('transaction_date', $transaction?->transaction_date?->toDateString() ?? now()->toDateString())" required />
        <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Keterangan (opsional)" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $transaction?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('kas.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>

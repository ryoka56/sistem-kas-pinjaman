<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Catat Pembayaran</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        @if ($loans->isEmpty())
            <p class="text-sm text-gray-600">Tidak ada pinjaman yang belum lunas.</p>
        @else
            <form method="POST" action="{{ route('repayments.store') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <x-input-label for="loan_id" value="Pinjaman" />
                        <select id="loan_id" name="loan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— pilih pinjaman —</option>
                            @foreach ($loans as $loan)
                                <option value="{{ $loan->id }}" @selected((int) old('loan_id', $selected) === $loan->id)>
                                    {{ $loan->member->name }} — Sisa: Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('loan_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" value="Nominal Pembayaran (Rp)" />
                        <x-text-input id="amount" name="amount" type="number" min="1" class="mt-1 block w-full"
                                      :value="old('amount')" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="payment_date" value="Tanggal Pembayaran" />
                        <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full"
                                      :value="old('payment_date', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('payment_date')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <x-primary-button>Simpan</x-primary-button>
                    <a href="{{ route('loans.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>

@php($locked = $loan->exists && $loan->repayments_count > 0)

<div class="space-y-4">
    <div>
        <x-input-label for="member_id" value="Anggota" />
        <select id="member_id" name="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">— pilih anggota —</option>
            @foreach ($members as $member)
                <option value="{{ $member->id }}" @selected((int) old('member_id', $loan->member_id) === $member->id)>{{ $member->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('member_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="amount" value="Nominal Pinjaman (Rp)" />
        <x-text-input id="amount" name="amount" type="number" min="1"
                      class="mt-1 block w-full {{ $locked ? 'bg-gray-100 text-gray-500' : '' }}"
                      :value="old('amount', $loan->amount)" :readonly="$locked" required />
        @if ($locked)
            <p class="mt-1 text-xs text-amber-600">Nominal tidak dapat diubah karena sudah ada cicilan.</p>
        @endif
        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="loan_date" value="Tanggal Pinjaman" />
            <x-text-input id="loan_date" name="loan_date" type="date" class="mt-1 block w-full"
                          :value="old('loan_date', $loan->loan_date?->toDateString() ?? now()->toDateString())" required />
            <x-input-error :messages="$errors->get('loan_date')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="due_date" value="Jatuh Tempo (opsional)" />
            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                          :value="old('due_date', $loan->due_date?->toDateString())" />
            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
        </div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('loans.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>

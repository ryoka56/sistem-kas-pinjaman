@php($member = $member ?? null)

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $member?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="No. HP (opsional)" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                      :value="old('phone', $member?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="address" value="Alamat (opsional)" />
        <textarea id="address" name="address" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $member?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('members.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>

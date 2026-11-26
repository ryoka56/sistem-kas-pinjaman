<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Anggota</h2>
            <a href="{{ route('members.create') }}"
               class="rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-900">+ Anggota</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2 font-medium">Nama</th>
                    <th class="px-4 py-2 font-medium">No. HP</th>
                    <th class="px-4 py-2 font-medium">Alamat</th>
                    <th class="px-4 py-2 text-center font-medium">Pinjaman</th>
                    <th class="px-4 py-2 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($members as $member)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $member->name }}</td>
                        <td class="px-4 py-2">{{ $member->phone ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ \Illuminate\Support\Str::limit($member->address, 50) ?: '—' }}</td>
                        <td class="px-4 py-2 text-center">{{ $member->loans_count }}</td>
                        <td class="whitespace-nowrap px-4 py-2 text-right">
                            <a href="{{ route('members.edit', $member) }}" class="text-indigo-600 hover:underline">Ubah</a>
                            <form method="POST" action="{{ route('members.destroy', $member) }}" class="inline"
                                  onsubmit="return confirm('Hapus anggota ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="ml-2 text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada anggota.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $members->links() }}</div>
</x-app-layout>

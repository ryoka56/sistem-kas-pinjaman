<?php

namespace App\Http\Controllers;

use App\Exceptions\ActionBlockedException;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Member::withCount('loans')->orderBy('name')->paginate(15);

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        return view('members.create', ['member' => new Member()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Member::create($this->validated($request));

        return redirect()->route('members.index')->with('status', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $member->update($this->validated($request));

        return redirect()->route('members.index')->with('status', 'Anggota berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->loans()->exists()) {
            throw ActionBlockedException::memberHasLoans();
        }

        $member->delete();

        return redirect()->route('members.index')->with('status', 'Anggota berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama anggota wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
        ]);
    }
}

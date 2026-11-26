<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex">
            <aside class="w-60 shrink-0 bg-slate-900 text-slate-300 flex flex-col">
                <div class="px-5 py-5 border-b border-slate-800">
                    <a href="{{ route('dashboard') }}" class="block text-white font-semibold">Kas &amp; Peminjaman</a>
                    <span class="text-xs text-slate-500">Panel Admin</span>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
                    @php
                        $menu = [
                            ['dashboard', 'Dashboard', 'dashboard'],
                            ['kas.index', 'Kas', 'kas.*'],
                            ['loans.index', 'Peminjaman', 'loans.*'],
                            ['repayments.index', 'Pengembalian', 'repayments.*'],
                            ['members.index', 'Anggota', 'members.*'],
                            ['reports.index', 'Laporan Keuangan', 'reports.*'],
                        ];
                    @endphp
                    @foreach ($menu as [$route, $label, $pattern])
                        <a href="{{ route($route) }}"
                           class="block rounded-md px-3 py-2 {{ request()->routeIs($pattern) ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>

                <div class="px-3 py-4 border-t border-slate-800 text-sm space-y-1">
                    <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 hover:bg-slate-800 hover:text-white">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full text-left rounded-md px-3 py-2 hover:bg-slate-800 hover:text-white">Keluar</button>
                    </form>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0">
                <header class="bg-white border-b">
                    <div class="px-6 py-4">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h2 class="text-xl font-semibold text-gray-800">{{ config('app.name') }}</h2>
                        @endisset
                    </div>
                </header>

                <main class="p-6 flex-1">
                    @if (session('status'))
                        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

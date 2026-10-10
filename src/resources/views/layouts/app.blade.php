<!doctype html>
<html lang="ja" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Laravel 開発環境（見本）</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-zinc-50 font-sans text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
    <header class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <nav class="mx-auto flex max-w-4xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3 text-sm">
            <a href="{{ route('home') }}" class="border-l-4 border-amber-700 pl-2 text-lg font-bold">Laravel 見本</a>
            <a href="{{ route('contact.create') }}" class="hover:underline">お問い合わせ</a>
            <a href="{{ route('admin.inquiries.index') }}" class="hover:underline">管理画面</a>
            @auth
                <form method="post" action="{{ route('admin.logout') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="hover:underline">ログアウト（{{ auth()->user()->name }}）</button>
                </form>
            @endauth
        </nav>
    </header>

    <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-6">
        @if (session('message'))
            <p role="status" class="mb-4 rounded-md border-l-4 border-green-700 bg-white px-3 py-2 dark:bg-zinc-900">{{ session('message') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>

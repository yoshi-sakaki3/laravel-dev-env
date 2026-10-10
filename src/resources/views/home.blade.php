@extends('layouts.app')

@section('title', '環境チェック')

@section('content')
    <h1 class="mb-4 text-xl font-bold">Laravel 開発環境チェック</h1>

    <section class="card">
        <h2 class="mb-2 font-bold">PHP・Laravel</h2>
        <p class="font-semibold text-green-700 dark:text-green-400">PHP {{ $phpVersion }} ／ Laravel {{ $laravelVersion }}</p>
        <p class="mt-1 text-sm text-zinc-500">稼働確認：<a class="underline" href="/up">/up</a></p>
    </section>

    <section class="card">
        <h2 class="mb-2 font-bold">データベース（MySQL）</h2>
        <p @class(['font-semibold', 'text-green-700 dark:text-green-400' => $db['ok'], 'text-red-700 dark:text-red-400' => ! $db['ok']])>
            {{ $db['ok'] ? '接続できました' : '接続できません' }}
        </p>
        <p class="mt-1 text-sm break-all text-zinc-500">{{ $db['detail'] }}</p>
        <p class="mt-1 text-sm text-zinc-500">画面で見る：<a class="underline" href="http://localhost:8281">phpMyAdmin</a></p>
    </section>

    <section class="card">
        <h2 class="mb-2 font-bold">見本アプリ（お問い合わせ管理）</h2>
        <ul class="list-disc space-y-1 pl-5">
            <li><a class="underline" href="{{ route('contact.create') }}">お問い合わせフォーム</a>：送信すると DB に保存し、通知メールと自動返信メールを送ります</li>
            <li><a class="underline" href="{{ route('admin.inquiries.index') }}">管理画面</a>：検索・対応状況の絞り込み・社内メモ（ログイン：admin@example.com / password）</li>
            <li><a class="underline" href="http://localhost:8030">Mailpit</a>：送ったメールはここに届きます（外部には送られません）</li>
        </ul>
    </section>
@endsection

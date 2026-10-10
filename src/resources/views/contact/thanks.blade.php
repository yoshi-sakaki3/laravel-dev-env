@extends('layouts.app')

@section('title', '送信しました')

@section('content')
    <div class="card text-center">
        <h1 class="mb-2 text-xl font-bold">お問い合わせを受け付けました</h1>
        <p class="mb-6 text-zinc-600 dark:text-zinc-300">確認のメールをお送りしました。担当者からの返信をお待ちください。</p>
        <a href="{{ route('home') }}" class="btn-secondary">トップに戻る</a>
    </div>
@endsection

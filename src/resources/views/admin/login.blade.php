@extends('layouts.app')

@section('title', '管理画面ログイン')

@section('content')
    <h1 class="mb-4 text-xl font-bold">管理画面ログイン</h1>

    <form method="post" action="{{ route('admin.login.store') }}" class="card max-w-md space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="label">メールアドレス</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="field" autocomplete="username" required autofocus>
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">パスワード</label>
            <input id="password" name="password" type="password" class="field" autocomplete="current-password" required>
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-primary">ログイン</button>
        <p class="text-sm text-zinc-500">見本のログイン：admin@example.com / password（ローカル開発専用）</p>
    </form>
@endsection

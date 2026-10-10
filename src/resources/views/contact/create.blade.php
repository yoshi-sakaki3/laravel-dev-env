@extends('layouts.app')

@section('title', 'お問い合わせ')

@section('content')
    <h1 class="mb-4 text-xl font-bold">お問い合わせ</h1>

    <form method="post" action="{{ route('contact.store') }}" class="card space-y-4" novalidate>
        @csrf

        <div>
            <label for="name" class="label">お名前 <span class="text-red-700 dark:text-red-400">（必須）</span></label>
            <input id="name" name="name" value="{{ old('name') }}" class="field" maxlength="50" autocomplete="name" required
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name') <p id="name-error" class="error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label">メールアドレス <span class="text-red-700 dark:text-red-400">（必須）</span></label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="field" autocomplete="email" required
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email') <p id="email-error" class="error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="body" class="label">お問い合わせ内容 <span class="text-red-700 dark:text-red-400">（必須・2000文字まで）</span></label>
            <textarea id="body" name="body" rows="6" class="field" maxlength="2000" required
                @error('body') aria-invalid="true" aria-describedby="body-error" @enderror>{{ old('body') }}</textarea>
            @error('body') <p id="body-error" class="error">{{ $message }}</p> @enderror
        </div>

        {{-- 迷惑送信の対策：人には見えない欄。機械が埋めてきたら保存しない --}}
        <div class="hidden" aria-hidden="true">
            <label for="website">この欄は空のままにしてください</label>
            <input id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="agree" value="1" class="mt-1 size-4" @checked(old('agree'))>
                <span>入力した内容を、お問い合わせへの回答のために使うことに同意します。</span>
            </label>
            @error('agree') <p class="error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary">送信する</button>
    </form>
@endsection

@extends('layouts.app')

@section('title', 'お問い合わせの詳細')

@section('content')
    <p class="mb-2 text-sm"><a href="{{ route('admin.inquiries.index') }}" class="underline">← 一覧に戻る</a></p>
    <h1 class="mb-4 text-xl font-bold">お問い合わせの詳細</h1>

    <section class="card">
        <dl class="grid gap-x-4 gap-y-2 sm:grid-cols-[8rem_1fr]">
            <dt class="font-semibold">受付日時</dt>
            <dd>{{ $inquiry->created_at->format('Y/m/d H:i') }}</dd>
            <dt class="font-semibold">お名前</dt>
            <dd>{{ $inquiry->name }}</dd>
            <dt class="font-semibold">メールアドレス</dt>
            <dd class="break-all"><a class="underline" href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></dd>
            <dt class="font-semibold">内容</dt>
            <dd class="whitespace-pre-wrap break-words">{{ $inquiry->body }}</dd>
        </dl>
    </section>

    <form method="post" action="{{ route('admin.inquiries.update', $inquiry) }}" class="card space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="status" class="label">対応状況</label>
            <select id="status" name="status" class="field max-w-xs">
                @foreach (App\Enums\InquiryStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('status', $inquiry->status->value) === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            @error('status') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="note" class="label">社内メモ（お客さまには見えません）</label>
            <textarea id="note" name="note" rows="4" class="field" maxlength="2000">{{ old('note', $inquiry->note) }}</textarea>
            @error('note') <p class="error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-primary">保存する</button>
    </form>
@endsection

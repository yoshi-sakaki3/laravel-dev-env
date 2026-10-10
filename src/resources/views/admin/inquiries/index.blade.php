@extends('layouts.app')

@section('title', 'お問い合わせ一覧')

@section('content')
    <h1 class="mb-4 text-xl font-bold">お問い合わせ一覧</h1>

    <form method="get" action="{{ route('admin.inquiries.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <label class="min-w-0 flex-1 basis-60">
            <span class="label">検索（お名前・メールアドレス・内容）</span>
            <input name="q" value="{{ $keyword }}" class="field" placeholder="例：料金">
        </label>
        <label>
            <span class="label">対応状況</span>
            <select name="status" class="field">
                <option value="">すべて</option>
                @foreach (App\Enums\InquiryStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>
                        {{ $case->label() }}（{{ $counts[$case->value] ?? 0 }}）
                    </option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="btn-secondary">絞り込む</button>
    </form>

    <p class="mb-2 text-sm text-zinc-500">{{ $inquiries->total() }} 件</p>

    @if ($inquiries->isEmpty())
        <p class="rounded-md border border-dashed border-zinc-300 p-6 text-center text-zinc-500 dark:border-zinc-600">該当するお問い合わせがありません。</p>
    @else
        <div class="overflow-x-auto rounded-md border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-zinc-100 text-left dark:bg-zinc-800">
                    <tr>
                        <th class="px-3 py-2">受付日時</th>
                        <th class="px-3 py-2">お名前</th>
                        <th class="px-3 py-2">内容</th>
                        <th class="px-3 py-2">対応状況</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inquiries as $inquiry)
                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $inquiry->created_at->format('Y/m/d H:i') }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="font-semibold text-amber-800 hover:underline dark:text-amber-300">{{ $inquiry->name }}</a>
                            </td>
                            <td class="px-3 py-2">{{ Str::limit($inquiry->body, 40) }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">@include('admin.inquiries._status', ['status' => $inquiry->status])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $inquiries->links() }}</div>
    @endif
@endsection

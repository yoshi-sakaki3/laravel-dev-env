@php
    $color = match ($status) {
        App\Enums\InquiryStatus::New => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200',
        App\Enums\InquiryStatus::InProgress => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
        App\Enums\InquiryStatus::Done => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
    };
@endphp
<span class="rounded px-1.5 py-0.5 text-xs font-semibold {{ $color }}">{{ $status->label() }}</span>

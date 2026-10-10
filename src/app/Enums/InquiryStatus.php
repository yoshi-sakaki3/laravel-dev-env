<?php

namespace App\Enums;

/** お問い合わせの対応状況 */
enum InquiryStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::New => '未対応',
            self::InProgress => '対応中',
            self::Done => '完了',
        };
    }
}

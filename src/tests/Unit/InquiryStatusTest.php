<?php

namespace Tests\Unit;

use App\Enums\InquiryStatus;
use PHPUnit\Framework\TestCase;

class InquiryStatusTest extends TestCase
{
    public function test_すべての対応状況に日本語の表示名がある(): void
    {
        $labels = array_map(fn (InquiryStatus $status) => $status->label(), InquiryStatus::cases());

        $this->assertSame(['未対応', '対応中', '完了'], $labels);
    }
}

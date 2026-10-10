<?php

namespace Database\Seeders;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * 見本データを入れる（ローカル開発専用）。
     * 管理画面のログイン：admin@example.com / password
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => '管理者（見本）', 'password' => 'password'],
        );

        if (Inquiry::query()->exists()) {
            return;
        }

        Inquiry::factory(8)->status(InquiryStatus::New)->create();
        Inquiry::factory(4)->status(InquiryStatus::InProgress)->create(['note' => '電話で折り返し済み。資料を送る。']);
        Inquiry::factory(6)->status(InquiryStatus::Done)->create();
    }
}

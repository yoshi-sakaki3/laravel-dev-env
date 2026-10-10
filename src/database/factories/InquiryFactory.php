<?php

namespace Database\Factories;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * 見本データ。名前は架空、メールアドレスは example.com などの予約ドメインだけを使う。
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'body' => fake()->randomElement([
                '料金プランについて、詳しい資料をいただけますか。',
                '来週の平日で、相談できる日時を教えてください。',
                '申し込み後のキャンセルはできますか。',
                'ホームページの写真と実際の内容が違うようなので、確認をお願いします。',
                '領収書の宛名を変更したいです。',
                '初めて利用します。持ち物や準備するものはありますか。',
            ]),
        ];
    }

    public function status(InquiryStatus $status): static
    {
        return $this->afterMaking(fn (Inquiry $inquiry) => $inquiry->status = $status);
    }
}

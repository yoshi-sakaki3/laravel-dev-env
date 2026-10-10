<?php

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Mail\InquiryAutoReply;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function validInput(): array
    {
        return [
            'name' => '　山田 花子 ',
            'email' => 'hanako@example.com',
            'body' => "料金について教えてください。\n2行目です。",
            'agree' => '1',
        ];
    }

    public function test_フォームが表示される(): void
    {
        $this->get('/contact')->assertOk()->assertSee('お問い合わせ内容');
    }

    public function test_送信すると保存され、通知と自動返信のメールが送られる(): void
    {
        Mail::fake();

        $this->post('/contact', $this->validInput())->assertRedirect('/contact/thanks');

        $inquiry = Inquiry::sole();
        $this->assertSame('山田 花子', $inquiry->name);
        $this->assertSame(InquiryStatus::New, $inquiry->fresh()->status);

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $mail) => $mail->hasTo('owner@example.com')
            && $mail->hasReplyTo('hanako@example.com'));
        Mail::assertSent(InquiryAutoReply::class, fn (InquiryAutoReply $mail) => $mail->hasTo('hanako@example.com'));
    }

    public function test_誤った入力は項目ごとにエラーを返し、保存しない(): void
    {
        Mail::fake();

        $this->from('/contact')
            ->post('/contact', ['name' => '', 'email' => 'not-an-email', 'body' => str_repeat('あ', 2001)])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['name', 'email', 'body', 'agree']);

        $this->assertSame(0, Inquiry::count());
        Mail::assertNothingSent();
    }

    public function test_名前に改行が入った送信を断る(): void
    {
        $this->post('/contact', [...$this->validInput(), 'name' => "山田\r\nBcc: someone@example.com"])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_見えない欄が埋まっていたら、保存もメール送信もしない(): void
    {
        Mail::fake();

        $this->post('/contact', [...$this->validInput(), 'website' => 'https://spam.example'])
            ->assertRedirect('/contact/thanks');

        $this->assertSame(0, Inquiry::count());
        Mail::assertNothingSent();
    }

    public function test_同じ人からの送信は1分に5回まで(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->validInput())->assertRedirect('/contact/thanks');
        }

        $this->post('/contact', $this->validInput())->assertTooManyRequests();
        $this->assertSame(5, Inquiry::count());
    }
}

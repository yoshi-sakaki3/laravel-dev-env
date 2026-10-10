<?php

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインしていないと管理画面は見られない(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->get('/admin/inquiries')->assertRedirect('/admin/login');
        $this->get("/admin/inquiries/{$inquiry->id}")->assertRedirect('/admin/login');
        $this->put("/admin/inquiries/{$inquiry->id}", ['status' => 'done'])->assertRedirect('/admin/login');

        $this->assertSame(InquiryStatus::New, $inquiry->fresh()->status);
    }

    public function test_正しいメールアドレスとパスワードでログインできる(): void
    {
        $user = User::factory()->create(['password' => 'correct-password']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-password'])
            ->assertRedirect('/admin/inquiries');
        $this->assertAuthenticatedAs($user);
    }

    public function test_一覧を検索と対応状況で絞り込める(): void
    {
        Inquiry::factory()->create(['name' => '佐藤 一郎', 'body' => '料金について']);
        Inquiry::factory()->status(InquiryStatus::Done)->create(['name' => '鈴木 次郎', 'body' => '料金の領収書']);
        Inquiry::factory()->create(['name' => '高橋 三郎', 'body' => '予約の変更']);

        $this->actingAs(User::factory()->create());

        $this->get('/admin/inquiries?q=料金')
            ->assertOk()->assertSee('佐藤 一郎')->assertSee('鈴木 次郎')->assertDontSee('高橋 三郎');

        $this->get('/admin/inquiries?q=料金&status=done')
            ->assertOk()->assertSee('鈴木 次郎')->assertDontSee('佐藤 一郎');

        // % は「すべてに一致」ではなく、文字として探す
        $this->get('/admin/inquiries?q=%25')->assertOk()->assertSee('該当するお問い合わせがありません');
    }

    public function test_対応状況と社内メモを更新できる(): void
    {
        $inquiry = Inquiry::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->put("/admin/inquiries/{$inquiry->id}", ['status' => 'in_progress', 'note' => '電話で折り返す'])
            ->assertRedirect("/admin/inquiries/{$inquiry->id}");

        $inquiry->refresh();
        $this->assertSame(InquiryStatus::InProgress, $inquiry->status);
        $this->assertSame('電話で折り返す', $inquiry->note);

        $this->put("/admin/inquiries/{$inquiry->id}", ['status' => 'unknown'])->assertSessionHasErrors('status');
    }

    public function test_本文に入ったタグはそのまま文字として表示する(): void
    {
        $inquiry = Inquiry::factory()->create(['body' => '<script>alert(1)</script>']);
        $this->actingAs(User::factory()->create());

        $this->get("/admin/inquiries/{$inquiry->id}")
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
            ->assertDontSee('<script>alert(1)</script>', escape: false);
    }
}

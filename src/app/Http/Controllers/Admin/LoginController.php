<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** 管理画面のログイン・ログアウト（Laravel 標準の認証を使う） */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
            ['email' => ['required', 'email'], 'password' => ['required', 'string']],
            ['required' => '入力してください。', 'email' => 'メールアドレスの形が正しくありません。'],
        );

        if (! Auth::attempt($credentials)) {
            // どちらが違うかは伝えない（登録の有無を知られないため）
            return back()->withErrors(['email' => 'メールアドレスかパスワードが違います。'])->onlyInput('email');
        }

        // ログイン前のセッション ID を使い回さない（セッション固定攻撃の対策）
        $request->session()->regenerate();

        return redirect()->intended(route('admin.inquiries.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('admin.login');
    }
}

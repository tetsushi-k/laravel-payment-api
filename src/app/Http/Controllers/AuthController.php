<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * セッションベースの認証コントローラー
 *
 * Blade（SSR）構成のため、Sanctumトークンではなく
 * Laravelの標準セッション認証を使用する。
 */
class AuthController extends Controller
{
    /**
     * ログイン画面を表示
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/orders');
        }

        return view('auth.login');
    }

    /**
     * ログイン処理
     *
     * Auth::attempt() がパスワードのハッシュ検証・セッション生成を自動で行う。
     * セッション固定攻撃を防ぐため、ログイン成功後に regenerate() を呼ぶ。
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // セッション固定攻撃（Session Fixation）を防ぐ
            $request->session()->regenerate();

            return redirect()->intended('/orders');
        }

        return back()
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。'])
            ->onlyInput('email');
    }

    /**
     * ログアウト処理
     *
     * セッションを完全に破棄してCSRFトークンも再生成する。
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

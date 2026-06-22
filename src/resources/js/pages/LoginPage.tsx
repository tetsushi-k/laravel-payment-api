import { FormEvent, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ApiError } from '@/api/client';
import { useAuth } from '@/hooks/useAuth';

export function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();
        setError(null);
        setSubmitting(true);

        try {
            await login(email, password);
            navigate('/orders', { replace: true });
        } catch (err) {
            if (err instanceof ApiError) {
                setError(err.errors.email?.[0] ?? err.message);
            } else {
                setError('ログインに失敗しました。');
            }
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 p-5">
            <div className="w-full max-w-md rounded-xl bg-white p-10 shadow-sm">
                <h1 className="text-xl font-semibold text-slate-900">ログイン</h1>
                <p className="mt-2 text-sm text-slate-500">
                    注文一覧を確認するにはログインが必要です
                </p>

                <form onSubmit={handleSubmit} className="mt-8 space-y-5">
                    <div>
                        <label htmlFor="email" className="mb-1.5 block text-sm font-semibold text-slate-700">
                            メールアドレス
                        </label>
                        <input
                            id="email"
                            type="email"
                            autoComplete="email"
                            value={email}
                            onChange={(event) => setEmail(event.target.value)}
                            className={`w-full rounded-md border px-3.5 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15 ${
                                error ? 'border-red-400' : 'border-slate-200'
                            }`}
                        />
                        {error && <p className="mt-1.5 text-sm text-red-600">{error}</p>}
                    </div>

                    <div>
                        <label
                            htmlFor="password"
                            className="mb-1.5 block text-sm font-semibold text-slate-700"
                        >
                            パスワード
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            value={password}
                            onChange={(event) => setPassword(event.target.value)}
                            className="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={submitting}
                        className="w-full rounded-md bg-indigo-500 py-3 text-sm font-semibold text-white hover:bg-indigo-600 disabled:opacity-60"
                    >
                        {submitting ? 'ログイン中...' : 'ログイン'}
                    </button>
                </form>

                <div className="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        テスト用アカウント
                    </p>
                    <p>
                        メール:{' '}
                        <code className="rounded bg-slate-200 px-1.5 py-0.5">test@example.com</code>
                    </p>
                    <p className="mt-1">
                        パスワード:{' '}
                        <code className="rounded bg-slate-200 px-1.5 py-0.5">password123</code>
                    </p>
                </div>
            </div>
        </div>
    );
}

import { FormEvent, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { CardElement, Elements, useElements, useStripe } from '@stripe/react-stripe-js';
import { loadStripe, type StripeCardElementOptions } from '@stripe/stripe-js';
import { api, ApiError } from '@/api/client';
import type { PaymentIntentResponse, StripeConfigResponse } from '@/types/payment';

const cardElementOptions: StripeCardElementOptions = {
    hidePostalCode: true,
    style: {
        base: {
            fontSize: '16px',
            color: '#1e293b',
        },
    },
};

function PaymentForm() {
    const stripe = useStripe();
    const elements = useElements();
    const navigate = useNavigate();
    const [amount, setAmount] = useState('1000');
    const [error, setError] = useState<string | null>(null);
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (event: FormEvent) => {
        event.preventDefault();

        if (!stripe || !elements) {
            return;
        }

        const cardElement = elements.getElement(CardElement);

        if (!cardElement) {
            return;
        }

        setError(null);
        setSubmitting(true);

        try {
            const data = await api.post<PaymentIntentResponse>('/api/payment/intent', {
                amount: parseInt(amount, 10),
            });

            if (!data?.clientSecret) {
                throw new Error('clientSecret が取得できませんでした。');
            }

            const { error: stripeError } = await stripe.confirmCardPayment(data.clientSecret, {
                payment_method: { card: cardElement },
            });

            if (stripeError) {
                setError(stripeError.message ?? '決済に失敗しました。');
                return;
            }

            navigate('/payment/success', { replace: true });
        } catch (err) {
            if (err instanceof ApiError) {
                setError(err.errors.amount?.[0] ?? err.message);
            } else if (err instanceof Error) {
                setError(err.message);
            } else {
                setError('決済に失敗しました。');
            }
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 className="text-xl font-semibold text-slate-900">テスト決済フォーム</h1>
            <p className="mt-2 text-sm text-slate-500">Laravel × Stripe Webhook ポートフォリオ</p>

            <form onSubmit={handleSubmit} className="mt-8 space-y-5">
                <div>
                    <label htmlFor="amount" className="mb-1.5 block text-sm font-semibold text-slate-700">
                        金額（円）
                    </label>
                    <input
                        id="amount"
                        type="number"
                        min={100}
                        max={1000000}
                        value={amount}
                        onChange={(event) => setAmount(event.target.value)}
                        className="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/15"
                    />
                </div>

                <div>
                    <label className="mb-1.5 block text-sm font-semibold text-slate-700">カード情報</label>
                    <div className="rounded-md border border-slate-200 px-3.5 py-3">
                        <CardElement options={cardElementOptions} />
                    </div>
                    {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
                </div>

                <button
                    type="submit"
                    disabled={submitting || !stripe}
                    className="w-full rounded-md bg-indigo-500 py-3 text-sm font-semibold text-white hover:bg-indigo-600 disabled:opacity-60"
                >
                    {submitting ? '処理中...' : '支払う'}
                </button>
            </form>

            <div className="mt-6 rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                <p className="font-semibold">テスト用カード番号</p>
                <p className="mt-2">番号: 4242 4242 4242 4242</p>
                <p>有効期限: 任意の将来日付（例: 12/34）</p>
                <p>CVC: 任意の3桁</p>
            </div>

            <Link to="/orders" className="mt-4 block text-center text-sm font-medium text-indigo-500 hover:text-indigo-600">
                注文一覧を見る →
            </Link>
        </div>
    );
}

export function PaymentPage() {
    const [stripePromise, setStripePromise] = useState<ReturnType<typeof loadStripe> | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        api.get<StripeConfigResponse>('/api/config/stripe')
            .then((config) => {
                setStripePromise(loadStripe(config.publicKey));
            })
            .catch(() => {
                setError('Stripe 設定の取得に失敗しました。');
            })
            .finally(() => {
                setLoading(false);
            });
    }, []);

    if (loading) {
        return <p className="text-sm text-slate-500">読み込み中...</p>;
    }

    if (error || !stripePromise) {
        return <p className="text-sm text-red-600">{error ?? 'Stripe を初期化できませんでした。'}</p>;
    }

    return (
        <Elements stripe={stripePromise}>
            <PaymentForm />
        </Elements>
    );
}

import { Link } from 'react-router-dom';

export function PaymentSuccessPage() {
    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-100 p-5">
            <div className="w-full max-w-md rounded-xl bg-white p-10 text-center shadow-sm">
                <div className="text-5xl">✅</div>
                <h1 className="mt-4 text-xl font-semibold text-slate-900">決済が完了しました</h1>
                <p className="mt-2 text-sm text-slate-500">
                    ご購入ありがとうございます。
                    <br />
                    Stripe Webhook により注文ステータスが更新されます。
                </p>
                <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <Link
                        to="/orders"
                        className="rounded-md bg-indigo-500 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-600"
                    >
                        注文一覧を確認する
                    </Link>
                    <Link
                        to="/payment"
                        className="rounded-md border-2 border-indigo-500 px-6 py-3 text-sm font-semibold text-indigo-500 hover:bg-indigo-50"
                    >
                        もう一度試す
                    </Link>
                </div>
            </div>
        </div>
    );
}

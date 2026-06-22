import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '@/api/client';
import {
    orderStatusClassName,
    orderStatusLabel,
    type Order,
    type OrdersMeta,
    type OrdersResponse,
} from '@/types/order';

function formatAmount(amount: number): string {
    return `¥${amount.toLocaleString('ja-JP')}`;
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('ja-JP');
}

export function OrderListPage() {
    const [orders, setOrders] = useState<Order[]>([]);
    const [meta, setMeta] = useState<OrdersMeta | null>(null);
    const [page, setPage] = useState(1);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const fetchOrders = useCallback(async (targetPage: number, isRefresh = false) => {
        if (isRefresh) {
            setRefreshing(true);
        } else {
            setLoading(true);
        }

        try {
            const data = await api.get<OrdersResponse>(`/api/orders?page=${targetPage}`);
            setOrders(data.data);
            setMeta(data.meta);
            setPage(data.meta.current_page);
            setError(null);
        } catch {
            setError('注文一覧の取得に失敗しました。');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useEffect(() => {
        fetchOrders(page);
    }, [fetchOrders, page]);

    const handleRefresh = () => {
        fetchOrders(page, true);
    };

    if (loading) {
        return <p className="text-sm text-slate-500">読み込み中...</p>;
    }

    return (
        <div>
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 className="text-xl font-semibold text-slate-900">注文一覧</h1>
                <button
                    type="button"
                    onClick={handleRefresh}
                    disabled={refreshing}
                    className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                >
                    {refreshing ? '再取得中...' : '再取得'}
                </button>
            </div>

            {error && <p className="mb-4 text-sm text-red-600">{error}</p>}

            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                {orders.length === 0 ? (
                    <div className="px-6 py-16 text-center text-sm text-slate-500">
                        <p>まだ注文がありません。</p>
                        <p className="mt-2">決済フォームから最初の注文を作成してください。</p>
                        <Link
                            to="/payment"
                            className="mt-4 inline-block text-sm font-medium text-indigo-500 hover:text-indigo-600"
                        >
                            決済フォームへ →
                        </Link>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-sm">
                            <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th className="px-4 py-3">ID</th>
                                    <th className="px-4 py-3">金額</th>
                                    <th className="px-4 py-3">ステータス</th>
                                    <th className="px-4 py-3">PaymentIntent ID</th>
                                    <th className="px-4 py-3">作成日時</th>
                                </tr>
                            </thead>
                            <tbody>
                                {orders.map((order) => (
                                    <tr key={order.id} className="border-t border-slate-100">
                                        <td className="px-4 py-3 text-slate-700">#{order.id}</td>
                                        <td className="px-4 py-3 font-semibold text-slate-900">
                                            {formatAmount(order.amount)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={`inline-block rounded-full px-2.5 py-1 text-xs font-semibold ${orderStatusClassName(order.status)}`}
                                            >
                                                {orderStatusLabel(order.status)}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs text-slate-400">
                                            {order.stripe_payment_intent_id ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {formatDateTime(order.created_at)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {meta && meta.last_page > 1 && (
                <div className="mt-4 flex items-center justify-center gap-2">
                    <button
                        type="button"
                        onClick={() => setPage((current) => Math.max(1, current - 1))}
                        disabled={page <= 1}
                        className="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                    >
                        前へ
                    </button>
                    <span className="text-sm text-slate-600">
                        {meta.current_page} / {meta.last_page}
                    </span>
                    <button
                        type="button"
                        onClick={() => setPage((current) => Math.min(meta.last_page, current + 1))}
                        disabled={page >= meta.last_page}
                        className="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50 disabled:opacity-50"
                    >
                        次へ
                    </button>
                </div>
            )}

            <p className="mt-4 text-center text-xs text-slate-400">
                Webhook 受信後に「再取得」を押すとステータスが更新されます
            </p>
        </div>
    );
}

export type OrderStatus = 'pending' | 'succeeded' | 'failed' | 'refunded';

export type Order = {
    id: number;
    amount: number;
    status: OrderStatus;
    stripe_payment_intent_id: string | null;
    created_at: string;
};

export type OrdersMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type OrdersResponse = {
    data: Order[];
    meta: OrdersMeta;
};

const STATUS_LABELS: Record<OrderStatus, string> = {
    pending: '処理中',
    succeeded: '成功',
    failed: '失敗',
    refunded: '返金済',
};

const STATUS_CLASSES: Record<OrderStatus, string> = {
    pending: 'bg-amber-100 text-amber-900',
    succeeded: 'bg-emerald-100 text-emerald-900',
    failed: 'bg-red-100 text-red-900',
    refunded: 'bg-sky-100 text-sky-900',
};

export function orderStatusLabel(status: OrderStatus): string {
    return STATUS_LABELS[status] ?? status;
}

export function orderStatusClassName(status: OrderStatus): string {
    return STATUS_CLASSES[status] ?? 'bg-slate-100 text-slate-700';
}

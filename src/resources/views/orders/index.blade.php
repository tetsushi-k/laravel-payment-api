<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文一覧 | Laravel Payment API</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f4f8;
            padding: 40px 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        h1 { font-size: 1.4rem; color: #1a202c; }
        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn:hover { background: #5a67d8; }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f7fafc; }
        th {
            padding: 14px 16px;
            text-align: left;
            font-size: 0.8rem;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f0f4f8;
            font-size: 0.9rem;
            color: #2d3748;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f7fafc; }
        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .status-pending   { background: #fefcbf; color: #744210; }
        .status-succeeded { background: #c6f6d5; color: #22543d; }
        .status-failed    { background: #fed7d7; color: #742a2a; }
        .status-refunded  { background: #bee3f8; color: #2a4365; }
        .amount { font-weight: 600; }
        .pi-id {
            font-size: 0.75rem;
            color: #a0aec0;
            font-family: monospace;
        }
        .empty {
            padding: 60px;
            text-align: center;
            color: #a0aec0;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 8px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            background: #fff;
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .pagination .active span {
            background: #667eea;
            color: #fff;
        }
        .refresh-note {
            text-align: center;
            color: #a0aec0;
            font-size: 0.82rem;
            margin-top: 12px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>注文一覧</h1>
        <a href="/payment" class="btn">+ 新しい決済</a>
    </div>

    <div class="card">
        @if($orders->isEmpty())
            <div class="empty">まだ注文がありません。<br>決済フォームから最初の注文を作成してください。</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>金額</th>
                        <th>ステータス</th>
                        <th>PaymentIntent ID</th>
                        <th>作成日時</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td class="amount">¥{{ number_format($order->amount) }}</td>
                        <td>
                            <span class="status status-{{ $order->status }}">
                                {{ match($order->status) {
                                    'pending'   => '処理中',
                                    'succeeded' => '成功',
                                    'failed'    => '失敗',
                                    'refunded'  => '返金済',
                                    default     => $order->status,
                                } }}
                            </span>
                        </td>
                        <td class="pi-id">{{ $order->stripe_payment_intent_id ?? '—' }}</td>
                        <td>{{ $order->created_at->format('Y/m/d H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="pagination">
        {{ $orders->links() }}
    </div>

    <p class="refresh-note">Webhook 受信後にページを再読み込みするとステータスが更新されます</p>
</div>
</body>
</html>

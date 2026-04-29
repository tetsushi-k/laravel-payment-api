<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>決済完了 | Laravel Payment API</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 40px;
            width: 100%;
            max-width: 460px;
            text-align: center;
        }
        .icon { font-size: 3rem; margin-bottom: 16px; }
        h1 { font-size: 1.4rem; color: #1a202c; margin-bottom: 8px; }
        p { color: #718096; margin-bottom: 28px; }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 6px;
        }
        a:hover { background: #5a67d8; }
        a.secondary {
            background: #fff;
            color: #667eea;
            border: 2px solid #667eea;
        }
        a.secondary:hover { background: #ebf4ff; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">✅</div>
    <h1>決済が完了しました</h1>
    <p>ご購入ありがとうございます。<br>Stripe Webhook により注文ステータスが更新されます。</p>
    <a href="/orders">注文一覧を確認する</a>
    <a href="/payment" class="secondary">もう一度試す</a>
</div>
</body>
</html>

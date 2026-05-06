<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>決済フォーム | Laravel Payment API</title>
    {{-- Stripe.js を CDN から読み込む（カード情報は Stripe のサーバーに直接送信される） --}}
    <script src="https://js.stripe.com/v3/"></script>
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
        }
        h1 {
            font-size: 1.4rem;
            color: #1a202c;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 28px;
        }
        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            margin-bottom: 20px;
            outline: none;
            transition: border 0.2s;
        }
        input[type="number"]:focus { border-color: #667eea; }
        #card-element {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 8px;
            background: #fff;
        }
        #card-errors {
            color: #e53e3e;
            font-size: 0.85rem;
            min-height: 20px;
            margin-bottom: 20px;
        }
        button {
            width: 100%;
            padding: 14px;
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover:not(:disabled) { background: #5a67d8; }
        button:disabled { background: #a0aec0; cursor: not-allowed; }
        .test-info {
            margin-top: 20px;
            padding: 12px;
            background: #ebf8ff;
            border-radius: 6px;
            font-size: 0.82rem;
            color: #2b6cb0;
        }
        .nav-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .nav-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <h1>テスト決済フォーム</h1>
    <p class="subtitle">Laravel × Stripe Webhook ポートフォリオ</p>

    <form id="payment-form">
        <label for="amount">金額（円）</label>
        <input type="number" id="amount" name="amount" value="1000" min="100" max="1000000">

        <label>カード情報</label>
        {{-- Stripe Elements がこの要素にカード入力フォームを挿入する --}}
        <div id="card-element"></div>
        <div id="card-errors"></div>

        <button id="submit-btn" type="submit">支払う</button>
    </form>

    <div class="test-info">
        <strong>テスト用カード番号</strong><br>
        番号: 4242 4242 4242 4242<br>
        有効期限: 任意の将来日付（例: 12/34）<br>
        CVC: 任意の3桁
    </div>

    <a href="/orders" class="nav-link">注文一覧を見る →</a>
</div>

<script>
    // Stripe.js の初期化（公開可能キーを使用）
    const stripe = Stripe('{{ $stripePublicKey }}');
    const elements = stripe.elements();

    // Card Element を作成してフォームに挿入
    const cardElement = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: { fontSize: '16px', color: '#1a202c' }
        }
    });
    cardElement.mount('#card-element');

    // カード入力エラーをリアルタイム表示
    cardElement.on('change', (event) => {
        document.getElementById('card-errors').textContent = event.error ? event.error.message : '';
    });

    // フォーム送信処理
    document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = '処理中...';

        const amount = document.getElementById('amount').value;

        // Step 1: サーバーに PaymentIntent の作成を依頼
        const response = await fetch('/payment/intent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ amount: parseInt(amount) }),
        });

        const { clientSecret } = await response.json();

        // Step 2: Stripe.js でカード情報を Stripe に送信して決済を確定
        // カード情報はブラウザ → Stripe サーバーに直接送られる（自社サーバーを経由しない）
        const { error } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: { card: cardElement }
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitBtn.disabled = false;
            submitBtn.textContent = '支払う';
        } else {
            // 決済成功 → 完了画面へ
            window.location.href = '/payment/success';
        }
    });
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン | Laravel Payment API</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        h1 {
            font-size: 1.4rem;
            color: #1a202c;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 0.875rem;
            color: #718096;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 6px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.9rem;
            color: #2d3748;
            transition: border-color 0.2s;
            outline: none;
        }
        input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
        }
        input.error { border-color: #fc8181; }
        .error-message {
            font-size: 0.8rem;
            color: #e53e3e;
            margin-top: 6px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 0.875rem;
            color: #4a5568;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: #5a67d8; }
    </style>
</head>
<body>
<div class="card">
    <h1>ログイン</h1>
    <p class="subtitle">注文一覧を確認するにはログインが必要です</p>

    <form method="POST" action="/login">
        @csrf

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                class="{{ $errors->has('email') ? 'error' : '' }}"
            >
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
            >
        </div>

        <div class="remember">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember" style="margin-bottom:0; font-weight:400;">ログイン状態を保持する</label>
        </div>

        <button type="submit" class="btn">ログイン</button>
    </form>

    <div style="margin-top:24px; padding:16px; background:#f7fafc; border-radius:8px; border:1px solid #e2e8f0;">
        <p style="font-size:0.78rem; font-weight:600; color:#718096; margin-bottom:8px;">テスト用アカウント</p>
        <p style="font-size:0.85rem; color:#4a5568;">メール：<code style="background:#edf2f7; padding:2px 6px; border-radius:4px;">test@example.com</code></p>
        <p style="font-size:0.85rem; color:#4a5568; margin-top:4px;">パスワード：<code style="background:#edf2f7; padding:2px 6px; border-radius:4px;">password123</code></p>
    </div>
</div>
</body>
</html>

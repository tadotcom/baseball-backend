<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ログイン</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { height: 100%; }
        body { display: flex; align-items: center; justify-content: center; background-color: #f5f5f5; }
        .form-signin { max-width: 400px; padding: 1rem; }
    </style>
</head>
<body class="text-center">
    
    <main class="form-signin w-100 m-auto">
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <h1 class="h3 mb-3 fw-normal">管理者ログイン</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                <label for="email">Email アドレス</label>
            </div>
            <div class="form-floating mt-2">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">パスワード</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">ログイン</button>
        </form>
    </main>

</body>
</html>
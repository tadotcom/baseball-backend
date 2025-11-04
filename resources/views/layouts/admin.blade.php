<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理画面') - 草野球マッチング</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">管理者ダッシュボード</a>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn {{ request()->routeIs('admin.dashboard') ? 'btn-light' : 'btn-outline-light' }} btn-sm me-2">ダッシュボード</a>
                <a href="{{ route('admin.games.index') }}" class="btn {{ request()->routeIs('admin.games.*') ? 'btn-light' : 'btn-outline-light' }} btn-sm me-2">試合管理</a>
                <a href="{{ route('admin.users.index') }}" class="btn {{ request()->routeIs('admin.users.*') ? 'btn-light' : 'btn-outline-light' }} btn-sm me-2">ユーザー管理</a>
                <a href="{{ route('admin.notifications.create') }}" class="btn {{ request()->routeIs('admin.notifications.*') ? 'btn-light' : 'btn-outline-light' }} btn-sm me-2">通知管理</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">ログアウト</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>@yield('title', '管理画面')</h1>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
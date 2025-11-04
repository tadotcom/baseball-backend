<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '管理画面') - {{ config('app.name', 'Laravel') }}</title>

    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    @stack('head')

    <style>
        /* Basic Layout Styles */
        body { margin: 0; font-family: sans-serif; background-color: #f8f9fa; }
        .admin-header { background-color: #343a40; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .admin-header a { color: white; text-decoration: none; margin-left: 1rem; }
        .admin-container { display: flex; }
        .admin-sidebar { width: 240px; background-color: #e9ecef; min-height: calc(100vh - 60px); padding: 1rem; }
        .admin-sidebar ul { list-style: none; padding: 0; margin: 0; }
        .admin-sidebar li a { display: block; padding: 0.5rem 0; text-decoration: none; color: #333; }
        .admin-content { flex-grow: 1; padding: 2rem; }
        .flash-message { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .flash-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; background-color: white; }
        th, td { border: 1px solid #dee2e6; padding: 0.75rem; text-align: left; }
        thead { background-color: #e9ecef; }
        .pagination { margin-top: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold;}
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box;}
        .form-group .error-message { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; }
        .button { padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .button-primary { background-color: #007bff; color: white; }
        .button-danger { background-color: #dc3545; color: white; }
        .button-link { background: none; border: none; color: #007bff; text-decoration: underline; padding: 0; cursor: pointer; }
    </style>
</head>
<body>
    <header class="admin-header">
        <div>
            <a href="{{ route('admin.dashboard') }}">草野球マッチング管理画面</a>
        </div>
        <div>
            @auth
                <span>{{ Auth::user()->name ?? Auth::user()->email }}</span>
                <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="button-link" style="color: white; margin-left: 1rem;">ログアウト</button>
                </form>
            @endauth
            @guest
                <a href="{{ route('admin.login') }}">ログイン</a>
            @endguest
        </div>
    </header>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <nav>
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">📊 ダッシュボード</a></li>
                    <li><a href="{{ route('admin.users.index') }}">👥 ユーザー管理</a></li>
                    <li><a href="{{ route('admin.games.index') }}">⚾ 試合管理</a></li>
                    <li><a href="#">📢 通知管理</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">
            @if (session('success'))
                <div class="flash-message flash-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash-message flash-error">{{ session('error') }}</div>
            @endif

            <h1>@yield('title', '管理画面')</h1>

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
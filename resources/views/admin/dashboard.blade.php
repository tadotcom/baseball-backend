<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者ダッシュボード</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">管理者ダッシュボード</a>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-light btn-sm me-2">ダッシュボード</a>
                <a href="{{ route('admin.games.index') }}" class="btn btn-outline-light btn-sm me-2">試合管理</a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light btn-sm me-2">ユーザー管理</a>
                {{-- ★ 修正: 通知管理のリンクを履歴一覧(index)に変更 --}}
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-light btn-sm me-2">通知管理</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">ログアウト</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>ようこそ、管理画面へ</h1>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        
        <h2 class="mt-4">統計情報</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">総ユーザー数</h6>
                        <p class="card-text fs-3 fw-bold">{{ $totalUsers ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">今月の新規登録</h6>
                        <p class="card-text fs-3 fw-bold">{{ $monthlySignups ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">開催予定試合</h6>
                        <p class="card-text fs-3 fw-bold">{{ $upcomingGamesCount ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title">参加登録数(今月)</h6>
                        <p class="card-text fs-3 fw-bold">{{ $monthlyParticipants ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <h2>最近の試合</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>試合名</th>
                        <th>開催日時</th>
                        <th>ステータス</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentGames ?? [] as $game)
                    <tr>
                        <td>{{ $game->place_name }}</td>
                        <td>{{ $game->game_date_time ? \Carbon\Carbon::parse($game->game_date_time)->format('Y/m/d H:i') : '' }}</td>
                        <td><span class="badge bg-success">{{ $game->status }}</span></td>
                        <td><a href="{{ route('admin.games.edit', $game) }}" class="btn btn-sm btn-primary">詳細/編集</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">開催予定の試合はありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <h3>クイックアクセス</h3>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">⚾ 試合管理</h5>
                            <p class="card-text">試合の登録、編集、削除を行います</p>
                            <a href="{{ route('admin.games.index') }}" class="btn btn-primary">試合一覧へ</a>
                            <a href="{{ route('admin.games.create') }}" class="btn btn-success ms-2">新規登録</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">👥 ユーザー管理</h5>
                            <p class="card-text">ユーザーの検索、詳細表示、削除を行います</p>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">ユーザー一覧へ</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">📢 通知管理</h5>
                            <p class="card-text">プッシュ通知やメールの配信を行います</p>
                            {{-- ★ 修正: 通知履歴一覧へのリンク --}}
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-primary">通知一覧へ</a>
                            <a href="{{ route('admin.notifications.create') }}" class="btn btn-success ms-2">新規作成</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@extends('layouts.admin')

@section('title', '試合詳細: ' . $game->place_name)

@section('content')
    <div class="container-fluid">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div></div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.games.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 一覧に戻る
                </a>
                <a href="{{ route('admin.games.edit', $game) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> 編集
                </a>
                <form action="{{ route('admin.games.destroy', $game) }}" method="POST" class="d-inline" onsubmit="return confirm('本当にこの試合を削除しますか?\n参加者がいる場合は削除できません。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> 削除
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- 基本情報 --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">⚾ 基本情報</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" style="width: 150px;">試合ID</th>
                                <td><small class="text-muted">{{ $game->game_id }}</small></td>
                            </tr>
                            <tr>
                                <th class="bg-light">場所名</th>
                                <td>{{ $game->place_name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">開催日時</th>
                                <td>{{ $game->game_date_time->format('Y年m月d日 H:i') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">住所</th>
                                <td>{{ $game->address }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">都道府県</th>
                                <td>{{ $game->prefecture }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" style="width: 150px;">座標</th>
                                <td>緯度: {{ $game->latitude }}, 経度: {{ $game->longitude }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">許容半径</th>
                                <td>{{ $game->acceptable_radius }} m</td>
                            </tr>
                            <tr>
                                <th class="bg-light">ステータス</th>
                                <td>
                                    @switch($game->status)
                                        @case('募集中')
                                            <span class="badge bg-success">{{ $game->status }}</span>
                                            @break
                                        @case('満員')
                                            <span class="badge bg-warning text-dark">{{ $game->status }}</span>
                                            @break
                                        @case('開催済み')
                                            <span class="badge bg-secondary">{{ $game->status }}</span>
                                            @break
                                        @case('中止')
                                            <span class="badge bg-danger">{{ $game->status }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $game->status }}</span>
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">参加費</th>
                                <td>{{ $game->fee === null || $game->fee == 0 ? '無料' : number_format($game->fee) . '円' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">募集人数</th>
                                <td>{{ $game->capacity }} 人</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th class="bg-light" style="width: 150px;">登録日時</th>
                                <td>{{ $game->created_at->format('Y年m月d日 H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">最終更新日時</th>
                                <td>{{ $game->updated_at->format('Y年m月d日 H:i:s') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- 参加者情報 --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">👥 参加者情報 ({{ $game->participations->count() }} / {{ $game->capacity }}人)</h5>
            </div>
            <div class="card-body">
                @if($game->participations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ユーザーID</th>
                                    <th>ニックネーム</th>
                                    <th>チーム</th>
                                    <th>ポジション</th>
                                    <th>ステータス</th>
                                    <th>登録日時</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($game->participations as $participation)
                                    <tr>
                                        <td>
                                            @if($participation->user)
                                                <small class="text-muted">{{ Str::limit($participation->user->user_id, 8, '...') }}</small>
                                            @else
                                                <span class="text-danger">(ユーザー削除済み)</span>
                                            @endif
                                        </td>
                                        <td>{{ $participation->user?->nickname ?? 'N/A' }}</td>
                                        <td>
                                            @if($participation->team_division === 'A')
                                                <span class="badge bg-primary">Aチーム</span>
                                            @else
                                                <span class="badge bg-info">Bチーム</span>
                                            @endif
                                        </td>
                                        <td>{{ $participation->position }}</td>
                                        <td>
                                            @switch($participation->status)
                                                @case('参加')
                                                    <span class="badge bg-success">{{ $participation->status }}</span>
                                                    @break
                                                @case('キャンセル')
                                                    <span class="badge bg-danger">{{ $participation->status }}</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $participation->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $participation->created_at->format('Y/m/d H:i') }}</td>
                                        <td>
                                            @if($participation->user)
                                                <a href="{{ route('admin.users.show', $participation->user) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-person"></i> ユーザー詳細
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">現在、参加登録者はいません。</p>
                @endif
            </div>
        </div>
    </div>
@endsection
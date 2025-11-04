@extends('layouts.admin')

@section('title', 'ユーザー詳細')

@section('content')
    <div class="card">
        <div class="card-header">
            ユーザー情報
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">ユーザーID</dt>
                <dd class="col-sm-9">{{ $user->user_id }}</dd>

                <dt class="col-sm-3">ニックネーム</dt>
                <dd class="col-sm-9">{{ $user->nickname }}</dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $user->email }}</dd>

                <dt class="col-sm-3">登録日時</dt>
                <dd class="col-sm-9">{{ $user->created_at->format('Y/m/d H:i:s') }}</dd>
            </dl>
        </div>
    </div>

    {{-- ★ ここが参加履歴を表示するブロックです --}}
    @if($user->participations && $user->participations->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                参加履歴 ({{ $user->participations->count() }} 件)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>試合名</th>
                                <th>開催日時</th>
                                <th>ステータス</th>
                                <th>参加日時</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->participations as $participation)
                                <tr>
                                    {{-- 試合情報が存在する場合のみリンクを表示 --}}
                                    @if ($participation->game)
                                        <td>
                                            <a href="{{ route('admin.games.show', $participation->game) }}">
                                                {{ $participation->game->place_name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $participation->game->game_date_time ? \Carbon\Carbon::parse($participation->game->game_date_time)->format('Y/m/d H:i') : 'N/A' }}</td>
                                        <td>{{ $participation->game->status ?? 'N/A' }}</td>
                                        <td>{{ $participation->created_at->format('Y/m/d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.games.show', $participation->game) }}" class="btn btn-sm btn-outline-info">試合詳細</a>
                                        </td>
                                    @else
                                        <td colspan="5" class="text-muted">（試合情報が削除されています）</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card mt-4">
            <div class="card-header">
                参加履歴
            </div>
            <div class="card-body">
                <p class="text-muted">このユーザーの試合参加履歴はありません。</p>
            </div>
        </div>
    @endif
    {{-- ★ 参加履歴ブロックここまで --}}


    <div class="mt-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">一覧に戻る</a>
        
        {{-- ログイン中の管理者自身は削除ボタンを表示しない --}}
        @if(auth()->user()->user_id !== $user->user_id)
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('本当にこのユーザーを強制退会させますか?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">ユーザーを強制退会</button>
            </form>
        @endif
    </div>
@endsection
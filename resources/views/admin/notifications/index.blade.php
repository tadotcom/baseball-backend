@extends('layouts.admin')

@section('title', '通知送信履歴')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>通知送信履歴</h1>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> 新規通知作成
        </a>
    </div>

    {{-- フィルターフォーム --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">通知種類</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">すべて</option>
                        <option value="push" {{ request('type') === 'push' ? 'selected' : '' }}>プッシュ通知</option>
                        <option value="email" {{ request('type') === 'email' ? 'selected' : '' }}>メール</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="target_type" class="form-label">配信対象</label>
                    <select class="form-select" id="target_type" name="target_type">
                        <option value="">すべて</option>
                        <option value="all" {{ request('target_type') === 'all' ? 'selected' : '' }}>全ユーザー</option>
                        <option value="game" {{ request('target_type') === 'game' ? 'selected' : '' }}>特定試合参加者</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">ステータス</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">すべて</option>
                        <option value="送信中" {{ request('status') === '送信中' ? 'selected' : '' }}>送信中</option>
                        <option value="送信完了" {{ request('status') === '送信完了' ? 'selected' : '' }}>送信完了</option>
                        <option value="送信失敗" {{ request('status') === '送信失敗' ? 'selected' : '' }}>送信失敗</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2">フィルター</button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">クリア</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 通知履歴テーブル --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>送信日時</th>
                            <th>種類</th>
                            <th>タイトル/件名</th>
                            <th>配信対象</th>
                            <th>送信数</th>
                            <th>ステータス</th>
                            <th>送信者</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('Y/m/d H:i') }}</td>
                            <td>
                                @if($log->type === 'push')
                                    <span class="badge bg-primary">📱 プッシュ</span>
                                @else
                                    <span class="badge bg-success">✉️ メール</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;" title="{{ $log->title }}">
                                    {{ $log->title }}
                                </div>
                            </td>
                            <td>
                                @if($log->target_type === 'all')
                                    <span class="badge bg-info">全ユーザー</span>
                                @else
                                    <span class="badge bg-secondary">
                                        試合: {{ $log->game?->place_name ?? '削除済み' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-success fw-bold">{{ $log->sent_count }}</span>
                                @if($log->failed_count > 0)
                                    / <span class="text-danger">{{ $log->failed_count }}</span>
                                @endif
                            </td>
                            <td>
                                @if($log->status === '送信完了')
                                    <span class="badge bg-success">{{ $log->status }}</span>
                                @elseif($log->status === '送信中')
                                    <span class="badge bg-warning text-dark">{{ $log->status }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $log->status }}</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($log->sent_by_admin, 20) }}</td>
                            <td>
                                <a href="{{ route('admin.notifications.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                    詳細
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                送信履歴はありません。
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーション --}}
            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
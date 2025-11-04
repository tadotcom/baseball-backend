@extends('layouts.admin')

@section('title', '通知送信履歴詳細')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> 一覧に戻る
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" style="width: 150px;">送信日時</th>
                            <td>{{ $log->created_at->format('Y年m月d日 H:i') }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">通知種類</th>
                            <td>
                                @if($log->type === 'push')
                                    <span class="badge bg-primary">📱 プッシュ通知</span>
                                @else
                                    <span class="badge bg-success">✉️ メール</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">配信対象</th>
                            <td>
                                @if($log->target_type === 'all')
                                    <span class="badge bg-info">全ユーザー</span>
                                @else
                                    <span class="badge bg-secondary">特定試合参加者</span>
                                    @if($log->game)
                                        <br>
                                        <small class="text-muted">
                                            試合: {{ $log->game->place_name }}
                                            ({{ $log->game->game_date_time->format('Y/m/d H:i') }})
                                        </small>
                                    @else
                                        <br>
                                        <small class="text-danger">(試合は削除されました)</small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">ステータス</th>
                            <td>
                                @if($log->status === '送信完了')
                                    <span class="badge bg-success">{{ $log->status }}</span>
                                @elseif($log->status === '送信中')
                                    <span class="badge bg-warning text-dark">{{ $log->status }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $log->status }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" style="width: 150px;">送信成功</th>
                            <td><span class="text-success fw-bold fs-5">{{ $log->sent_count }}</span> 件</td>
                        </tr>
                        <tr>
                            <th class="bg-light">送信失敗</th>
                            <td>
                                @if($log->failed_count > 0)
                                    <span class="text-danger fw-bold fs-5">{{ $log->failed_count }}</span> 件
                                @else
                                    <span class="text-muted">0 件</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">送信者</th>
                            <td>{{ $log->sent_by_admin }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">更新日時</th>
                            <td>{{ $log->updated_at->format('Y年m月d日 H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- エラーメッセージ --}}
            @if($log->error_message)
                <div class="alert alert-danger">
                    <h6 class="alert-heading">エラー詳細</h6>
                    <p class="mb-0">{{ $log->error_message }}</p>
                </div>
            @endif

            {{-- タイトル/件名 --}}
            <div class="mb-3">
                <h5>
                    @if($log->type === 'push')
                        タイトル
                    @else
                        件名
                    @endif
                </h5>
                <div class="p-3 bg-light border rounded">
                    {{ $log->title }}
                </div>
            </div>

            {{-- 本文 --}}
            <div class="mb-3">
                <h5>本文</h5>
                <div class="p-3 bg-light border rounded" style="white-space: pre-wrap;">{{ $log->body }}</div>
            </div>

            {{-- 統計情報 --}}
            <div class="mt-4">
                <h5>送信統計</h5>
                <div class="progress" style="height: 30px;">
                    @php
                        $total = $log->sent_count + $log->failed_count;
                        $successPercent = $total > 0 ? ($log->sent_count / $total) * 100 : 0;
                        $failedPercent = $total > 0 ? ($log->failed_count / $total) * 100 : 0;
                    @endphp
                    
                    @if($log->sent_count > 0)
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $successPercent }}%" 
                             aria-valuenow="{{ $log->sent_count }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $total }}">
                            成功: {{ $log->sent_count }}件 ({{ number_format($successPercent, 1) }}%)
                        </div>
                    @endif
                    
                    @if($log->failed_count > 0)
                        <div class="progress-bar bg-danger" role="progressbar" 
                             style="width: {{ $failedPercent }}%" 
                             aria-valuenow="{{ $log->failed_count }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $total }}">
                            失敗: {{ $log->failed_count }}件 ({{ number_format($failedPercent, 1) }}%)
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
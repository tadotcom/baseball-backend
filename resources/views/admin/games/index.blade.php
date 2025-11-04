@extends('layouts.admin')

@section('title', '試合管理')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div></div>
            <a href="{{ route('admin.games.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> 新規試合登録
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- フィルターフォーム --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.games.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="prefecture" class="form-label">都道府県</label>
                        <select class="form-select" id="prefecture" name="prefecture">
                            <option value="">すべて</option>
                            @foreach($prefectures as $pref)
                                <option value="{{ $pref }}" {{ request('prefecture') === $pref ? 'selected' : '' }}>
                                    {{ $pref }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label">ステータス</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">すべて</option>
                            <option value="募集中" {{ request('status') === '募集中' ? 'selected' : '' }}>募集中</option>
                            <option value="満員" {{ request('status') === '満員' ? 'selected' : '' }}>満員</option>
                            <option value="開催済み" {{ request('status') === '開催済み' ? 'selected' : '' }}>開催済み</option>
                            <option value="中止" {{ request('status') === '中止' ? 'selected' : '' }}>中止</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="date_from" class="form-label">開催日(From)</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label for="date_to" class="form-label">開催日(To)</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary me-2">フィルター</button>
                        <a href="{{ route('admin.games.index') }}" class="btn btn-outline-secondary">クリア</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- ソート用のヘルパー関数をBladeで定義 --}}
        @php
            $sortUrl = function($field) use ($sortField, $sortDirection) {
                $newDirection = ($sortField === $field && $sortDirection === 'asc') ? 'desc' : 'asc';
                return request()->fullUrlWithQuery(['sort' => $field, 'direction' => $newDirection]);
            };
            
            $sortIcon = function($field) use ($sortField, $sortDirection) {
                if ($sortField !== $field) return '⇅';
                return $sortDirection === 'asc' ? '↑' : '↓';
            };
        @endphp

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>
                                    <a href="{{ $sortUrl('place_name') }}" class="text-decoration-none text-dark">
                                        場所名 {!! $sortIcon('place_name') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('game_date_time') }}" class="text-decoration-none text-dark">
                                        開催日時 {!! $sortIcon('game_date_time') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('prefecture') }}" class="text-decoration-none text-dark">
                                        都道府県 {!! $sortIcon('prefecture') !!}
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $sortUrl('status') }}" class="text-decoration-none text-dark">
                                        ステータス {!! $sortIcon('status') !!}
                                    </a>
                                </th>
                                <th>参加/定員</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($games as $game)
                            <tr>
                                <td>
                                    <small class="text-muted">{{ Str::limit($game->game_id, 8, '...') }}</small>
                                </td>
                                <td>{{ $game->place_name }}</td>
                                <td>{{ $game->game_date_time->format('Y/m/d H:i') }}</td>
                                <td>{{ $game->prefecture }}</td>
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
                                <td>
                                    <span class="fw-bold">{{ $game->participations_count }}</span> / {{ $game->capacity }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.games.show', $game) }}" class="btn btn-sm btn-outline-info">詳細</a>
                                    <a href="{{ route('admin.games.edit', $game) }}" class="btn btn-sm btn-outline-primary">編集</a>
                                    <form action="{{ route('admin.games.destroy', $game) }}" method="POST" class="d-inline" onsubmit="return confirm('本当にこの試合を削除しますか?\n参加者がいる場合は削除できません。');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">登録されている試合はありません。</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ページネーション --}}
                @if($games->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        <nav aria-label="ページネーション">
                            {{ $games->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* ページネーションのサイズ調整 */
        .pagination {
            margin-bottom: 0;
        }
        .pagination .page-link {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
    </style>
    @endpush
@endsection
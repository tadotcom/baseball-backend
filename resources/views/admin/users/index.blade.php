@extends('layouts.admin')

@section('title', 'ユーザー管理')

@section('content')
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Email または ニックネームで検索" value="{{ $search ?? '' }}">
            <button class="btn btn-outline-secondary" type="submit">検索</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th scope="col">ニックネーム</th>
                    <th scope="col">Email</th>
                    <th scope="col">登録日時</th>
                    <th scope="col">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->nickname ?? $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at->format('Y/m/d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">該当するユーザーが見つかりません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $users->links() }}
    </div>
@endsection
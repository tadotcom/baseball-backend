@extends('layouts.admin')

@section('title', '試合情報編集')

@section('content')
    <div class="container-fluid">
        <div class="mb-3">
            <p class="text-muted mb-0">試合ID: <code>{{ $game->game_id }}</code></p>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.games.update', $game) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Include common form fields, passing the existing game data --}}
                    @include('admin.games._form_fields', ['game' => $game])

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('admin.games.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 更新する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
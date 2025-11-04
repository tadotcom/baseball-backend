@extends('layouts.admin')

@section('title', '新規試合登録')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.games.store') }}" method="POST">
                    @csrf

                    {{-- Include common form fields --}}
                    @include('admin.games._form_fields', ['game' => null])

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('admin.games.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 登録する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
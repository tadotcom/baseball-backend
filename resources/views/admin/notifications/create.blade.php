@extends('layouts.admin')

@section('title', '通知管理')

@section('content')
    <div class="alert alert-info mb-4" role="alert">
        <strong>📢 通知配信機能</strong><br>
        プッシュ通知またはメールで、ユーザーに情報を配信できます。
    </div>

    <div class="row">
        <div class="col-md-6">
            {{-- プッシュ通知フォーム --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📱 プッシュ通知配信</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notifications.send-push') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="push_target_type" class="form-label">配信対象</label>
                            <select class="form-select" id="push_target_type" name="target_type" required>
                                <option value="all">全ユーザー</option>
                                <option value="game">特定の試合参加者</option>
                            </select>
                        </div>

                        <div class="mb-3" id="push_game_select" style="display: none;">
                            <label for="push_game_id" class="form-label">試合選択</label>
                            <select class="form-select" id="push_game_id" name="game_id">
                                <option value="">試合を選択してください</option>
                                @foreach($games as $game)
                                    <option value="{{ $game->game_id }}">
                                        {{ $game->place_name }} - {{ $game->game_date_time ? \Carbon\Carbon::parse($game->game_date_time)->format('Y/m/d H:i') : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="push_title" class="form-label">タイトル</label>
                            <input type="text" class="form-control" id="push_title" name="title" maxlength="100" required placeholder="例: 試合開催のお知らせ">
                            <div class="form-text">最大100文字</div>
                        </div>

                        <div class="mb-3">
                            <label for="push_body" class="form-label">本文</label>
                            <textarea class="form-control" id="push_body" name="body" rows="4" maxlength="500" required placeholder="例: 明日の試合開催についてのお知らせです。"></textarea>
                            <div class="form-text">最大500文字</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">プッシュ通知を送信</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            {{-- メール配信フォーム --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✉️ メール配信</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notifications.send-email') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email_target_type" class="form-label">配信対象</label>
                            <select class="form-select" id="email_target_type" name="target_type" required>
                                <option value="all">全ユーザー</option>
                                <option value="game">特定の試合参加者</option>
                            </select>
                        </div>

                        <div class="mb-3" id="email_game_select" style="display: none;">
                            <label for="email_game_id" class="form-label">試合選択</label>
                            <select class="form-select" id="email_game_id" name="game_id">
                                <option value="">試合を選択してください</option>
                                @foreach($games as $game)
                                    <option value="{{ $game->game_id }}">
                                        {{ $game->place_name }} - {{ $game->game_date_time ? \Carbon\Carbon::parse($game->game_date_time)->format('Y/m/d H:i') : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="email_subject" class="form-label">件名</label>
                            <input type="text" class="form-control" id="email_subject" name="subject" maxlength="200" required placeholder="例: 【草野球マッチング】試合開催のお知らせ">
                            <div class="form-text">最大200文字</div>
                        </div>

                        <div class="mb-3">
                            <label for="email_body" class="form-label">本文</label>
                            <textarea class="form-control" id="email_body" name="body" rows="8" maxlength="5000" required placeholder="メール本文を入力してください。"></textarea>
                            <div class="form-text">最大5000文字</div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">メールを送信</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 注意事項 --}}
    <div class="alert alert-warning" role="alert">
        <h6 class="alert-heading">⚠️ ご注意</h6>
        <ul class="mb-0">
            <li>全ユーザーへの配信は慎重に行ってください。</li>
            <li>プッシュ通知は配信後に取り消すことができません。</li>
            <li>メール配信は送信に時間がかかる場合があります。</li>
            <li>配信前に内容を十分に確認してください。</li>
        </ul>
    </div>

    @push('scripts')
    <script>
        // プッシュ通知の配信対象変更時の処理
        document.getElementById('push_target_type').addEventListener('change', function() {
            const gameSelect = document.getElementById('push_game_select');
            const gameIdInput = document.getElementById('push_game_id');
            
            if (this.value === 'game') {
                gameSelect.style.display = 'block';
                gameIdInput.required = true;
            } else {
                gameSelect.style.display = 'none';
                gameIdInput.required = false;
                gameIdInput.value = '';
            }
        });

        // メールの配信対象変更時の処理
        document.getElementById('email_target_type').addEventListener('change', function() {
            const gameSelect = document.getElementById('email_game_select');
            const gameIdInput = document.getElementById('email_game_id');
            
            if (this.value === 'game') {
                gameSelect.style.display = 'block';
                gameIdInput.required = true;
            } else {
                gameSelect.style.display = 'none';
                gameIdInput.required = false;
                gameIdInput.value = '';
            }
        });
    </script>
    @endpush
@endsection
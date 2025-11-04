{{--
Variables expected:
- $game: Game model instance (nullable, null for create)
- $prefectures: Array of prefecture names (passed from controller)
--}}

{{-- Display Validation Errors --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>入力内容にエラーがあります。</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="place_name" class="form-label">場所名 <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('place_name') is-invalid @enderror" id="place_name" name="place_name" value="{{ old('place_name', $game?->place_name) }}" required maxlength="254">
    @error('place_name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="game_date_time" class="form-label">開催日時 <span class="text-danger">*</span></label>
    <input type="datetime-local" class="form-control @error('game_date_time') is-invalid @enderror" id="game_date_time" name="game_date_time" value="{{ old('game_date_time', $game?->game_date_time?->format('Y-m-d\TH:i')) }}" required>
    @error('game_date_time')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="address" class="form-label">住所 <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $game?->address) }}" required maxlength="254">
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="prefecture" class="form-label">都道府県 <span class="text-danger">*</span></label>
        <select class="form-select @error('prefecture') is-invalid @enderror" id="prefecture" name="prefecture" required>
            <option value="">選択してください</option>
            @foreach($prefectures as $pref)
                <option value="{{ $pref }}" @selected(old('prefecture', $game?->prefecture) == $pref)>{{ $pref }}</option>
            @endforeach
        </select>
        @error('prefecture')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="latitude" class="form-label">緯度 <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $game?->latitude) }}" required step="0.00000001" min="-90" max="90">
        @error('latitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="longitude" class="form-label">経度 <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $game?->longitude) }}" required step="0.00000001" min="-180" max="180">
        @error('longitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="acceptable_radius" class="form-label">許容半径(m) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('acceptable_radius') is-invalid @enderror" id="acceptable_radius" name="acceptable_radius" value="{{ old('acceptable_radius', $game?->acceptable_radius ?? 500) }}" required min="1" max="1999">
        @error('acceptable_radius')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="capacity" class="form-label">募集人数 <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $game?->capacity ?? 18) }}" required min="18" max="100">
        @error('capacity')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="fee" class="form-label">参加費(円)</label>
        <input type="number" class="form-control @error('fee') is-invalid @enderror" id="fee" name="fee" value="{{ old('fee', $game?->fee ?? 0) }}" min="0">
        <div class="form-text">0円の場合は無料</div>
        @error('fee')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Status field only for Edit form --}}
@if ($game)
    <div class="mb-3">
        <label for="status" class="form-label">ステータス <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach(['募集中', '満員', '開催済み', '中止'] as $statusOption)
                <option value="{{ $statusOption }}" @selected(old('status', $game->status) == $statusOption)>{{ $statusOption }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif
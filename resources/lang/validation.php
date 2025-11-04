<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeは:dateより後の日付にしてください。',
    'after_or_equal' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeはアルファベットのみ使用できます。',
    'alpha_dash' => ':attributeはアルファベット、数字、ダッシュ(-)、アンダースコア(_)のみ使用できます。',
    'alpha_num' => ':attributeはアルファベットと数字のみ使用できます。',
    'array' => ':attributeは配列でなければなりません。',
    'ascii' => ':attributeは、シングルバイトの英数字と記号のみ使用できます。',
    'before' => ':attributeは:dateより前の日付にしてください。',
    'before_or_equal' => ':attributeは:date以前の日付にしてください。',
    'between' => [
        'array' => ':attributeの項目数は:min個から:max個の間にしてください。',
        'file' => ':attributeのファイルサイズは:min KBから:max KBの間にしてください。',
        'numeric' => ':attributeは:minから:maxの間の数値にしてください。',
        'string' => ':attributeは:min文字から:max文字の間にしてください。',
    ],
    'boolean' => ':attributeはtrueかfalseにしてください。',
    'confirmed' => ':attributeが確認用項目と一致しません。', //
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_equals' => ':attributeは:dateと同じ日付にしてください。',
    'date_format' => ':attributeは":format"形式の日付にしてください。', //
    'decimal' => ':attributeは、小数点以下が:decimal桁である必要があります。',
    'declined' => ':attributeを辞退してください。',
    'declined_if' => ':otherが:valueの場合、:attributeを辞退してください。',
    'different' => ':attributeと:otherは異なる値にしてください。',
    'digits' => ':attributeは:digits桁の数字にしてください。',
    'digits_between' => ':attributeは:min桁から:max桁の数字にしてください。',
    'dimensions' => ':attributeは無効な画像サイズです。',
    'distinct' => ':attributeに重複した値があります。',
    'doesnt_end_with' => ':attributeは、次のいずれかで終わることはできません: :values',
    'doesnt_start_with' => ':attributeは、次のいずれかで始まることはできません: :values',
    'email' => ':attributeは有効なメールアドレス形式にしてください。', //
    'ends_with' => ':attributeは、次のいずれかで終わる必要があります: :values',
    'enum' => ':attributeで選択された値は無効です。',
    'exists' => '選択された:attributeは存在しません。', // (より具体的にはFormRequestで上書き)
    'file' => ':attributeはファイルでなければなりません。',
    'filled' => ':attributeは必須です。',
    'gt' => [
        'array' => ':attributeの項目数は:value個より多くしてください。',
        'file' => ':attributeのファイルサイズは:value KBより大きくしてください。',
        'numeric' => ':attributeは:valueより大きい数値にしてください。',
        'string' => ':attributeは:value文字より長くしてください。',
    ],
    'gte' => [
        'array' => ':attributeの項目数は:value個以上にしてください。',
        'file' => ':attributeのファイルサイズは:value KB以上にしてください。',
        'numeric' => ':attributeは:value以上の数値にしてください。',
        'string' => ':attributeは:value文字以上にしてください。',
    ],
    'image' => ':attributeは画像ファイルでなければなりません。',
    'in' => '選択された:attributeは無効です。', //
    'in_array' => ':attributeは:otherの項目に存在しません。',
    'integer' => ':attributeは整数でなければなりません。',
    'ip' => ':attributeは有効なIPアドレスにしてください。',
    'ipv4' => ':attributeは有効なIPv4アドレスにしてください。',
    'ipv6' => ':attributeは有効なIPv6アドレスにしてください。',
    'json' => ':attributeは有効なJSON文字列にしてください。',
    'lowercase' => ':attributeは小文字である必要があります。',
    'lt' => [
        'array' => ':attributeの項目数は:value個より少なくしてください。',
        'file' => ':attributeのファイルサイズは:value KBより小さくしてください。',
        'numeric' => ':attributeは:valueより小さい数値にしてください。',
        'string' => ':attributeは:value文字より短くしてください。',
    ],
    'lte' => [
        'array' => ':attributeの項目数は:value個以下にしてください。',
        'file' => ':attributeのファイルサイズは:value KB以下にしてください。',
        'numeric' => ':attributeは:value以下の数値にしてください。',
        'string' => ':attributeは:value文字以下にしてください。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスである必要があります。',
    'max' => [
        'array' => ':attributeの項目数は:max個以下にしてください。',
        'file' => ':attributeのファイルサイズは:max KB以下にしてください。',
        'numeric' => ':attributeは:max以下の数値にしてください。',
        'string' => ':attributeは:max文字以下にしてください。', //
    ],
    'max_digits' => ':attributeは:max桁以下である必要があります。',
    'mimes' => ':attributeは:valuesタイプのファイルでなければなりません。',
    'mimetypes' => ':attributeは:valuesタイプのファイルでなければなりません。',
    'min' => [
        'array' => ':attributeの項目数は:min個以上にしてください。',
        'file' => ':attributeのファイルサイズは:min KB以上にしてください。',
        'numeric' => ':attributeは:min以上の数値にしてください。', //
        'string' => ':attributeは:min文字以上にしてください。', //
    ],
    'min_digits' => ':attributeは:min桁以上である必要があります。',
    'missing' => ':attributeフィールドが存在しない場合に実行されます。',
    'missing_if' => ':otherが:valueの場合、:attributeフィールドは存在してはなりません。',
    'missing_unless' => ':otherが:valueでない限り、:attributeフィールドは存在してはなりません。',
    'missing_with' => ':valuesが存在する場合、:attributeフィールドは存在してはなりません。',
    'missing_with_all' => ':valuesが存在する場合、:attributeフィールドは存在してはなりません。',
    'multiple_of' => ':attributeは:valueの倍数でなければなりません。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeは数値でなければなりません。',
    'password' => [ // Laravel 10+ Password Rule Messages
        'letters' => ':attributeは文字を1文字以上含める必要があります。',
        'mixed' => ':attributeは少なくとも1つの大文字と1つの小文字を含める必要があります。',
        'numbers' => ':attributeは数字を1文字以上含める必要があります。',
        'symbols' => ':attributeは記号を1文字以上含める必要があります。',
        'uncompromised' => ':attributeは漏洩した可能性のあるパスワードです。別のパスワードを試してください。',
    ],
    'present' => ':attributeが存在している必要があります。',
    'prohibited' => ':attributeフィールドは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeフィールドは禁止されています。',
    'prohibited_unless' => ':otherが:valuesでない限り、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeフィールドは、:otherが存在することを禁止します。',
    'regex' => ':attributeの形式が無効です。', // (FormRequestで上書き推奨)
    'required' => ':attributeは必須です。',
    'required_array_keys' => ':attributeフィールドには、:valuesのエントリが必要です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_if_accepted' => ':otherが承認された場合、:attributeフィールドは必須です。',
    'required_unless' => ':otherが:valuesでない限り、:attributeは必須です。',
    'required_with' => ':valuesが存在する場合、:attributeは必須です。',
    'required_with_all' => ':valuesが全て存在する場合、:attributeは必須です。',
    'required_without' => ':valuesが存在しない場合、:attributeは必須です。',
    'required_without_all' => ':valuesが全て存在しない場合、:attributeは必須です。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'array' => ':attributeの項目数は:size個にしてください。',
        'file' => ':attributeのファイルサイズは:size KBにしてください。',
        'numeric' => ':attributeは:sizeにしてください。',
        'string' => ':attributeは:size文字にしてください。', //
    ],
    'starts_with' => ':attributeは、次のいずれかで始まる必要があります: :values',
    'string' => ':attributeは文字列でなければなりません。',
    'timezone' => ':attributeは有効なタイムゾーンにしてください。',
    'unique' => ':attributeは既に使用されています。', // (FormRequestで上書き推奨)
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'uppercase' => ':attributeは大文字である必要があります。',
    'url' => ':attributeは有効なURL形式にしてください。',
    'ulid' => ':attributeは有効なULIDである必要があります。',
    'uuid' => ':attributeは有効なUUIDである必要があります。', //

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        // Example for password regex (if not overridden in FormRequest)
        // 'password' => [
        //     'regex' => 'E-422-06: パスワードは英字と数字をそれぞれ1文字以上含めてください',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    // Map attribute names to Japanese
    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード確認',
        'nickname' => 'ニックネーム',
        'place_name' => '場所名',
        'game_date_time' => '開催日時',
        'address' => '住所',
        'prefecture' => '都道府県',
        'latitude' => '緯度',
        'longitude' => '経度',
        'acceptable_radius' => '許容半径',
        'status' => 'ステータス',
        'fee' => '参加費',
        'capacity' => '募集人数',
        'team_division' => 'チーム区分',
        'position' => '守備ポジション',
        'token' => 'トークン', // device token
        'device_type' => 'デバイスタイプ',
        'title' => 'タイトル', // notification title
        'body' => '本文', // notification body
        'subject' => '件名', // email subject
        'target' => '配信対象',
        'target.type' => '配信対象タイプ',
        'target.game_id' => '対象試合ID',
        'target.user_ids' => '対象ユーザーIDリスト',
        'target.user_ids.*' => '対象ユーザーID',
    ],

];
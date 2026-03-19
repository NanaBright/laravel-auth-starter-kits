<?php

return [
    // Login & Register
    'login' => 'ログイン',
    'logout' => 'ログアウト',
    'register' => '登録',
    'email' => 'メールアドレス',
    'password' => 'パスワード',
    'remember_me' => 'ログイン状態を保持',
    'forgot_password' => 'パスワードをお忘れですか？',
    'confirm_password' => 'パスワード確認',
    'name' => '名前',

    // Success Messages
    'login_success' => 'ログインに成功しました。',
    'logout_success' => 'ログアウトしました。',
    'register_success' => 'アカウントが作成されました。',
    'password_reset_sent' => 'パスワードリセットリンクを送信しました。',
    'password_reset_success' => 'パスワードがリセットされました。',
    'email_verified' => 'メールアドレスが確認されました。',

    // Error Messages
    'invalid_credentials' => 'メールアドレスまたはパスワードが正しくありません。',
    'account_disabled' => 'アカウントが無効化されています。',
    'email_not_verified' => 'メールアドレスを確認してください。',
    'too_many_attempts' => '試行回数が多すぎます。:seconds秒後に再試行してください。',
    'token_expired' => 'このトークンは期限切れです。',
    'token_invalid' => 'このトークンは無効です。',

    // OTP
    'otp_sent' => ':destination に確認コードを送信しました。',
    'otp_sent_sms' => ':phone にSMSを送信しました。',
    'otp_sent_email' => ':email にメールを送信しました。',
    'otp_invalid' => '確認コードが無効です。',
    'otp_expired' => '確認コードの有効期限が切れています。',
    'otp_resent' => '確認コードを再送信しました。',
    'enter_otp' => ':digits桁の確認コードを入力してください',

    // Two-Factor Authentication
    'two_factor' => '二要素認証',
    'two_factor_code' => '認証コード',
    'two_factor_recovery' => 'リカバリーコードを使用',
    'recovery_code' => 'リカバリーコード',
    'enable_2fa' => '二要素認証を有効にする',
    'disable_2fa' => '二要素認証を無効にする',
    '2fa_enabled' => '二要素認証が有効になりました。',
    '2fa_disabled' => '二要素認証が無効になりました。',
    '2fa_invalid_code' => '認証コードが無効です。',
    '2fa_recovery_used' => 'リカバリーコードを使用しました。残り:remaining個のコード。',
    'scan_qr' => '認証アプリでこのQRコードをスキャンしてください',
    'backup_codes' => 'バックアップコード',
    'recovery_codes_regenerated' => 'リカバリーコードが再生成されました。',
    'save_recovery_codes' => 'これらのリカバリーコードを安全な場所に保存してください。',

    // Social Auth
    'continue_with' => ':provider で続ける',
    'social_link_success' => ':provider アカウントが連携されました。',
    'social_unlink_success' => ':provider アカウントの連携が解除されました。',
    'social_already_linked' => 'この :provider アカウントは既に他のユーザーに連携されています。',
    'social_auth_failed' => ':provider での認証に失敗しました。',
    'social_email_taken' => 'この :provider アカウントのメールアドレスは既に使用されています。',

    // Profile
    'profile' => 'プロフィール',
    'update_profile' => 'プロフィール更新',
    'profile_updated' => 'プロフィールが更新されました。',
    'password_updated' => 'パスワードが更新されました。',
    'current_password' => '現在のパスワード',
    'new_password' => '新しいパスワード',
    'current_password_incorrect' => '現在のパスワードが正しくありません。',
];

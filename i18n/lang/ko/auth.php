<?php

return [
    // Login & Register
    'login' => '로그인',
    'logout' => '로그아웃',
    'register' => '회원가입',
    'email' => '이메일 주소',
    'password' => '비밀번호',
    'remember_me' => '로그인 상태 유지',
    'forgot_password' => '비밀번호를 잊으셨나요?',
    'confirm_password' => '비밀번호 확인',
    'name' => '이름',

    // Success Messages
    'login_success' => '로그인되었습니다.',
    'logout_success' => '로그아웃되었습니다.',
    'register_success' => '계정이 생성되었습니다.',
    'password_reset_sent' => '비밀번호 재설정 링크가 전송되었습니다.',
    'password_reset_success' => '비밀번호가 재설정되었습니다.',
    'email_verified' => '이메일이 인증되었습니다.',

    // Error Messages
    'invalid_credentials' => '이메일 또는 비밀번호가 올바르지 않습니다.',
    'account_disabled' => '계정이 비활성화되었습니다.',
    'email_not_verified' => '이메일을 인증해 주세요.',
    'too_many_attempts' => '시도 횟수가 너무 많습니다. :seconds초 후에 다시 시도해 주세요.',
    'token_expired' => '이 토큰은 만료되었습니다.',
    'token_invalid' => '이 토큰은 유효하지 않습니다.',

    // OTP
    'otp_sent' => ':destination (으)로 인증 코드가 전송되었습니다.',
    'otp_sent_sms' => ':phone (으)로 SMS가 전송되었습니다.',
    'otp_sent_email' => ':email (으)로 이메일이 전송되었습니다.',
    'otp_invalid' => '인증 코드가 유효하지 않습니다.',
    'otp_expired' => '인증 코드가 만료되었습니다.',
    'otp_resent' => '인증 코드가 재전송되었습니다.',
    'enter_otp' => ':digits자리 인증 코드를 입력하세요',

    // Two-Factor Authentication
    'two_factor' => '2단계 인증',
    'two_factor_code' => '인증 코드',
    'two_factor_recovery' => '복구 코드 사용',
    'recovery_code' => '복구 코드',
    'enable_2fa' => '2단계 인증 활성화',
    'disable_2fa' => '2단계 인증 비활성화',
    '2fa_enabled' => '2단계 인증이 활성화되었습니다.',
    '2fa_disabled' => '2단계 인증이 비활성화되었습니다.',
    '2fa_invalid_code' => '인증 코드가 유효하지 않습니다.',
    '2fa_recovery_used' => '복구 코드가 사용되었습니다. 남은 코드: :remaining개.',
    'scan_qr' => '인증 앱으로 이 QR 코드를 스캔하세요',
    'backup_codes' => '백업 코드',
    'recovery_codes_regenerated' => '복구 코드가 재생성되었습니다.',
    'save_recovery_codes' => '이 복구 코드를 안전한 곳에 저장하세요.',

    // Social Auth
    'continue_with' => ':provider (으)로 계속',
    'social_link_success' => ':provider 계정이 연결되었습니다.',
    'social_unlink_success' => ':provider 계정 연결이 해제되었습니다.',
    'social_already_linked' => '이 :provider 계정은 이미 다른 사용자에게 연결되어 있습니다.',
    'social_auth_failed' => ':provider 인증에 실패했습니다.',
    'social_email_taken' => '이 :provider 계정의 이메일이 이미 사용 중입니다.',

    // Profile
    'profile' => '프로필',
    'update_profile' => '프로필 업데이트',
    'profile_updated' => '프로필이 업데이트되었습니다.',
    'password_updated' => '비밀번호가 업데이트되었습니다.',
    'current_password' => '현재 비밀번호',
    'new_password' => '새 비밀번호',
    'current_password_incorrect' => '현재 비밀번호가 올바르지 않습니다.',
];

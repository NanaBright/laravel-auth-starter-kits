<?php

return [
    // Login
    'login' => '登录',
    'logout' => '退出',
    'register' => '注册',
    'email' => '电子邮箱',
    'password' => '密码',
    'remember_me' => '记住我',
    'forgot_password' => '忘记密码？',
    'confirm_password' => '确认密码',
    'name' => '姓名',
    'sign_in' => '登录',
    'sign_up' => '注册',
    'create_account' => '创建账户',
    'already_have_account' => '已有账户？',
    'dont_have_account' => '没有账户？',
    
    // Messages
    'welcome' => '欢迎，:name！',
    'login_success' => '登录成功。',
    'logout_success' => '退出成功。',
    'register_success' => '账户创建成功。',
    'invalid_credentials' => '邮箱或密码错误。',
    'account_disabled' => '您的账户已被禁用。',
    'email_not_verified' => '请验证您的电子邮箱。',
    
    // OTP
    'enter_otp' => '输入验证码',
    'otp_sent' => '验证码已发送至 :destination。',
    'otp_invalid' => '验证码无效。',
    'otp_expired' => '验证码已过期。',
    'resend_otp' => '重新发送验证码',
    'otp_resent' => '新验证码已发送。',
    
    // 2FA
    'two_factor' => '双重认证',
    'two_factor_code' => '认证码',
    'two_factor_recovery' => '使用恢复码',
    'two_factor_invalid' => '认证码无效。',
    'recovery_code' => '恢复码',
    'enable_2fa' => '启用双重认证',
    'disable_2fa' => '禁用双重认证',
    'scan_qr' => '使用认证器应用扫描此二维码',
    
    // Social Auth
    'continue_with' => '使用 :provider 继续',
    'or_continue_with' => '或使用以下方式继续',
    'social_auth_error' => '无法通过 :provider 认证。',
    'account_linked' => ':provider 账户已成功关联。',
    'account_unlinked' => ':provider 账户已解除关联。',
    
    // Password Reset
    'reset_password' => '重置密码',
    'send_reset_link' => '发送重置链接',
    'reset_link_sent' => '密码重置链接已发送至您的邮箱。',
    'password_updated' => '密码更新成功。',
    'new_password' => '新密码',
    
    // Verification
    'verify_email' => '验证电子邮箱',
    'verification_sent' => '验证链接已发送。',
    'email_verified' => '邮箱验证成功。',
    'resend_verification' => '重新发送验证邮件',
    
    // Profile
    'profile' => '个人资料',
    'update_profile' => '更新资料',
    'profile_updated' => '资料更新成功。',
    'current_password' => '当前密码',
    'security' => '安全设置',
    
    // Errors
    'throttle' => '尝试次数过多。请在 :seconds 秒后重试。',
    'session_expired' => '您的会话已过期。请重新登录。',
];

<?php

return [
    // Login & Register
    'login' => 'Entrar',
    'logout' => 'Sair',
    'register' => 'Registrar',
    'email' => 'Endereço de E-mail',
    'password' => 'Senha',
    'remember_me' => 'Lembrar-me',
    'forgot_password' => 'Esqueceu sua senha?',
    'confirm_password' => 'Confirmar Senha',
    'name' => 'Nome',

    // Success Messages
    'login_success' => 'Login realizado com sucesso.',
    'logout_success' => 'Logout realizado com sucesso.',
    'register_success' => 'Conta criada com sucesso.',
    'password_reset_sent' => 'Link de redefinição de senha enviado.',
    'password_reset_success' => 'Sua senha foi redefinida com sucesso.',
    'email_verified' => 'Seu e-mail foi verificado.',

    // Error Messages
    'invalid_credentials' => 'E-mail ou senha inválidos.',
    'account_disabled' => 'Sua conta foi desativada.',
    'email_not_verified' => 'Por favor, verifique seu endereço de e-mail.',
    'too_many_attempts' => 'Muitas tentativas. Tente novamente em :seconds segundos.',
    'token_expired' => 'Este token expirou.',
    'token_invalid' => 'Este token é inválido.',

    // OTP
    'otp_sent' => 'Código de verificação enviado para :destination.',
    'otp_sent_sms' => 'SMS enviado para :phone.',
    'otp_sent_email' => 'E-mail enviado para :email.',
    'otp_invalid' => 'Código de verificação inválido.',
    'otp_expired' => 'O código de verificação expirou.',
    'otp_resent' => 'Código de verificação reenviado.',
    'enter_otp' => 'Digite o código de verificação de :digits dígitos',

    // Two-Factor Authentication
    'two_factor' => 'Autenticação de Dois Fatores',
    'two_factor_code' => 'Código de Autenticação',
    'two_factor_recovery' => 'Usar Código de Recuperação',
    'recovery_code' => 'Código de Recuperação',
    'enable_2fa' => 'Habilitar Autenticação de Dois Fatores',
    'disable_2fa' => 'Desabilitar Autenticação de Dois Fatores',
    '2fa_enabled' => 'Autenticação de dois fatores habilitada com sucesso.',
    '2fa_disabled' => 'Autenticação de dois fatores desabilitada.',
    '2fa_invalid_code' => 'Código de autenticação inválido.',
    '2fa_recovery_used' => 'Código de recuperação usado. :remaining códigos restantes.',
    'scan_qr' => 'Escaneie este código QR com seu aplicativo de autenticação',
    'backup_codes' => 'Códigos de Backup',
    'recovery_codes_regenerated' => 'Códigos de recuperação regenerados.',
    'save_recovery_codes' => 'Salve estes códigos de recuperação em um local seguro.',

    // Social Auth
    'continue_with' => 'Continuar com :provider',
    'social_link_success' => 'Conta :provider vinculada com sucesso.',
    'social_unlink_success' => 'Conta :provider desvinculada.',
    'social_already_linked' => 'Esta conta :provider já está vinculada a outro usuário.',
    'social_auth_failed' => 'Falha na autenticação com :provider.',
    'social_email_taken' => 'O e-mail desta conta :provider já está em uso.',

    // Profile
    'profile' => 'Perfil',
    'update_profile' => 'Atualizar Perfil',
    'profile_updated' => 'Perfil atualizado com sucesso.',
    'password_updated' => 'Senha atualizada com sucesso.',
    'current_password' => 'Senha Atual',
    'new_password' => 'Nova Senha',
    'current_password_incorrect' => 'A senha atual está incorreta.',
];

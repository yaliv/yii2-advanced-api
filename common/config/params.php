<?php

/**
 * Important params should be categorized with prefix.
 * 
 * For example, regarding auth process, use "auth" prefix
 */
return [
    /** The possible params are email, username, and both */
    'auth.loginWith' => \common\forms\LoginForm::LOGIN_WITH_BOTH,
    
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'loginByEmail' => false,
];

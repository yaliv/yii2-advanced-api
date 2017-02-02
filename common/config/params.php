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
    /** set password reset token expired 60 sec * 60 Min  */
    'user.passwordResetTokenExpire' => 3600,
    /** set login params. If value is true, then should login by email. else, login by username. */
    'loginByEmail' => false,
    /**
     * Is user must be use strong password?
     * there is only 5 choices : simple | normal | fair | medium | strong
     */
    'strongPassword' => 'simple',
];

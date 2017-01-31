<?php

namespace api\config;

use yii\base\Object;

class ApiCode extends Object
{
    /**
     * ApiCode for auth
     */
    const REGISTER_SUCCESS             = 10;
    const REGISTER_FAILED              = 11;
    const LOGIN_SUCCESS                = 12;
    const LOGIN_FAILED                 = 13;
    const LOGOUT_SUCCESS               = 14;
    const FORGOT_PASSWORD_SUCCESS      = 15;
    const FORGOT_PASSWORD_FAILED       = 16;
    const RESET_PASSWORD_TOKEN_INVALID = 17;
    const RESET_PASSWORD_SUCCESS       = 18;
    const RESET_PASSWORD_TOKEN_VALID   = 19;


    /**
     * ApiCode for invalid configuration
     */
    const DEVICE_IDENTIFIER_NOT_FOUND = 100;
}

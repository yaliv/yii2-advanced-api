<?php

namespace api\config;

use yii\base\Object;

class ApiCode extends Object {
    /**
     * ApiCode for auth
     */
    const REGISTER_SUCCESS = 10;
    const REGISTER_FAILED  = 11;
    const LOGIN_SUCCESS    = 12;
    const LOGIN_FAILED     = 13;

    /**
     * ApiCode for invalid configuration
     */
    const DEVICE_IDENTIFIER_NOT_FOUND = 100;

}
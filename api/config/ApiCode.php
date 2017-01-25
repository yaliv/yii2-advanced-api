<?php

namespace api\config;

use yii\base\Object;

class ApiCode extends Object {
    /**
     * ApiCode for auth
     */
    const REGISTER_SUCCESS = 10;
    const REGISTER_FAILED = 11;

    /**
     * ApiCode for invalid configuration
     */
    const DEVICE_IDENTIFIER_NOT_FOUND = 100;

}
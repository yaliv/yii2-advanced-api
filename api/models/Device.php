<?php

namespace api\models;

class Device extends \common\models\Device
{
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }
}
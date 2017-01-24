<?php

namespace api\models;

use common\models\Device;
use yii\base\InvalidConfigException;

class User extends \common\models\User
{

    /**
     * @var Device
     */
    private $_activeDevice;

    /**
     * Setup a new device after login
     */
    public function setupNewDevice($deviceIdentifier)
    {
        if (empty($deviceIdentifier)) {
            throw new InvalidConfigException('Device identifier cannot be empty');
        }
        // deactivate all devices with same identifier
        Device::updateAll(['status' => Device::STATUS_INACTIVE], ['identifier' => $deviceIdentifier]);

        // create a new device
        $device             = new Device();
        $device->userId     = $this->id;
        $device->identifier = $deviceIdentifier;
        $device->status     = Device::STATUS_ACTIVE;
        $device->save();

        $this->_activeDevice = $device;
    }

    public function getActiveDevice()
    {
        return $this->_activeDevice;
    }

    public function setActiveDevice($activeDevice)
    {
        $this->_activeDevice = $activeDevice;
    }

    ///////////////////////
    // Relationship Area //
    ///////////////////////


    /////////////////////////////
    // Identity Interface Area //
    /////////////////////////////

    /**
     * Find identity by access token, used in API
     *
     * @param mixed $token
     * @param null $type
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // find user by accessToken
        $user = User::find()
            ->where([
                self::tableName() . '.status' => self::STATUS_ACTIVE
            ])
            ->joinWith([
                'devices' => function ($query) use ($token) {
                    $query->andWhere([
                        Device::tableName() . '.accessToken' => $token,
                        Device::tableName() . '.status'      => Device::STATUS_ACTIVE
                    ]);
                }
            ], true)
            ->one();

        if ($user) {
            $user->activeDevice = $user->devices[0];
        }

        return $user;
    }
    ///////////////////////////////
    // End of Identity Interface //
    ///////////////////////////////
}

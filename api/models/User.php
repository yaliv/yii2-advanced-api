<?php

namespace api\models;

use yii\base\InvalidConfigException;

/**
 * Class User
 *
 * @author Haqqi <me@haqqi.net>
 * @package api\models
 * 
 * @var Device $activeDevice
 */
class User extends \common\models\User
{
    ///////////////////////
    // Relationship Area //
    ///////////////////////

    public function getDevices()
    {
        return $this->hasMany(Device::className(), ['userId' => 'id'])
            ->inverseOf('user');
    }

    /////////////////////////////
    // Identity Interface Area //
    /////////////////////////////

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $activeDevice = Device::find()
            ->joinWith(['user'])
            ->where([
                Device::tableName() . '.accessToken' => $token,
                Device::tableName() . '.status'      => Device::STATUS_ACTIVE,
                self::tableName() . '.status'        => self::STATUS_ACTIVE
            ])->one();

        if ($activeDevice) {
            /** @var User $user */
            $user = $activeDevice->user;
            $user->setActiveDevice($activeDevice);

            return $user;
        }

        return null;
    }
    ///////////////////////////////
    // End of Identity Interface //
    ///////////////////////////////

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

    /**
     * @var Device
     */
    private $_activeDevice;

    public function getActiveDevice()
    {
        return $this->_activeDevice;
    }

    public function setActiveDevice($activeDevice)
    {
        $this->_activeDevice = $activeDevice;
    }
}

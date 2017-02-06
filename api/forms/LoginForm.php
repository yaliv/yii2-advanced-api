<?php

namespace api\forms;

use api\models\User;
use yii\base\InvalidConfigException;

/**
 * Class LoginForm
 * @package api\forms
 * @author Haqqi <me@haqqi.net>
 */
class LoginForm extends \common\forms\LoginForm
{
    /**
     * @var string Special device identifier for new device
     */
    public $deviceIdentifier;

    public function __construct($deviceIdentifier, $config = [])
    {
        if (empty($deviceIdentifier)) {
            throw new InvalidConfigException('Device identifier could not be found.');
        }

        $this->deviceIdentifier = $deviceIdentifier;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // set user classname to api
        $this->_userClass = User::className();
    }


    /**
     * Override the parent login mechanism, as in the API, it will be a different login scheme.
     * The real login process in stateless API is creating a new access token for a device.
     *
     * @author Haqqi <me@haqqi.net>
     */
    public function login()
    {
        if ($this->validate()) {
            /** @var User $user */
            $user = $this->getUser();
            // create new device
            $user->setupNewDevice($this->deviceIdentifier);
            return \Yii::$app->user->login($user);
        }
        return false;
    }
}

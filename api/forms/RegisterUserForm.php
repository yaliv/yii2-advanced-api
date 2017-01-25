<?php


namespace api\forms;


use common\models\User;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class RegisterUserForm extends Model
{
    public $username, $email, $password;

    public $deviceIdentifier;

    public function __construct($deviceIdentifier, $config = []) {
        if(empty($deviceIdentifier)) {
            throw new InvalidConfigException('Device identifier could not be found.');
        }

        $this->deviceIdentifier = $deviceIdentifier;

        parent::__construct($config);
    }

    public function rules()
    {
        return ArrayHelper::merge([
          [['username', 'email', 'password'], 'required'],
          [['username', 'email', 'password'], 'trim'],
          ['email', 'email'],
          [['username', 'email'], 'unique', 'targetClass' => User::className()]
        ], User::getPasswordRules());
    }

    public function register()
    {
        $user = new User();
        $user->setScenario(User::SCENARIO_REGISTER_VIA_API);
        $user->attributes = $this->attributes;
        $user->save();

        return $user;
    }


}
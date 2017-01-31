<?php

namespace common\forms;

use common\models\User;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ChangePasswordForm
 */
class ResetPasswordForm extends Model {

  /**
   * @var $password
   */
  public $password;

  /**
   * @var User
   */
  private $_user;

  public function __construct($passwordResetToken, $config = []) {

    // find user by password reset token and validate it before. reference: https://github.com/yiisoft/yii2-app-advanced/blob/master/common/models/User.php
    $this->_user = User::findByPasswordResetToken($passwordResetToken);

    if (!$this->_user) {
      throw new InvalidConfigException('Password reset token is invalid');
    }
    
    parent::__construct($config);
  }


  public function rules() {
    return ArrayHelper::merge([
      [['password'], 'required'],
      [['password'], 'trim'],
    ], User::getPasswordRules());
  }
  
  public function resetPassword() {
    $user = $this->_user;
    $user->password = $this->password;
    $user->removePasswordResetToken();
    return $user->save();
  }
}
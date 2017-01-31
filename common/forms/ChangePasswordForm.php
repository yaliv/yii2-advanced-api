<?php

namespace common\forms;

use common\models\User;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ChangePasswordForm
 * @package api\forms
 */
class ChangePasswordForm extends Model {
  /**
   * @var string
   */
  public $password;

  /**
   * @var string
   */
  public $oldPassword;

  /**
   * @var User
   */
  private $_user;

  public function init() {
    parent::init();
    
    $this->_user = \Yii::$app->user->getIdentity();
  }


  public function rules() {
    return ArrayHelper::merge([
      [['password','oldPassword'], 'required'],
      [['password','oldPassword'], 'trim'],
      [['oldPassword'], 'validateOldPassword'],
      [['password'], 'compare', 'compareAttribute' => 'oldPassword', 'operator' => '!==']
    ], User::getPasswordRules());
  }
  
  public function validateOldPassword($attribute, $params) {
    if(!$this->hasErrors()) {
      if(!$this->_user->validatePassword($this->{$attribute})) {
        $this->addError($attribute, 'Old password is not matched.');
      }
    }
  }
  
  public function changePassword() {
    $user = $this->_user;
    $user->password = $this->password;
    return $user->save(false);
  }
}
<?php

namespace api\modules\v1;
use api\filters\AccessTokenAuth;
use yii\base\BootstrapInterface;
use yii\filters\auth\CompositeAuth;

/**
 * Class Module
 * @package api\modules\v1
 * @author Haqqi <me@haqqi.net>
 */
class Module extends \yii\base\Module implements BootstrapInterface {
  public function init() {
    parent::init();

    // configure module using config object
    \Yii::configure($this, require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'main.php'));
  }

  public function bootstrap($app) {
    // adding routes dynamicaly
    $app->urlManager->addRules(
      require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.php'),
      false
    );

    // custom access token auth can be customized here
    /** @author Haqqi <me@haqqi.net> */
    \Yii::$container->set(AccessTokenAuth::className(), [
      'accessTokenKey' => 'X-Device-accessToken'
    ]);
    
    // set the auth method
    /** @author Haqqi <me@haqqi.net> */
    \Yii::$container->set(CompositeAuth::className(), [
      'authMethods' => [
        AccessTokenAuth::className()
      ]
    ]);
  }
}

<?php

namespace api\modules\v1;
use yii\base\BootstrapInterface;

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
  }
}

<?php

namespace common\models;

use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class Device
 * @package common\models
 * @author Haqqi <me@haqqi.net>
 * 
 * @property string $accessToken
 * @property int $osType
 * @property string $osVersion
 * @property string $identifier
 * @property string $model
 * @property string $appVersion
 * @property double $latitude
 * @property double $longitude
 * @property string $ip
 * @property string $timezone
 * @property string $status
 * @property string $createdAt
 * @property string $updatedAt
 * 
 * @property User $user
 */
class Device extends ActiveRecord {

  const SCENARIO_CREATE      = 'create';
  const SCENARIO_EDIT        = 'edit';
  const SCENARIO_UPDATE_INFO = 'updateInfo';

  const STATUS_ACTIVE   = 'active';
  const STATUS_INACTIVE = 'inactive';

  // references: https://documentation.onesignal.com/reference#add-a-device
  const TYPE_IOS                = 0;
  const TYPE_ANDROID            = 1;
  const TYPE_AMAZON             = 2;
  const TYPE_WINDOWS_PHONE_MPNS = 3;
  const TYPE_CHROME_APP         = 4;
  const TYPE_CHROME_WEB_PUSH    = 5;
  const TYPE_WINDOWS_PHONE_WNS  = 6;
  const TYPE_SAFARI             = 7;
  const TYPE_FIREFOX            = 8;
  const TYPE_MACOS              = 9;

  public function behaviors() {
    return [
      [
        'class'              => TimestampBehavior::className(),
        'value'              => new Expression("'" . date('Y-m-d H:i:s') . "'"),
        'createdAtAttribute' => 'createdAt',
        'updatedAtAttribute' => 'updatedAt'
      ],
      [
        'class'      => AttributeBehavior::className(),
        'attributes' => [
          ActiveRecord::EVENT_BEFORE_INSERT => 'accessToken'
        ],
        'value'      => [$this, 'generateAccessToken']
      ]
    ];
  }

  public static function tableName() {
    return '{{%device}}';
  }

  public function fields() {
    $fields = parent::fields();

    return $fields;
  }


  public function attributeLabels() {
    return [
      'userId'      => 'User Id',
      'accessToken' => 'Access Token',
      'osType'      => 'OS Type',
      'osVersion'   => 'OS Version',
      'identifier'  => 'Identifier',
      'model'       => 'Model',
      'appVersion'  => 'App Version',
      'latitude'    => 'Latitude',
      'longitude'   => 'Longitude',
      'ip'          => 'IP',
      'timezone'    => 'Timezone',
      'status'      => 'Status',
      'createdAt'   => 'Created at',
      'updatedAt'   => 'Updated at'
    ];
  }

  public function scenarios() {
    $scenarios = parent::scenarios();

    $scenarios[self::SCENARIO_UPDATE_INFO] = [
      'appVersion', 'osType', 'osVersion', 'model', 'latitude', 'longitude', 'timezone'
    ];

    return $scenarios;
  }

  /**
   * Generate accessToken, called in event before insert by AttributeBehavior
   */
  public function generateAccessToken($event) {
    $randomString = \Yii::$app->security->generateRandomString();
    // sha1 the token, with additional time
    $accessToken = sha1($randomString . $this->userId) . '.' . time();

    return $accessToken;
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////// Relationship Area //////////////////////////
  ////////////////////////////////////////////////////////////////////

  public function getUser() {
    return $this->hasOne(User::className(), ['id' => 'userId'])->inverseOf('devices');
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////// End of Relationship Area ///////////////////////
  ////////////////////////////////////////////////////////////////////
}
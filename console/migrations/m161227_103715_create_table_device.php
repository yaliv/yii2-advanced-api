<?php

use common\models\Device;
use common\models\User;
use console\components\Migration;

/**
 * Class m161227_103715_create_table_device
 * @author alvian burhanuddin <alvian.burhanuddin@akupeduli.org>
 */
class m161227_103715_create_table_device extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Device::tableName(), [
            'id'          => $this->bigPrimaryKey()->unsigned(),
            'userId'      => $this->bigInteger()->unsigned()->notNull(),
            'accessToken' => $this->string(255)->unique()->notNull(),
            'osType'      => $this->integer(),
            'osVersion'   => $this->string(100),
            'identifier'  => $this->string(100),
            'model'       => $this->string(100),
            'appVersion'  => $this->string(100),
            'latitude'    => $this->double(),
            'longitude'   => $this->double(),
            'ip'          => $this->string(50),
            'timezone'    => $this->string(100),
            'status'      => $this->string(50)->defaultValue(Device::STATUS_ACTIVE),
            'createdAt'   => $this->dateTime(),
            'updatedAt'   => $this->dateTime()
        ], $this->tableOptions);

        $this->addForeignKey(
            'fk-device-user',
            Device::tableName(),
            'userId',
            User::tableName(),
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable(Device::tableName());
    }

}

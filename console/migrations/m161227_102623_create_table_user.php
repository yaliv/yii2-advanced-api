<?php

use common\models\User;
use console\components\Migration;

/**
 * Class m161227_102623_create_table_user
 * @author alvian burhanuddin <alvian.burhanuddin@akupeduli.org>
 */
class m161227_102623_create_table_user extends Migration
{
    public function safeUp()
    {
        $this->createTable(User::tableName(), [
            'id'                 => $this->bigPrimaryKey()->unsigned(),
            'username'           => $this->string(100)->notNull()->unique(),
            'email'              => $this->string(100)->notNull()->unique(),
            'passwordHash'       => $this->string(255),
            'passwordResetToken' => $this->string(255),
            'status'             => $this->string(50)->notNull()->defaultValue(User::STATUS_ACTIVE),
            'authKey'            => $this->string(255),
            'createdAt'          => $this->dateTime(),
            'updatedAt'          => $this->dateTime()
        ], $this->tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable(User::tableName());
    }
}

<?php
/**
 * @copyright Copyright (c) 2015 ET-Soft
 * @license MIT
 * @link https://github.com/et-soft/yii2-migrations-create
 *
 * @copyright Copyright (c) 2017 Muhammad Yahya Muhaimin
 */
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\db\Schema;

/**
 * This command create migration files from existing database tables.
 *
 * @author Evgeny Titov <etsoft2015@gmail.com>
 * @since 0.1
 *
 * @author Muhammad Yahya Muhaimin <myahyamuhaimin@yahoo.com>
 */
class RevmigrateController extends Controller
{
    /**
     * This command creates migration files for all tables in current database.
     */
    public function actionIndex()
    {
        if (!$this->confirm('Create migration files for all database tables?')) {
            return;
        }

        $tables = Yii::$app->db->schema->getTableSchemas();

        foreach ($tables as $table) {
            $this->createMigration($table);
        }
    }

    /**
     * This command creates a migration file for selected DB table.
     *
     * @param $table_name - name of the table
     */
    public function actionTable($table_name = null)
    {
        if (!$this->confirm('Create migration file for table ' . $table_name . '?')) {
            return;
        }

        $table = Yii::$app->db->schema->getTableSchema($table_name);

        $this->createMigration($table);
    }

    /**
     * Method for creating migration file for every DB table.
     *
     * @param $table - the table metadata
     */
    private function createMigration($table)
    {
        // Prefix for created filename.
        $prefix = 'm' . date('ymd_His', time());

        // Array of unique keys.
        // $uniqueKeys = $this->getUniqueKeys($table);
        $uniqueKeys = [];
        
        // Array of fields.
        $fields = array();

        if (property_exists($table, 'columns')) {
            foreach ($table->columns as $column) {
                $fields[] = [ 'property' => $column->name, 'decorators' => $this->buildColumnSchema($column, $uniqueKeys)];
            }
        }

        $params = array('table' => "{{%{$table->name}}}", 'className' => "{$prefix}_" . $table->name, 'fields' => $fields, 'foreignKeys' => array());

        $tpl = $this->renderFile(Yii::getAlias('@yii/views/createTableMigration.php'), $params);

        file_put_contents(Yii::getAlias('@app/migrations')."/{$prefix}_".$table->name.'.php', $tpl);
    }

    /**
     * Method for getting unique keys for every DB table.
     *
     * @param $table - the table metadata
     */
    private function getUniqueKeys($table)
    {
        $dbSchema = null;
        
        switch (\Yii::$app->db->driverName) {
            case 'mysql':
                $dbSchema = new \yii\db\mysql\Schema();
        }

        return $dbSchema->findUniqueIndexes($table);
    }

    /**
     * Method for generating string with Yii2 schema builder methods based on column description.
     *
     * @param $column - the column metadata
     * @return string
     */
    private function buildColumnSchema($column, $uniqueKeys)
    {
        $result = '';

        $length = null;
        $precision = null;
        $scale = null;

        if (!empty($column->size)) {
            $length = $column->size;
        }
        if (!empty($column->precision)) {
            $precision = $column->precision;
        }
        if (!empty($column->scale)) {
            $scale = $column->scale;
        }

        if ($column->isPrimaryKey == 1) {
            if ($column->type == SCHEMA::TYPE_BIGINT) {
                $result .= "bigPrimaryKey({$length})";
            } else {
                $result .= "primaryKey({$length})";
            }
        } else {
            switch ($column->type) {
                case SCHEMA::TYPE_CHAR:
                    $result .= "char({$length})";
                    break;
                case SCHEMA::TYPE_STRING:
                    $result .= "string({$length})";
                    break;
                case SCHEMA::TYPE_TEXT:
                    $result .= "text()";
                    break;
                case SCHEMA::TYPE_SMALLINT:
                    $result .= "smallInteger({$length})";
                    break;
                case SCHEMA::TYPE_INTEGER:
                    $result .= "integer({$length})";
                    break;
                case SCHEMA::TYPE_BIGINT:
                    $result .= "bigInteger({$length})";
                    break;
                case SCHEMA::TYPE_FLOAT:
                    $result .= "float({$precision})";
                    break;
                case SCHEMA::TYPE_DOUBLE:
                    $result .= "double({$precision})";
                    break;
                case SCHEMA::TYPE_DECIMAL:
                    $result .= "decimal({$precision}, {$scale})";
                    break;
                case SCHEMA::TYPE_DATETIME:
                    $result .= "dateTime({$precision})";
                    break;
                case SCHEMA::TYPE_TIMESTAMP:
                    $result .= "timestamp({$precision})";
                    break;
                case SCHEMA::TYPE_TIME:
                    $result .= "time({$precision})";
                    break;
                case SCHEMA::TYPE_DATE:
                    $result .= "date()";
                    break;
                case SCHEMA::TYPE_BINARY:
                    $result .= "binary({$length})";
                    break;
                case SCHEMA::TYPE_BOOLEAN:
                    $result .= "boolean()";
                    break;
                case SCHEMA::TYPE_MONEY:
                    $result .= "money({$precision}, {$scale})";
                    break;
            }
        }

        // if ($uniqueKeys !== null) {
        if (in_array($column->name, $uniqueKeys, true)) {
            $result .= '->unique()';
        }
        // }
        if ($column->unsigned == true) {
            $result .= '->unsigned()';
        }
        if ($column->allowNull != true) {
            $result .= '->notNull()';
        }
        if ($column->defaultValue != '') {
            $result .= "->defaultValue('{$column->defaultValue}')";
        }
        if ($column->comment != '') {
            $result .= "->comment('{$column->comment}')";
        }

        return $result;
    }
}

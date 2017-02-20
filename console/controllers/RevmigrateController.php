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
            $this->createSchemaMigration($table);
        }

        foreach ($tables as $table) {
            $this->createFKMigration($table);
        }
    }

    /**
     * This command creates a migration file for selected DB table.
     *
     * @param $tableName - name of the table
     */
    public function actionTable($tableName = null)
    {
        if (!$this->confirm('Create migration file for table ' . $tableName . '?')) {
            return;
        }

        $table = Yii::$app->db->schema->getTableSchema($tableName);

        $this->createSchemaMigration($table);

        $this->createFKMigration($table);
    }

    /**
     * Method for creating schema migration file for every DB table.
     *
     * @param $table - the table metadata
     */
    private function createSchemaMigration($table)
    {
        // Prefix for created filename.
        $prefix = 'm' . date('ymd_His', time()) . '_create_table_';

        // Original tablename.
        $oriTablename = $this->getOriTablename($table->name);

        // Array of columns with unique key.
        $uniqueCols = $this->getUniqueCols($table);

        // Array of fields.
        $fields = array();

        if (property_exists($table, 'columns') && count($table->columns)) {
            foreach ($table->columns as $column) {
                $fields[] = ['property' => $column->name, 'decorators' => $this->buildColumnSchema($column, $uniqueCols)];
            }
        } else {
            return;
        }

        $params = array('table' => "{{%{$oriTablename}}}", 'className' => $prefix . $oriTablename, 'fields' => $fields, 'foreignKeys' => array());

        $tpl = $this->renderFile(Yii::getAlias('@yii/views/createTableMigration.php'), $params);

        file_put_contents(Yii::getAlias('@app/migrations')."/{$prefix}".$oriTablename.'.php', $tpl);
    }

    /**
     * Method for creating foreign keys migration file for every DB table.
     *
     * @param $table - the table metadata
     */
    private function createFKMigration($table)
    {
        // Prefix for created filename.
        $prefix = 'm' . date('ymd_His', time()+1) . '_add_fk_';

        // Original tablename.
        $oriTablename = $this->getOriTablename($table->name);

        // Array of foreign keys.
        $foreignKeys = array();

        if (property_exists($table, 'foreignKeys') && count($table->foreignKeys)) {
            // Handles different Yii versions.
            switch (Yii::getVersion()) {
                case '2.0.10':
                    foreach ($table->foreignKeys as $key) {
                        $col = '';
                        $fkData = [];

                        foreach ($key as $fk => $pk) {
                            if ($fk === 0) {
                                $oriRelTablename = $this->getOriTablename($pk);

                                $fkData['relatedTable'] = "{{%{$oriRelTablename}}}";
                                $fkData['idx'] = 'fk_'.$oriTablename."_{$oriRelTablename}_idx";
                                $fkData['fk'] = 'fk_'.$oriTablename."_{$oriRelTablename}";
                            } else {
                                $col = $fk;
                            }
                        }

                        $foreignKeys[$col] = $fkData;
                    }

                    break;

                default:
                    foreach ($table->foreignKeys as $key => $arr) {
                        $col = '';
                        $fkData = [];

                        foreach ($arr as $fk => $pk) {
                            if ($fk === 0) {
                                $oriRelTablename = $this->getOriTablename($pk);

                                $fkData['relatedTable'] = "{{%{$oriRelTablename}}}";
                                $fkData['idx'] = $key.'_idx';
                                $fkData['fk'] = $key;
                            } else {
                                $col = $fk;
                                $fkData['relatedColumn'] = $pk;
                            }
                        }

                        $foreignKeys[$col] = $fkData;
                    }

                    break;
            }
        } else {
            return;
        }

        $params = array('table' => "{{%{$oriTablename}}}", 'className' => $prefix . $oriTablename, 'fields' => array(), 'foreignKeys' => $foreignKeys);

        $tpl = $this->renderFile(Yii::getAlias('@yii/views/createTableMigration.php'), $params);

        file_put_contents(Yii::getAlias('@app/migrations')."/{$prefix}".$oriTablename.'.php', $tpl);
    }

    /**
     * Method for getting original tablename if using tablePrefix.
     *
     * @param $tableName - name of the table
     *
     * @return string original tablename (prefix is omitted)
     */
    private function getOriTablename($tableName)
    {
        $prefix = Yii::$app->db->tablePrefix;

        if (substr($tableName, 0, strlen($prefix)) == $prefix) {
            return substr($tableName, strlen($prefix));
        }

        return $tableName;
    }

    /**
     * Method for getting columns with unique key for every DB table.
     *
     * @param $table - the table metadata
     *
     * @return array columns with unique key
     */
    private function getUniqueCols($table)
    {
        $uniqueKeys = Yii::$app->db->schema->findUniqueIndexes($table);

        $uniqueCols = [];

        foreach ($uniqueKeys as $name) {
            foreach ($name as $col) {
                $uniqueCols[] = $col;
            }
        }

        return $uniqueCols;
    }

    /**
     * Method for generating string containing Yii2 schema builder methods based on column description.
     *
     * @param $column - the column metadata
     * @param $uniqueCols - columns with unique key
     *
     * @return string containing schema builder methods
     */
    private function buildColumnSchema($column, $uniqueCols)
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
                    $result .= 'text()';
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
                    $result .= 'date()';
                    break;
                case SCHEMA::TYPE_BINARY:
                    $result .= "binary({$length})";
                    break;
                case SCHEMA::TYPE_BOOLEAN:
                    $result .= 'boolean()';
                    break;
                case SCHEMA::TYPE_MONEY:
                    $result .= "money({$precision}, {$scale})";
                    break;
            }
        }

        if (in_array($column->name, $uniqueCols, true)) {
            $result .= '->unique()';
        }
        if ($column->unsigned == true) {
            $result .= '->unsigned()';
        }
        if ($column->allowNull != true) {
            $result .= '->notNull()';
        }
        if (isset($column->defaultValue)) {
            $result .= "->defaultValue('{$column->defaultValue}')";
        }
        if ($column->comment != '') {
            $result .= "->comment('{$column->comment}')";
        }

        return $result;
    }
}

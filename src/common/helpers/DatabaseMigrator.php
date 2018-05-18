<?php

namespace modular\common\helpers;


use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


/**
 * Class DatabaseMigrator
 *
 * @property string $result read-only результат выполненной миграции
 * @property array  $log    read-only массив сообщений об ошибках
 *
 * @package modular\common\helpers
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 * @version v1.0
 *
 * Пример использования
 *
 * ```php
 *
 * $migrator = new DatabaseMigrator([
 *      'renderResult' => false,
 *      'scheme' => [
 *      [
 *          [ 'rms_mod_pay__v2_transactions', 'rms_mod_epay__txn' ],
 *          [
 *              'app_uid'     => 'appUid',
 *              'uid'         => 'txn',
 *              'msisdn',
 *              'amount',
 *              'currency',
 *              'description' => 'order_description',
 *              'localize'    => 'localization',
 *              'create_hash' => 'hash_request',
 *              'result_hash' => function ( $source ) {
 *                  // `$source` is an object with source model properties ( as key->value pairs )
 *                  // callback functionality here
 *              },
 *              'updated_at',
 *              'created_at',
 *          ],
 *      ],
 * ]);
 * return $migrator->migrate();
 *
 * ```
 *
 */
class DatabaseMigrator extends Object
{


    /**
     * @var array $scheme массив схемы миграции таблиц
     */
    public $scheme = [];


    /**
     * @var bool $truncateTarget если требуется очищать целевую таблицу перед миграцией
     */
    public $truncateTarget = true;


    /**
     * @var bool $renderResult
     */
    public $renderResult = true;


    /**
     * @var string $nullTargetValue
     */
    public $nullTargetValue = '';


    /**
     * @var string $_targetTableName
     */
    private $_targetTableName = '';


    /**
     * @var string $_sourceTableName
     */
    private $_sourceTableName = '';


    /**
     * @var array $_log
     */
    private $_log = [];


    /**
     * @var array $_migrateResult
     */
    private $_migrateResult = [];


    protected function renderResult()
    {
        return Html::tag('div',
            Html::tag('div',
                $this->result,
                [
                    'class' => 'panel-body',
                ]
            ),
            [
                'class' => 'panel panel-default',
            ]
        );
    }


    public function getLog()
    {
        return $this->_log;
    }


    public function getResult()
    {
        $result = "Результат миграции:\r\n\r\n";
        if (empty($this->_log) && !empty($this->scheme)) {
            foreach ($this->scheme as $tableRelation) {
                if ($this->isValidRelation($tableRelation)) {
                    if (!empty($this->_migrateResult[ $tableRelation[ 0 ][ 0 ] ])) {
                        $result .= "Данные `<b>" . $tableRelation[ 0 ][ 1 ] . "</b>` успешно перенесены в `<b>" . $tableRelation[ 0 ][ 0 ] . "</b>`\r\n";
                        $result .= "В таблице `<b>" . $tableRelation[ 0 ][ 0 ] . "</b>` создано новых записей : <b>" . $this->_migrateResult[ $tableRelation[ 0 ][ 0 ] ] . "</b>\r\n\r\n";
                    }
                }
            }
        } else {
            $result .= 'возникли ошибки миграции';
        }
        return Html::tag('pre', preg_replace("/\r\n/i", "<br/>", $result));
    }


    public function readyFor($type = '', $values = [])
    {
        $readyValues = [];
        if (!empty($values)) {
            switch ($type) {
                case 'keys' :
                    $itemMark = "`";
                    break;
                case 'values' :
                    $itemMark = "'";
                    break;
                default :
                    $itemMark = "";
                    break;
            }
            foreach ($values as $value) {
                $readyValues[] = $itemMark . $value . $itemMark;
            }
        }
        return $readyValues;
    }


    public function migrate()
    {
        if (!empty($this->scheme)) {
            foreach ($this->scheme as $tableRelation) {
                if ($this->isValidRelation($tableRelation)) {
                    list($this->_targetTableName, $this->_sourceTableName) = $tableRelation[ 0 ];
                    $fieldsRelations = ArrayHelper::merge(['id' => 'id',], $tableRelation[ 1 ]);
                    $sourceModels = \Yii::$app->db->createCommand("SELECT * FROM " . $this->_sourceTableName . " ORDER BY id ASC")->queryAll();
                    if (!empty($fieldsRelations) && !empty($sourceModels)) {
                        if ($this->truncateTarget) {
                            \Yii::$app->db->createCommand('TRUNCATE ' . $this->_targetTableName)->execute();
                        }
                        $_migrateCommand = '';
                        $_rowsMigrating = 0;
                        foreach ($sourceModels as $sourceModel) {
                            $modelFields = [];
                            // обрабатывает взаимосвязи
                            foreach ($fieldsRelations as $targetField => $sourceField) {
                                $value = null;
                                if (is_int($targetField)) {
                                    $value = $sourceModel[ $sourceField ];
                                } else {
                                    if ($sourceField instanceof \Closure) {
                                        $value = call_user_func($sourceField, (object)$sourceModel);
                                    } elseif (preg_match("/:/i", $sourceField)) {
                                        list($previousValue, $pattern) = explode(":", $sourceField);
                                        if (preg_match("/" . $pattern . "/i", $previousValue)) {
                                            $value = $previousValue;
                                        }
                                    } else {
                                        $value = $sourceModel[ $sourceField ];
                                    }
                                }
                                $modelFields[ (is_int($targetField)) ? $sourceField : $targetField ] = (!empty($value)) ? $value : $this->nullTargetValue;
                            }
                            $_migrateCommand .= "INSERT INTO"
                                . "`" . $this->_targetTableName . "`"
                                . "(" . implode(", ", $this->readyFor('keys', array_keys($modelFields))) . ")"
                                . "VALUES"
                                . "(" . implode(", ", $this->readyFor('values', array_values($modelFields))) . ");";
                            $_rowsMigrating++;
                        }
                        \Yii::$app->db->createCommand($_migrateCommand)->execute();
                        $this->_migrateResult[ $this->_targetTableName ] = $_rowsMigrating;
                    }
                }
            }
        }
        return ($this->renderResult) ? $this->renderResult() : $this->result;
    }


    public function isValidRelation($tableRelation)
    {
        return (
            !empty($tableRelation[ 0 ])
            && ArrayHelper::isIndexed($tableRelation[ 0 ])
            && !empty($tableRelation[ 1 ])
        );
    }

}
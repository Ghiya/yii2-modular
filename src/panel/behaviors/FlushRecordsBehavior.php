<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\Controller;


/**
 * Class FlushRecordsBehavior поведение контроллера для автоматического удаления устаревших записей согласно
 * установленным параметрам очистки. При событии [[\yii\web\Controller::EVENT_BEFORE_ACTION]] производит проверку на
 * наличие подходящих записей и удаляет найденные.
 *
 * @property ActiveRecord $owner
 *
 * @package panel\behaviors
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class FlushRecordsBehavior extends Behavior
{


    /**
     * @var string $recordClass строковое название класса модели записи
     */
    public $recordClass = '';


    /**
     * @var int $interval максимальный срок хранения записей ( дни )
     */
    public $interval;


    /**
     * @var string $attribute аттрибут модели для отбора записей на удаление
     */
    public $attribute = 'created_at';


    /**
     * @var string $permission разрешённая RBAC роль пользователя для выполнения очистки
     */
    public $permission;


    /**
     * @var int $bottomTimestamp
     */
    protected $bottomTimestamp;


    /**
     * @var int $counter
     */
    protected $counter = 0;


    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->recordClass)) {
            throw new InvalidConfigException("Класс модели `recordClass` для автоматической очистки записей должно быть определён.");
        }
        if (empty($this->interval)) {
            throw new InvalidConfigException("Свойство интервала активности `interval` для автоматической очистки должно быть определено.");
        }
    }


    /**
     * @inheritdoc
     */
    public function events()
    {
        return
            [
                Controller::EVENT_BEFORE_ACTION => 'beforeAction',
            ];
    }


    /**
     * Производит проверку на наличие записей для очистки и удаляет найденные.
     */
    public function beforeAction()
    {
        if (empty($this->permission) || \Yii::$app->user->can($this->permission)) {
            $this->flushDeprecated();
            if (!empty($this->counter)) {
                \Yii::$app->session->setFlash(
                    'info',
                    "Произведена автоматическая очистка <strong>$this->counter</strong> записей созданных ранее чем <strong>" .
                    \Yii::$app->formatter->asDatetime($this->getBottomTimestamp(), "php:d.m.Y") .
                    "</strong>."
                );
            }
        }
    }


    /**
     * Удаляет найденные записи.
     *
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    protected function flushDeprecated()
    {
        foreach ($this->getRecords() as $record) {
            $this->counter = $record->delete() ? $this->counter + 1 : $this->counter;
        }
    }


    /**
     * Возвращает все записи подходящие под условие отбора.
     *
     * @return ActiveRecord[]|null
     */
    protected function getRecords()
    {
        return $this->getFindQuery()
            ->where(
                [
                    '<',
                    $this->attribute,
                    $this->getBottomTimestamp()
                ]
            )
            ->all();
    }


    /**
     * Возвращает объект запроса поиска записей в базе данных.
     *
     * @return ActiveQuery
     */
    protected function getFindQuery()
    {
        return call_user_func(
            $this->recordClass . '::find'
        );
    }


    /**
     * Возвращает значение нижней границы отбора записей для очистки.
     *
     * @return int
     */
    protected function getBottomTimestamp()
    {
        if (empty($this->bottomTimestamp)) {
            $this->bottomTimestamp =
                (int)\Yii::$app->formatter->asTimestamp(
                    date(
                        "Y-m-d",
                        strtotime("- $this->interval days")
                    ) . " 00:00:00"
                );
        }
        return $this->bottomTimestamp;
    }
}
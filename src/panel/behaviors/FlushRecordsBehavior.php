<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\behaviors;

use modular\core\Controller;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;


/**
 * @todo    release proper safeDelete functionality
 *
 * Class FlushRecordsBehavior
 * Поведение контроллера для автоматического удаления устаревших записей согласно установленным параметрам очистки. При
 * событии [[\yii\web\Controller::EVENT_BEFORE_ACTION]] производит проверку на наличие подходящих записей и удаляет
 * найденные.
 *
 * @property Controller $owner
 *
 * @package panel\behaviors
 */
class FlushRecordsBehavior extends Behavior
{


    /**
     * @var string строковое название класса модели записи
     */
    public $recordClass = '';


    /**
     * @var int максимальный срок хранения записей ( дни )
     */
    public $interval;


    /**
     * @var string аттрибут модели для отбора записей на удаление
     */
    public $attribute = 'created_at';


    /**
     * @var string разрешённая RBAC роль пользователя для выполнения очистки
     */
    public $permission;


    /**
     * @var bool если требуется безопасное удаление записей
     */
    public $isSafeDelete = false;


    /**
     * @var int лимит количества записей обрабатываемых за одну итерацию
     */
    public $rowsLimit = 10000;


    /**
     * @var array
     */
    private $_messages = [];


    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->recordClass)) {
            throw new InvalidConfigException("Property `recordClass` must be defined.");
        }
        if (empty($this->interval)) {
            throw new InvalidConfigException("Property `interval` must be defined.");
        }
    }


    /**
     * {@inheritdoc}
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
     *
     * @throws InvalidConfigException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function beforeAction()
    {
        if (empty($this->permission) || \Yii::$app->user->can($this->permission)) {
            $this->flush();
            $this->resultMessages();
        }
    }


    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function flush()
    {
        $countDeleted = 0;
        if ($this->isSafeDelete) {
            foreach ($this->getRows() as $row) {
                $countDeleted = $row->delete() ? $countDeleted + 1 : $countDeleted;
            }
            if (!empty($this->getRows())) {
                $this->addResultMessage(
                    "warning",
                    "Для безопасного удаления всех устарвеших записей требуется повторно перезагружать страницу до исчезновения этого уведомления."
                );
            }
        }
        else {
            $countDeleted = $this->getFlushQuery();
        }
        if ($countDeleted) {
            $this->addResultMessage(
                "Произведена автоматическая очистка <strong>$countDeleted</strong> записей раздела созданных ранее чем <strong>" .
                \Yii::$app->formatter->asDate($this->getTimestamp()) .
                "</strong>."
            );
        }
    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function getRows()
    {
        return
            $this
                ->getFilterQuery()
                ->where($this->getMatchCondition())
                ->orderBy(
                    [
                        $this->attribute => SORT_DESC
                    ]
                )
                ->limit($this->rowsLimit)
                ->all();
    }


    /**
     * Возвращает список параметров отбора записей для удаления.
     *
     * @return array
     */
    protected function getMatchCondition()
    {
        return
            [
                '<',
                $this->attribute,
                $this->getTimestamp()
            ];
    }


    /**
     * Выполняет удалений отобранных записей и возвращает их количество.
     *
     * @return mixed
     */
    protected function getFlushQuery()
    {
        return
            call_user_func(
                $this->recordClass . '::deleteAll',
                $this->getMatchCondition()
            );
    }


    /**
     * Возвращает объект запроса поиска записей в таблице.
     *
     * @return ActiveQuery
     */
    protected function getFilterQuery()
    {
        return
            call_user_func(
                $this->recordClass . '::find'
            );
    }


    /**
     * Возвращает значение нижней границы временной метки отбора записей для удаления.
     *
     * @return int
     */
    protected function getTimestamp()
    {
        return strtotime("- $this->interval days");
    }


    /**
     *
     */
    protected function resultMessages()
    {
        if (!empty($this->_messages)) {
            foreach ($this->_messages as $key => $message) {
                \Yii::$app->session->setFlash(
                    $key,
                    $message
                );
            }
        }
    }


    /**
     * @param string $message
     * @param string $key
     */
    protected function addResultMessage($message = "", $key = "info")
    {
        if (isset($this->_messages[$key])) {
            $this->_messages[$key] .= "\r\n" . $message;
        }
        else {
            $this->_messages[$key] = $message;
        }
    }

}
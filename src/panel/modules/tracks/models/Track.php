<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace panel\modules\tracks\models;


use modular\common\models\ShortLink;
use modular\common\modules\Module;
use yii\base\DynamicModel;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;


/**
 * Class Track модель уведомления трекера уведомлений веб-ресурсов
 *
 * @property int    $id
 * @property string $session_id      идентификатор сессии входящего запроса
 * @property string $version         версия модуля веб-ресурса
 * @property string $module_id       идентификатор модуля веб-ресурса
 * @property string $related_item    связанный элемент уведомления
 * @property string $controller_id   идентификатор контроллера модуля веб-ресурса
 * @property string $action_id       идентификатор действия контроллера модуля веб-ресурса
 * @property string $request_post    POST данные запроса
 * @property string $request_get     GET данные запроса
 * @property string $priority        приоритет заметки
 * @property string $message         содержание заметки
 * @property string $user_ip         адрес IP входящего запроса
 * @property string $user_agent      веб-агент входящего запроса
 * @property string $viewed_by       данные просмотра в JSON
 * @property string $allowed_for     данные доступа в JSON
 * @property int    $updated_at
 * @property int    $created_at
 * @property array  $viewedBy        массив данных просмотра
 * @property array  $allowedFor      массив данных доступа
 * @property array  $observers       read-only массив данных контактов получателей уведомлений
 * @property array  $mailTo          read-only массив адресов электронной почты получателей уведомлений
 * @property array  $messageTo       read-only массив номеров телефонов получателей уведомлений
 * @property string $decodedPriority read-only строковое описание уровня приоритетности записи
 * @property string $debugData       read-only данные отладочной информации
 *
 * @package panel\modules\tracks\models
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class Track extends ActiveRecord
{


    /**
     * @const int PRIORITY_WARNING
     */
    const PRIORITY_WARNING = 2;


    /**
     * @const int PRIORITY_NOTICE
     */
    const PRIORITY_NOTICE = 1;


    /**
     * @var bool $shouldBeSaved если запись модели должна быть сохранена
     */
    public $shouldBeSaved = true;


    /**
     * @var array $trackerParams параметры отправки для модели уведомления
     */
    public $trackerParams = [];


    /**
     * @var array $_observers
     */
    private $_observers = [];


    /**
     * @var array $_mailTo
     */
    private $_mailTo = [];


    /**
     * @var array $_messageTo
     */
    private $_messageTo = [];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'resource__tracks';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }


    /**
     * @inheritdoc
     *
     * Используется в отладочной информации.
     */
    public function attributeLabels()
    {
        return [
            'user_agent'   => 'Веб-агент',
            'request_post' => 'POST',
            'request_get'  => 'GET',
        ];
    }


    /**
     * Возвращает read-only свойство массива адресов электронной почты получателей уведомлений.
     *
     * @return array
     */
    public function getMailTo()
    {
        if (empty($this->_mailTo)) {
            $this->_mailTo = (is_array($this->observers['mailTo'])) ? (array)$this->observers['mailTo'] : [];
        }
        return $this->_mailTo;
    }


    /**
     * Возвращает read-only свойство массива номеров телефонов получателей уведомлений.
     *
     * @return array
     */
    public function getMessageTo()
    {
        if (empty($this->_messageTo)) {
            $this->_messageTo = (is_array($this->observers['messageTo'])) ? $this->observers['messageTo'] : [];
        }
        return $this->_messageTo;
    }


    /**
     * Возвращает read-only свойство массива данных контактов получателей уведомлений.
     *
     * @return array
     */
    public function getObservers()
    {
        if (empty($this->_observers)) {
            $_mailTo = [];
            $_messageTo = [];
            foreach ($this->trackerParams['observers'] as $observer) {
                foreach ($observer as $contact) {
                    // email
                    $model = DynamicModel::validateData(['contact' => $contact,], [
                        ['contact', 'email'],
                    ]);
                    if (!$model->hasErrors()) {
                        $_mailTo[] = $contact;
                    }
                    // phone
                    $model = DynamicModel::validateData(['contact' => $contact,], [
                        ['contact', 'number'],
                    ]);
                    if (!$model->hasErrors()) {
                        $_messageTo[] = $contact;
                    }
                }
            }
            if (!empty($_mailTo)) {
                $_mailTo = array_unique($_mailTo);
            }
            if (!empty($_messageTo)) {
                $_messageTo = array_unique($_messageTo);
            }
            $this->_observers = [
                'mailTo'    => $_mailTo,
                'messageTo' => $_messageTo,
            ];
        }
        return $this->_observers;
    }


    /**
     * Если установлен указанный тип уведомлений.
     *
     * @param string $notifyParam тип уведомления получателей ( например : 'email', 'message' )
     *
     * @return bool
     */
    public function hasNotifyParam($notifyParam)
    {
        return (!empty($this->trackerParams['notify']) && ArrayHelper::isIndexed($this->trackerParams['notify'])) ?
            in_array($notifyParam, $this->trackerParams['notify']) : false;
    }


    /**
     * Возвращает read-only свойство с текстовым описанием уровня приоритетности записи.
     *
     * @return string
     */
    public function getDecodedPriority()
    {
        switch ($this->priority) {
            case self::PRIORITY_NOTICE :
                return 'уведомление';
                break;

            case self::PRIORITY_WARNING :
                return 'предупреждение';

            default :
                return '';
                break;
        }
    }


    /**
     * Помечает все уведомления модуля веб-ресурса как прочтённые указанным пользователем.
     *
     * @param string $moduleId идентификатор модуля
     * @param int    $userId   идентификатор пользователя
     */
    public static function allViewedBy($moduleId, $userId = 0)
    {
        /** @var static[] $tracks */
        $tracks = self::queryTracksBy($moduleId, $userId)->all();
        foreach ($tracks as $track) {
            $track->viewed($userId);
        }
    }


    /**
     * Возвращает массив данных просмотров c идентификаторами пользователей.
     *
     * @return array
     */
    public function getViewedBy()
    {
        return (empty($this->getAttribute('viewed_by'))) ? [] : Json::decode($this->getAttribute('viewed_by'));
    }


    /**
     * Добавляет в массив данных просмотр для указанного пользователя.
     *
     * @param int $userId идентификатор пользователя
     */
    public function setViewedBy($userId = 0)
    {
        if (!$this->hasBeenViewedBy($userId)) {
            $this->updateAttributes([
                'viewed_by' => Json::encode(ArrayHelper::merge($this->viewedBy, ["-" . $userId . "-",])),
            ]);
            $this->refresh();
        }
    }


    /**
     * Добавляет просмотр для указанного пользователя или группы пользователей.
     *
     * @param int|array $user идентификатор пользователя или массив идентификаторов
     */
    public function viewed($user = 0)
    {
        if (!empty($user)) {
            if (is_array($user)) {
                foreach ($user as $id) {
                    $this->viewedBy = $id;
                }
            } else {
                $this->viewedBy = $user;
            }
        }
    }


    /**
     * Если заметка была просмотрена указанным пользователем.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function hasBeenViewedBy($userId = 0)
    {
        return (!empty($userId)) ? in_array("-" . $userId . "-", $this->viewedBy) : false;
    }


    /**
     * Возвращает массив данных доступа c идентификаторами пользователей.
     *
     * @return array
     */
    public function getAllowedFor()
    {
        return (empty($this->getAttribute('allowed_for'))) ? [] : Json::decode($this->getAttribute('allowed_for'));
    }


    /**
     * Добавляет в массив данных доступ для указанного пользователя.
     *
     * @param int $userId
     */
    public function setAllowedFor($userId = 0)
    {
        if (!$this->isAllowedFor($userId)) {
            $this->updateAttributes([
                'allowed_for' => Json::encode(ArrayHelper::merge($this->allowedFor, ["-" . $userId . "-",])),
            ]);
        }
    }


    /**
     * Добавляет доступ для указанного пользователя или группы пользователей.
     *
     * @param int|array $user идентификатор пользователя или массив идентификаторов
     */
    public function allowed($user = 0)
    {
        if (!empty($user)) {
            if (is_array($user)) {
                foreach ($user as $id) {
                    $this->allowedFor = $id;
                }
            } else {
                $this->allowedFor = $user;
            }
        }
    }


    /**
     * Если у заметки есть доступ для указанного пользователя.
     *
     * @param int $userId
     *
     * @return bool
     */
    public function isAllowedFor($userId = 0)
    {
        return (!empty($userId)) ? in_array("-" . $userId . "-", $this->allowedFor) : false;
    }


    /**
     * Возвращает количество непросмотренных уведомлений модуля веб-ресурса для указанного пользователя.
     *
     * @param string $moduleId
     * @param int    $userId
     *
     * @return int
     */
    public static function countModuleActiveTracks($moduleId = '', $userId = 0)
    {
        return count(self::findActiveTracksBy($moduleId, $userId, 1)) + count(self::findActiveTracksBy($moduleId,
                $userId, 2));
    }


    /**
     * Возвращает массив активных уведомлений по указанным параметрам.
     *
     * @param string $moduleId
     * @param int    $priority
     * @param int    $userId
     *
     * @return Track[]
     */
    public static function findActiveTracksBy($moduleId = '', $userId = 0, $priority = 0)
    {
        $priorityCondition = ($priority > 0) ?
            "`priority` LIKE '" . $priority . "' AND " :
            "";
        $moduleCondition = "`module_id` REGEXP '$moduleId'";
        $viewedCondition = "( `viewed_by` IS NULL OR `viewed_by` NOT REGEXP '-" . $userId . "-' )";
        $allowedCondition = "( `allowed_for` IS NULL OR `allowed_for` REGEXP '-" . $userId . "-' )";
        return static::find()
            ->where($priorityCondition . $moduleCondition . " AND " . $viewedCondition . " AND " . $allowedCondition)
            ->orderBy(['created_at' => SORT_DESC,])->all();
    }


    /**
     * Возвращает запрос для всех уведомлений указанных модуля и пользователя.
     *
     * @param string $moduleId
     * @param int    $userId
     *
     * @return ActiveQuery
     */
    public static function queryTracksBy($moduleId = '', $userId = 0)
    {
        return static::find()
            ->where(
                "`module_id` REGEXP '$moduleId' AND "
                . "( `allowed_for` IS NULL OR `allowed_for` REGEXP '-$userId-' )"
            )
            ->orderBy(['created_at' => SORT_DESC,]);
    }


    /**
     * Сбрасывает данные просмотра и доступа.
     */
    public function resetAccessData()
    {
        $this->updateAttributes([
            'viewed_by'   => null,
            'allowed_for' => null,
        ]);
    }


    /**
     * Возвращает массив параметров почтового уведомления.
     *
     * @return array
     */
    public function messageParams()
    {
        return ArrayHelper::merge(
            $this->toArray([
                'message',
                'priority',
                'module_id',
                'version',
                'session_id',
            ]),
            [
                'link' => $this->getRelatedLink(false, true),
            ]
        );
    }


    /**
     * Возвращает массив данных связанной записи модуля веб-ресурса.
     *
     * @return array если данные неполные или отсутствуют, то вернёт пустой массив
     */
    protected function getRelatedItem()
    {
        if (!empty($this->related_item)) {
            $relatedItem = explode(":", $this->related_item);
            if (!empty($relatedItem[0]) && !empty($relatedItem[1])) {
                return $relatedItem;
            }
        }
        return [];
    }


    /**
     * Возвращает HTML ссылку на связанный элемент уведомления панели администрирования ресурса.
     * > Note: URL ссылки зависит от среды выполнения приложения.
     *
     * @param bool $asLink       если требуется прямая ссылка
     * @param bool $useShortLink если требуется использовать короткие ссылки
     *
     * @return string вернёт пустую строку если параметры ссылки отсутствуют
     */
    public function getRelatedLink($asLink = false, $useShortLink = false)
    {
        if (!empty($this->getRelatedItem())) {
            return !($asLink) ?
                "<i class='fa fa-angle-double-right'></i> " . Html::a(
                    "Данные инициатора уведомления",
                    $this->_buildLink($this->getRelatedItem(), $useShortLink),
                    [
                        'class' => 'revert red',
                    ]
                ) . "<br/><br/>" :
                $this->_buildLink($this->getRelatedItem(), $useShortLink);
        } else {
            return '';
        }

    }


    /**
     * Формирует ссылку на связанный элемент уведомления панели администрирования ресурса.
     *
     * @param array $relatedItem  данные связанного элемента
     * @param bool  $useShortLink если требуется использовать короткие ссылки
     *
     * @return bool|string
     */
    private function _buildLink($relatedItem = [], $useShortLink = true)
    {
        /** @var Module $module */
        $module = \Yii::$app->controller->module;
        if (count($relatedItem) == 2) {
            $panelLink = (defined("YII_DEBUG") && YII_DEBUG == true) ?
                "https://dev-services.v-tell.ru" :
                "https://services.v-tell.ru";
            $link = "$panelLink/" . $module->params['bundleParams']['module_id'] . "/$relatedItem[0]/view?id=$relatedItem[1]";
            if ($useShortLink) {
                $shortLink = (new ShortLink())->add($link);
                return !empty($shortLink) ?
                    $panelLink . "/ref/" . $shortLink :
                    $link;
            } else {
                return "$panelLink/" . $module->params['bundleParams']['module_id'] . "/$relatedItem[0]/view?id=$relatedItem[1]";
            }
        }
        return '';
    }

}
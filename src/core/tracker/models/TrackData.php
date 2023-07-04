<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\models;


use modular\core\helpers\ArrayHelper;
use modular\core\helpers\Html;
use modular\core\models\PackageInit;
use modular\core\models\ShortLink;
use modular\core\Module;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;


/**
 * Class Track модель уведомления трекера уведомлений веб-ресурсов
 *
 * @property int              $id
 * @property string           $module_id       идентификатор модуля активного контроллера
 * @property string           $controller_id   идентификатор активного контроллера
 * @property string           $action_id       идентификатор действия активного контроллера
 * @property string           $request         параметры запроса
 * @property string           $request_method  тип запроса
 * @property string           $priority        приоритет заметки
 * @property string           $message         содержание заметки
 * @property string           $user_ip         адрес IP входящего запроса
 * @property string           $user_agent      веб-агент входящего запроса
 * @property string           $viewed_by       данные просмотра в JSON
 * @property string           $allowed_for     данные доступа в JSON
 * @property string           $version         версия модуля веб-ресурса
 * @property int              $updated_at
 * @property int              $created_at
 * @property-read bool        $isViewed        если прочитано активным пользователем
 * @property array            $viewedBy        массив данных просмотра
 * @property array            $allowedFor      массив данных доступа
 * @property-read PackageInit $module          модуль создавший уведомление
 *
 * @package modular\core\tracker\models
 */
class TrackData extends ActiveRecord
{


    /**
     * Высокий приоритет уведомления.
     */
    const PRIORITY_WARNING = 2;


    /**
     * Обычный приоритет уведомления.
     */
    const PRIORITY_NOTICE = 1;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resource_rails__v1_tracks';
    }


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [TimestampBehavior::class,];
    }


    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return
            [
                self::SCENARIO_DEFAULT => [
                    'version',
                    'module_id',
                    'controller_id',
                    'action_id',
                    'request',
                    'request_method',
                    'priority',
                    'message',
                    'user_ip',
                    'user_agent',
                    'viewed_by',
                    'allowed_for'
                ],
            ];
    }


    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return
            [
                [
                    'controller_id',
                    'default',
                    'value' => function () {
                        return \Yii::$app->controller->id;
                    }
                ],
                [
                    'action_id',
                    'default',
                    'value' => function () {
                        return \Yii::$app->controller->action->id;
                    }
                ],
                [
                    'user_ip',
                    'default',
                    'value' => function () {
                        return \Yii::$app->request->userIP;
                    }
                ],
                [
                    'user_agent',
                    'default',
                    'value' => function () {
                        return \Yii::$app->request->userAgent;
                    }
                ],
                [
                    'request',
                    'default',
                    'value' => function () {
                        return
                            Json::encode(
                                \Yii::$app->request->isPost ?
                                    \Yii::$app->request->post() : \Yii::$app->request->get()
                            );
                    }
                ],
                [
                    'request_method',
                    'default',
                    'value' => function () {
                        return \Yii::$app->request->method;
                    }
                ]
            ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_agent'     => 'Веб-агент',
            'request'        => 'Параметры запроса',
            'request_method' => 'Метод запроса',
        ];
    }


    /**
     * @return array
     */
    public function fields()
    {
        return
            [
                'message',
                'priority',
                'module_id',
                'version',
            ];
    }


    /**
     * @return ActiveQuery
     */
    public function getModule()
    {
        return $this->hasOne(PackageInit::class, ['module_id' => 'module_id',]);
    }


    /**
     * Возвращает строковый заголовок уведомления.
     *
     * @return string
     */
    public function getMessageSubject()
    {
        return
            $this->isNewRecord ?
                \Yii::$app->name . " : " . $this->getPriorityLabel() :
                "[ Ticket: $this->id ] " . \Yii::$app->name . " : " . $this->getPriorityLabel();
    }


    /**
     * Возвращает строковое описание уведомления с его идентификатором.
     * @return string
     */
    public function getDescription()
    {
        return "[ <strong>$this->id</strong> ] " . $this->getPriorityLabel();
    }


    /**
     * Возвращает строковый идентификатор модуля и его версию.
     * @return string
     */
    public function getTitleAndVersion()
    {
        return "$this->module_id/$this->version";
    }

    /**
     * Возвращает название приоритета записи.
     *
     * @return string
     */
    public function getPriorityLabel()
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
     * Возвращает массив данных просмотров c идентификаторами пользователей.
     *
     * @return array
     */
    public function getViewedBy()
    {
        return
            empty($this->getAttribute('viewed_by'))
                ?
                [] :
                Json::decode(
                    $this->getAttribute('viewed_by')
                );
    }


    /**
     * Добавляет в массив данных просмотр для указанного пользователя.
     *
     * @param int $userId идентификатор пользователя
     */
    public function setViewedBy($userId = 0)
    {
        if (!$this->getIsViewed()) {
            $this->updateAttributes([
                'viewed_by' => Json::encode(ArrayHelper::merge($this->viewedBy, ["-" . $userId . "-",])),
            ]);
            $this->refresh();
        }
    }


    /**
     * Возвращает статус прочтения активным пользователем.
     *
     * @return bool
     */
    public function getIsViewed()
    {
        return
            in_array("-" . \Yii::$app->user->getId() . "-", $this->viewedBy);
    }


    /**
     * @param int $userId
     *
     * @return bool
     * @deprecated
     *
     * Если заметка была просмотрена указанным пользователем.
     *
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
                'allowed_for' => Json::encode(ArrayHelper::merge($this->getAllowedFor(), ["-" . $userId . "-",])),
            ]);
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
        return (!empty($userId)) ? in_array("-" . $userId . "-", $this->getAllowedFor()) : false;
    }


    /**
     * Помечает все уведомления модуля веб-ресурса как прочтённые указанным пользователем.
     *
     * @param string $moduleId   идентификатор модуля
     * @param int    $userId     идентификатор пользователя
     * @param array  $dateFilter парамтеры фильтрации уведомлений по дате
     */
    public static function allViewedBy($moduleId = "", $userId = 0, $dateFilter = [])
    {
        /** @var static[] $tracks */
        $tracks = self::listQuery($moduleId, $userId, $dateFilter)->all();
        foreach ($tracks as $track) {
            $track->viewed($userId);
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
     * Добавляет доступ для указанной группы пользователей.
     *
     * @param int|array|null $users
     */
    public function usersAllowed($users = [])
    {
        if (!empty($user)) {
            $user = (array)$user;
            foreach ($user as $id) {
                $this->allowedFor = $id;
            }
        }
    }


    /**
     * Возвращает фильтрованный список всех уведомлений ресурса.
     * Если параметры фильтрации не указаны, то вернёт список уведомлений доступных для просмотра всем пользователям.
     *
     * @param string     $cid        фильтр по идентификатору ресурса
     * @param int        $allowedFor фильтр по идентификатору пользователя
     * @param array|null $dateFilter фильтр по дате создания в указанном интервале
     *
     * @return ActiveQuery
     */
    public static function listQuery($cid = '', $allowedFor = 0, $dateFilter = [])
    {
        $dateFilter =
            isset($dateFilter) ?
                ArrayHelper::merge(
                    [
                        'from' => strtotime("00:00:00"),
                        'to'   => time()
                    ],
                    $dateFilter
                ) : $dateFilter;
        $listCondition = !empty($cid) ? "`module_id` REGEXP '$cid' AND " : "";
        $listQuery =
            isset($dateFilter) ?
                static::find()
                    ->where(
                        $allowedFor > 0 ?
                            $listCondition . "( `allowed_for` IS NULL OR `allowed_for` REGEXP '-$allowedFor-' )" :
                            $listCondition . "`allowed_for` IS NULL"
                    )
                    ->andWhere(
                        ['>=', 'created_at', $dateFilter['from']]
                    )
                    ->andWhere(['<=', 'created_at', $dateFilter['to']]) :
                static::find()
                    ->where(
                        $allowedFor > 0 ?
                            $listCondition . "( `allowed_for` IS NULL OR `allowed_for` REGEXP '-$allowedFor-' )" :
                            $listCondition . "`allowed_for` IS NULL"
                    );
        return
            $listQuery
                ->orderBy(
                    [
                        'created_at' => SORT_DESC,
                    ]
                );
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
            $link = "$panelLink/" . $module->id . "/$relatedItem[0]/view?id=$relatedItem[1]";
            if ($useShortLink) {
                $shortLink = (new ShortLink())->add($link);
                return !empty($shortLink) ?
                    $panelLink . "/ref/" . $shortLink :
                    $link;
            } else {
                return "$panelLink/" . $module->id . "/$relatedItem[0]/view?id=$relatedItem[1]";
            }
        }
        return '';
    }

}
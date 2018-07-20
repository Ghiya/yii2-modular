<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\models;


use modular\core\helpers\ArrayHelper;
use modular\core\helpers\Html;
use modular\core\models\ShortLink;
use modular\core\Module;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;


/**
 * Class Track модель уведомления трекера уведомлений веб-ресурсов
 *
 * @property int    $id
 * @property string $session_id      идентификатор сессии входящего запроса
 * @property string $resource_id     идентификатор модуля веб-ресурса
 * @property string $module_id       идентификатор модуля активного контроллера
 * @property string $controller_id   идентификатор активного контроллера
 * @property string $action_id       идентификатор действия активного контроллера
 * @property string $request         параметры запроса
 * @property string $request_method  тип запроса
 * @property string $priority        приоритет заметки
 * @property string $message         содержание заметки
 * @property string $user_ip         адрес IP входящего запроса
 * @property string $user_agent      веб-агент входящего запроса
 * @property string $viewed_by       данные просмотра в JSON
 * @property string $allowed_for     данные доступа в JSON
 * @property string $related_item    связанный элемент уведомления
 * @property string $version         версия модуля веб-ресурса
 * @property int    $updated_at
 * @property int    $created_at
 * @property array  $viewedBy        массив данных просмотра
 * @property array  $allowedFor      массив данных доступа
 * @property array  $observers       read-only массив данных контактов получателей уведомлений
 * @property array  $mailTo          read-only массив адресов электронной почты получателей уведомлений
 * @property array  $messageTo       read-only массив номеров телефонов получателей уведомлений
 * @property string $debugData       read-only данные отладочной информации
 *
 * @package modular\core\tracker\models
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class TrackData extends ActiveRecord
{


    const SCENARIO_SEARCH = 'tracker.searchTracksScenario';


    /**
     * Высокий приоритет уведомления.
     */
    const PRIORITY_WARNING = 2;


    /**
     * Обычный приоритет уведомления.
     */
    const PRIORITY_NOTICE = 1;


    /**
     * @var string
     */
    public $from;


    /**
     * @var string
     */
    public $to;


    /**
     * @var int
     */
    public $range = 8;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resource__tracks';
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
                    'session_id',
                    'resource_id',
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
                self::SCENARIO_SEARCH  => ['from', 'to'],
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
                    'session_id',
                    'default',
                    'value' => function () {
                        return \Yii::$app->session->id;
                    }
                ],
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
            !empty($this->from) && !empty($this->to) ?
                [
                    'from' => function ($model) {
                        return $model->from;
                    },
                    'to'   => function ($model) {
                        return $model->to;
                    }
                ] :
                [];
    }


    /**
     * @return ActiveQuery|TrackData
     */
    /*public function search()
    {
        $params = $this->toArray();
        return
            empty($params['from']) ?
                static::find()->orderBy(['created_at' => SORT_DESC,]) :
                $this->_getRangeListQuery($params);
    }*/


    /**
     * @param string $id
     * @param bool   $countTracks
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getRanges($id = "", $countTracks = false)
    {
        $formatter = \Yii::$app->formatter;
        $ranges = array_pad([], $this->range, []);
        foreach (array_keys($ranges) as $index) {
            $ranges[$index] =
                $index == 0 ?
                    [
                        'from' =>
                            $formatter->asTimestamp(
                                date(
                                    "Y-m-d 00:00:00.000000"
                                )
                            ),
                        'to'   => time(),
                        'date' => 'сегодня',//$formatter->asDatetime(time(), "php:d.m")
                    ] :
                    [
                        'from' =>
                            $formatter->asTimestamp(
                                date(
                                    "Y-m-d 00:00:00.000000",
                                    strtotime("- " . $index . " days")
                                )
                            ),
                        'to'   =>
                            $formatter->asTimestamp(
                                date(
                                    "Y-m-d 00:00:00.000000",
                                    strtotime("- " . ($index - 1) . " days")
                                )
                            ),
                        'date' =>
                            $formatter->asDatetime(
                                strtotime("- $index days"),
                                "php:d.m"
                            )
                    ];
        }
        if ($countTracks) {
            foreach ($ranges as $index => $range) {
                $ranges[$index] =
                    ArrayHelper::merge(
                        $ranges[$index],
                        [
                            'count'  => count($this->_getRangeListQuery($range, $id)->all()),
                            'active' =>
                                (
                                    $formatter->asDatetime(
                                        $range['to'],
                                        "php:d.m"
                                    ) == $formatter->asDatetime(
                                        $this->getCurrent(),
                                        "php:d.m"
                                    )
                                ) && !empty($this->to)
                        ]
                    );
            }
        }
        return $ranges;
    }


    /**
     * @return string
     */
    protected function getCurrent()
    {
        return
            empty($this->to) ?
                \Yii::$app->formatter->asTimestamp(date("Y-m-d H:i:s")) : $this->to;
    }


    /**
     * @param string $id
     * @param array  $timestamps
     *
     * @return ActiveQuery
     */
    private function _getRangeListQuery($timestamps = [], $id = "")
    {
        return
            static::find()
                ->where(
                    "`module_id` REGEXP '$id'"
                )
                ->andWhere(
                    ['>=', 'created_at', $timestamps['from']]
                )
                ->andWhere(['<=', 'created_at', $timestamps['to']]);
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
     * Помечает все уведомления модуля веб-ресурса как прочтённые указанным пользователем.
     *
     * @param string $moduleId идентификатор модуля
     * @param int    $userId   идентификатор пользователя
     */
    public static function allViewedBy($moduleId = "", $userId = 0)
    {
        /** @var static[] $tracks */
        $tracks = self::listQuery($moduleId, $userId)->all();
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
            }
            else {
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
            }
            else {
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
    public static function countActive($moduleId = '', $userId = 0)
    {
        return
            count(self::fetchList($moduleId, $userId, self::PRIORITY_NOTICE)) +
            count(self::fetchList($moduleId, $userId, self::PRIORITY_WARNING));
    }


    /**
     * Возвращает список уведомлений относительно указанных параметров.
     *
     * @param string $moduleId
     * @param int    $priority
     * @param int    $userId
     * @param bool   $activeOnly
     *
     * @return TrackData[]
     */
    public static function fetchList($moduleId = '', $userId = 0, $priority = 0, $activeOnly = true)
    {
        return
            static::find()
                ->where(
                    !empty($moduleId) ?
                        ['regexp', 'module_id', $moduleId] : null
                )
                ->andWhere(
                    !empty($priority) ?
                        ['like', 'priority', $priority] : null
                )
                ->andWhere(
                    $activeOnly ?
                        "`viewed_by` IS NULL OR `viewed_by` NOT REGEXP '-" . $userId . "-'" : null
                )
                ->andWhere(
                    $userId > 0 ?
                        "`allowed_for` IS NULL OR `allowed_for` REGEXP '-" . $userId . "-'" : null
                )
                ->orderBy(
                    [
                        'created_at' => SORT_DESC,
                    ]
                )
                ->all();
    }


    /**
     * @deprecated
     *
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
     * Возвращает отфильтрованный по дате/пользователю/модулю список всех уведомлений ресурса.
     *
     * @param string $id         если требуется фильтрация относительно модуля
     * @param int    $userId     если требуется фильтрация относительно пользователя
     * @param array  $dateFilter по-умолчанию - текущая дата
     *
     * @return ActiveQuery
     */
    public static function listQuery($id = '', $userId = 0, $dateFilter = [])
    {
        $dateFilter =
            isset($dateFilter) ?
                ArrayHelper::merge(
                    [
                        'from' => \Yii::$app->formatter->asTimestamp(date("Y-m-d") . " 00:00:00"),
                        'to'   => time()
                    ],
                    $dateFilter
                ) : $dateFilter;
        $listCondition = !empty($id) ? "`module_id` REGEXP '$id' AND " : "";
        $listQuery =
            isset($dateFilter) ?
                static::find()
                    ->where(
                        $userId > 0 ?
                            $listCondition . "( `allowed_for` IS NULL OR `allowed_for` REGEXP '-$userId-' )" :
                            $listCondition . "`allowed_for` IS NULL"
                    )
                    ->where(
                        ['>=', 'created_at', $dateFilter['from']]
                    )
                    ->andWhere(['<=', 'created_at', $dateFilter['to']]) :
                static::find()
                    ->where(
                        $userId > 0 ?
                            $listCondition . "( `allowed_for` IS NULL OR `allowed_for` REGEXP '-$userId-' )" :
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
     * Возвращает массив параметров почтового уведомления.
     *
     * @return array
     */
    public function getNoticeParams()
    {
        return
            $this->toArray([
                'message',
                'priority',
                'module_id',
                'version',
                'session_id',
            ]);
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
        }
        else {
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
            }
            else {
                return "$panelLink/" . $module->id . "/$relatedItem[0]/view?id=$relatedItem[1]";
            }
        }
        return '';
    }

}
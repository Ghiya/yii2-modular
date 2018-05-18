<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace resource\models;


use common\models\ModuleInit;
use panel\models\UserRole;
use resource\modules\_default\Module;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\UnsetArrayValue;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

/**
 * Class ActionsIndex
 *
 * @property int        $id
 * @property string     $resource_id
 * @property int        $index
 * @property string     $panel_link
 * @property string     $description
 * @property string     $user_ip
 * @property string     $user_agent
 * @property int        $created_at
 * @property array      $panelLink    read-only
 * @property ModuleInit $resource     read-only
 * @property string     $subscriberId read-only
 *
 * @package resource\models
 */
class ActionsIndex extends ActiveRecord implements Linkable
{


    /**
     * @const string SUBSCRIBER_GUEST
     */
    const SUBSCRIBER_GUEST = "Гость";


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'resource__index_actions';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ]
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'resource_id', 'index', 'description', 'user_ip', 'user_agent',],
            'safe'
        ];
    }


    /**
     * Возвращает связанную модель параметров ресурса.
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(ModuleInit::className(), ['id' => 'resource_id']);
    }


    /**
     * Добавляет запись действия для указанного модуля.
     *
     * @param Module $module
     */
    public static function add(Module $module)
    {
        $action = new static();
        $action->index = $module->index;
        $action->resource_id = $module->params['bundleParams']['id'];
        $action->panel_link = $module->getIndexPanelLink();
        $action->description = $module->indexDescription(
            \Yii::$app->controller->id,
            \Yii::$app->controller->action->id
        );
        $action->user_ip = \Yii::$app->request->userIP;
        $action->user_agent = \Yii::$app->request->userAgent;
        $action->save(false);
    }


    /**
     * Возвращает несколько записей действий отсортированных по дате создания в порядке убывания.
     *
     * @param int  $limit       количество записей
     * @param int  $createdFrom ограничение выводимых записей по времени создания
     * @param bool $asQuery     если требуется вернуть объект запроса в базу данных
     *
     * @return array|ActiveRecord[]|ActiveQuery
     */
    public static function findLast($limit = 15, $createdFrom = 0, $asQuery = false)
    {
        $query = static::find()->orderBy(['created_at' => SORT_DESC]);
        if (!empty($limit)) {
            $query->limit($limit);
        }
        if (!empty($createdFrom)) {
            $query->where(
                ['>', 'created_at', $createdFrom]
            );
        }
        return $asQuery ? $query : $query->all();
    }


    /**
     * @inheritdoc
     */
    public function fields()
    {
        return ArrayHelper::merge(
            parent::fields(),
            [
                'index'       => function ($model) {
                    /** @var ActionsIndex $model */
                    return $model->getSubscriberId();
                },
                'resource_id' => new UnsetArrayValue(),
                'panel_link'  => new UnsetArrayValue(),
                'resource'    => function ($model) {
                    /** @var ActionsIndex $model */
                    return $model->resource->title;
                },
                'created_at'  => function ($model) {
                    /** @var ActionsIndex $model */
                    return \Yii::$app->formatter->asDatetime($model->created_at, "php:H:i:s");
                },
                'user_ip'     => (\Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA)) ?
                    'user_ip' : new UnsetArrayValue(),
                'user_agent'  => (\Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA)) ?
                    'user_agent' : new UnsetArrayValue(),
            ]
        );
    }


    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = [];
        if ($this->hasPanelLink()) {
            $links[] = [
                Link::REL_SELF => Url::to(
                    [
                        '/' . $this->resource->module_id .
                        '/' . $this->getPanelController() .
                        '/view',
                        'id' => $this->getPanelItemId()
                    ],
                    true
                ),
            ];
        }
        if (!empty($this->index)) {
            $links[] = [
                'subscriber' => Url::to(
                    [
                        '/' . \common\Application::SERVICES_ID . '.billing/subscribers/view',
                        'id' => $this->index
                    ], true
                ),
            ];
        }
        return $links;
    }


    /**
     * Возвращает read-only данные действия для просмотра в панели администрирования.
     * @return array
     */
    public function getPanelLink()
    {
        return !empty($this->panel_link) ?
            Json::decode($this->panel_link) : [];
    }


    /**
     * Если в записи указаны данные просмотра действия в панели управления.
     * @return bool
     */
    protected function hasPanelLink()
    {
        $panelLink = $this->getPanelLink();
        return count($panelLink) == 2 && !empty($panelLink[0]) && !empty($panelLink[1]);
    }


    /**
     * Возвращает идентификатор контроллера панели управления для просмотра действия.
     * @return string
     */
    protected function getPanelController()
    {
        return $this->hasPanelLink() ?
            (string)$this->panelLink[0] : "";
    }


    /**
     * Возвращает идентификатор записи для просмотра действия.
     * @return string
     */
    protected function getPanelItemId()
    {
        return $this->hasPanelLink() ?
            (string)$this->panelLink[1] : "";
    }


    /**
     * @return array
     */
    public function viewFields()
    {
        return ArrayHelper::merge(
            [
                [
                    'label'  => "Дата",
                    'format' => 'html',
                    'value'  => \Yii::$app->formatter->asDatetime($this->created_at, "php:d.m.Y / H:i:s"),
                ],
                [
                    'label'  => "Абонент",
                    'format' => 'raw',
                    'value'  => !empty($this->index) ?
                        Html::a(
                            $this->index,
                            ["/" . \common\Application::SERVICES_ID . ".billing/subscribers/view?id=" . $this->index],
                            [
                                'class' => 'font-book revert red',
                                'data'  => ['spinner' => 'true',],
                            ]
                        ) :
                        Html::tag(
                            'span',
                            self::SUBSCRIBER_GUEST,
                            ['class' => 'font-book',]
                        ),
                ],
                [
                    'label'  => "Действие",
                    'format' => 'raw',
                    'value'  => function ($model) {
                        /** @var ActionsIndex $model */
                        $rendered = "";
                        if (!empty($this->description)) {
                            $rendered = $this->hasPanelLink() ?
                                Html::a(
                                    $model->description,
                                    [
                                        "/" . $model->resource->module_id .
                                        "/" . $this->getPanelController() .
                                        "/view?id=" . $this->getPanelItemId()
                                    ],
                                    [
                                        'class' => 'font-book revert green',
                                        'data'  => ['spinner' => 'true',],
                                    ]
                                ) : Html::tag(
                                    'span',
                                    $model->description,
                                    ['class' => 'font-book']
                                );
                        }
                        return $rendered;
                    },
                ],
            ],
            \Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA) ?
                [
                    [
                        'label'  => "IP",
                        'format' => 'html',
                        'value'  => $this->user_ip,
                    ],
                    [
                        'label'  => "Веб-агент",
                        'format' => 'html',
                        'value'  => $this->user_agent,
                    ],
                ] :
                []
        );
    }


    /**
     * Возвращает идентификатор ( номер телефона ) абонента.
     *
     * @return string
     */
    public function getSubscriberId()
    {
        return (!empty($this->index)) ?
            $this->index :
            self::SUBSCRIBER_GUEST;
    }

}
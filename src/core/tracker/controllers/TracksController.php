<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\controllers;


use modular\core\Controller;
use modular\core\helpers\ArrayHelper;
use modular\core\helpers\Html;
use modular\core\helpers\ResourceHelper;
use modular\core\tracker\models\SearchTrackData;
use modular\core\tracker\models\TrackData;
use modular\panel\models\UserRole;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TracksController extends Controller
{


    /**
     * @var string
     */
    public $breadcrumb = 'Уведомления';


    /**
     * @var string
     */
    public $viewPath = '@modular/core/tracker/views/tracks';


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index', 'view', 'viewed', 'state', 'list'],
                        'roles'   => [UserRole::PM_ACCESS_TRACKS, UserRole::PM_VIEW_RESOURCE_DATA],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['viewed-all'],
                        'roles'   => [UserRole::PM_ACCESS_TRACKS, UserRole::PM_MANAGE_RESOURCE_DATA],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['delete'],
                        'roles'   => [UserRole::PM_REMOVE_RESOURCE_DATA],
                    ],
                ],
            ],
            /*[
                'class'       => FlushRecordsBehavior::class,
                'recordClass' => TrackData::class,
                'interval'    => 7,
                'permission'  => UserRole::PM_REMOVE_RESOURCE_DATA,
            ]*/
        ];
    }


    /**
     * Возвращает HTML данные отладки просматриваемого уведомления.
     *
     * @param TrackData $model
     *
     * @return string HTML список с административными данными
     */
    private function _debugData($model)
    {
        $debugData = "";
        if (\Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA) && !empty($model)) {
            $request = Json::decode($model->request);
            $debugData .= '<ul class="list-group">';
            $debugData .=
                Html::tag(
                    "li",
                    Html::tag(
                        "p",
                        Html::tag(
                            "strong",
                            strtoupper($model->request_method),
                            [
                                'class' => 'green'
                            ]
                        )
                        . " $model->module_id/$model->controller_id/$model->action_id "
                        . Html::tag(
                            "span",
                            "/ "
                            . Html::tag(
                                "strong",
                                $model->version
                            ) .
                            " : $model->user_ip",
                            [
                                'class' => 'text-backwards'
                            ]
                        ),
                        [
                            'class' => 'font-book'
                        ]
                    ),
                    [
                        'class' => 'list-group-item'
                    ]
                );
            foreach ($request as $field => $value) {
                $fieldValue = !empty($value) ? $value : 'null';
                $debugData .= '<li class="list-group-item">' .
                    '<p class="list-group-item-text font-book">'
                    . "<span class='text-backwards'>`$field` : </span>"
                    . $fieldValue . '</p></li>';
            }
            $debugData .= '</ul>';
        }
        return $debugData;
    }


    /**
     * @param $id
     *
     * @return int
     */
    public function actionState($id)
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;
        return count(TrackData::fetchList($id, \Yii::$app->user->id, TrackData::PRIORITY_NOTICE));
    }


    /**
     * @param $id
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList($id)
    {
        $searchTracks = new SearchTrackData();
        $searchTracks->load(\Yii::$app->request->get());
        return
            $this->render(
                $this->viewPath . '/list',
                [
                    'dataProvider'   =>
                        new ActiveDataProvider(
                            [
                                'query'      =>
                                    TrackData::listQuery(
                                        $id,
                                        \Yii::$app->user->identity->id,
                                        $searchTracks->toArray()
                                    ),
                                'pagination' => (\Yii::$app->request->get('page', 1) == 0) ? false : [
                                    'pageSize' => 50,
                                ]
                            ]
                        ),
                    'resourceId'     => $id,
                    'active'         => TrackData::countActive($id, \Yii::$app->user->identity->id),
                    'searchRanges'   => array_reverse($searchTracks->getRanges($id, true)),
                    'filterUrlRoute' =>
                        ArrayHelper::merge(
                            [
                                "viewed",
                            ],
                            ArrayHelper::merge(
                                \Yii::$app->request->get(),
                                $searchTracks->toArray()
                            )
                        )
                ]
            );
    }


    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        $model = SearchTrackData::findOne(['id' => \Yii::$app->request->get('id', null),]);
        if (empty($model)) {
            throw new NotFoundHttpException("Указанная запись не существует или была удалена");
        }
        else {
            $model->viewed(\Yii::$app->user->identity->getId());
        }
        return
            $this->renderPartial(
                $this->viewPath . '/view',
                [
                    'model'     => $model,
                    'debugData' =>
                        ResourceHelper::debugPrint(
                            [
                                'value' => $this->_debugData($model),
                                'type'  => ResourceHelper::DEBUG_PRINT_PLAIN,
                            ],
                            false
                        ),
                ]
            );
    }


    /**
     * Устанавливает все уведомления модуля веб-ресурса просмотренными для указанного пользователя.
     *
     * @param string $id
     *
     * @return Response
     */
    public function actionViewed($id)
    {
        $tracks = new SearchTrackData();
        $tracks->load(\Yii::$app->request->get());
        SearchTrackData::allViewedBy(
            $id,
            \Yii::$app->user->identity->getId(),
            $tracks->toArray()
        );
        \Yii::$app->getSession()->setFlash(
            'success',
            'Действие выполнено.'
        );
        return
            $this->redirect(
                ArrayHelper::merge(
                    ["list"],
                    \Yii::$app->request->get()
                )
            );
    }


    /**
     * @return Response
     */
    public function actionViewedAll()
    {
        TrackData::allViewedBy(
            null,
            \Yii::$app->user->identity->getId()
        );
        \Yii::$app->session->setFlash(
            'success',
            'Действие выполнено.'
        );
        return
            $this->redirect(
                "/" . $this->module->id
            );
    }


    /**
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $model = TrackData::findOne(['id' => \Yii::$app->request->post('id', null)]);
        if (empty($model)) {
            \Yii::$app->getSession()->setFlash(
                'warning',
                'Указанная запись не существует или была удалена.'

            );
        }
        else {
            \Yii::$app->getSession()->setFlash(
                'info',
                $model->delete() ?
                    'Запись [ <strong>' . $model->id . '</strong> ] удалена.' :
                    'Ошибка удаления записи.'

            );
        }
        return $this->redirect(\Yii::$app->request->referrer);
    }

}
<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\controllers;


use modular\core\Controller;
use modular\core\helpers\ArrayHelper;
use modular\core\tracker\models\SearchTrackData;
use modular\core\tracker\models\TrackData;
use modular\panel\behaviors\FlushRecordsBehavior;
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
                        'actions' => ['index', 'delete', 'viewed-all'],
                        'roles'   => [UserRole::PM_REMOVE_RESOURCE_DATA],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['view', 'viewed', 'list', 'filter'],
                        'roles'   => [UserRole::PM_ACCESS_TRACKS, UserRole::PM_VIEW_RESOURCE_DATA],
                    ],
                ],
            ],
            [
                'class'       => FlushRecordsBehavior::class,
                'recordClass' => TrackData::class,
                'interval'    => 60,
                'permission'  => UserRole::PM_MANAGE_ALL,
            ]
        ];
    }


    /**
     * @return string
     * @throws \Throwable
     */
    public function actionIndex()
    {
        $searchTracks = new SearchTrackData();
        return
            $this->render(
                $this->viewPath . '/index',
                [
                    'dataProvider' =>
                        new ActiveDataProvider(
                            [
                                'query'      => $searchTracks->getListQuery(),
                                'pagination' =>
                                    \Yii::$app->request->get('page', 1) == 0 ?
                                        false :
                                        [
                                            'pageSize' => 50,
                                        ]
                            ]
                        ),
                    'active'       => $searchTracks->countActive(false, false)
                ]
            );
    }


    /**
     * @param $id
     *
     * @return string
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList($cid)
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
                                'query'      => $searchTracks->getListQuery(),
                                'pagination' =>
                                    \Yii::$app->request->get('page', 1) == 0 ?
                                        false :
                                        [
                                            'pageSize' => 50,
                                        ]
                            ]
                        ),
                    'resourceId'     => $cid,
                    'active'         => $searchTracks->countActive(),
                    'searchRanges'   => array_reverse($searchTracks->getRanges($cid, true)),
                    'filterForm'     =>
                        $this->renderPartial(
                            $this->viewPath . '/filter',
                            [
                                'isActive' => false,
                                'model'    => $searchTracks,
                            ]
                        ),
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
     * @param $cid
     *
     * @return string
     * @throws \Throwable
     */
    public function actionFilter($cid)
    {
        $searchTracks = new SearchTrackData(['fullRange' => true]);
        $searchTracks->load(\Yii::$app->request->get());
        return
            $this->render(
                $this->viewPath . '/list',
                [
                    'dataProvider' =>
                        new ActiveDataProvider(
                            [
                                'query'      => $searchTracks->getFilterQuery(),
                                'pagination' =>
                                    \Yii::$app->request->get('page', 1) == 0 ?
                                        false :
                                        [
                                            'pageSize' => 50,
                                        ]
                            ]
                        ),
                    'resourceId'   => $cid,
                    'filterForm'   =>
                        $this->renderPartial(
                            $this->viewPath . '/filter',
                            [
                                'isActive' => true,
                                'model'    => $searchTracks,
                            ]
                        )
                ]
            );
    }


    /**
     * @return string
     * @throws NotFoundHttpException
     * @throws \Throwable
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
                        \Yii::$app->user->can(UserRole::PM_MANAGE_ALL) ?
                            $this->renderPartial(
                                $this->viewPath . '/view-request',
                                [
                                    'model'   => $model,
                                    'request' => Json::decode($model->request)
                                ]
                            ) : "",
                ]
            );
    }


    /**
     * Устанавливает все уведомления модуля веб-ресурса просмотренными для указанного пользователя.
     *
     * @param string $cid
     *
     * @return Response
     */
    public function actionViewed($cid)
    {
        $tracks = new SearchTrackData();
        $tracks->load(\Yii::$app->request->get());
        SearchTrackData::allViewedBy(
            $cid,
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
        SearchTrackData::allViewedBy(
            null,
            \Yii::$app->user->identity->getId(),
            null
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
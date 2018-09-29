<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\logs\controllers;


use modular\core\controllers\Controller;
use modular\panel\behaviors\FlushRecordsBehavior;
use modular\panel\models\UserRole;
use modular\core\models\ServiceLog;
use modular\panel\modules\logs\models\Search;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController контроллер записей запросов провайдеров данных внешщних сервисов.
 *
 * @package modular\panel\modules\logs\controllers
 *
 */
class DefaultController extends Controller
{


    public $viewPath = '@modular/panel/modules/logs/views/default';


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['index', 'view', 'search'],
                        'roles'   => [UserRole::PM_ACCESS_LOGS],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['delete', 'delete-selected', 'flush',],
                        'roles'   => [UserRole::PM_REMOVE_RESOURCE_DATA],
                    ],
                ],
            ],
            [
                'class'       => FlushRecordsBehavior::className(),
                'recordClass' => ServiceLog::className(),
                'interval'    => 7,
                'permission'  => UserRole::PM_REMOVE_RESOURCE_DATA,
            ]
        ];
    }


    /**
     * @param array $defaultFields
     *
     * @return Search
     */
    protected function getSearchModel($defaultFields = [])
    {
        return new Search($defaultFields);
    }


    /**
     * Просмотр списка записей.
     *
     * @param string $providerId
     *
     * @return string
     */
    public function actionIndex($providerId)
    {
        return $this->render($this->viewPath . '/index', [
            'dataProvider' => new ActiveDataProvider(
                [
                    'query'      => $this->getSearchModel()
                        ->search(
                            [
                                'provider_id' => $providerId,
                                'day'         => date("d"),
                                'month'       => date("m"),
                                'year'        => date("Y"),
                            ]
                        ),
                    'pagination' => (\Yii::$app->request->get('page', 1) == 0) ? false : [
                        'pageSize' => 50,
                    ],
                    'sort'       => [
                        'attributes' => ['created_at'],
                    ],
                ]
            ),
            'searchForm'   =>
                $this->renderPartial(
                    $this->viewPath . '/_search-form',
                    [
                        'providerId' => $providerId,
                        'model'      => $this->getSearchModel(
                            [
                                'day'   => date("d"),
                                'month' => date("m"),
                                'year'  => date("Y"),
                            ]
                        ),
                    ]
                ),
        ]);
    }


    /**
     * Просмотр отдельной записи.
     *
     * @return string
     * @throws NotFoundHttpException если указанная запись не существует или была удалена
     */
    public function actionView()
    {
        $model = ServiceLog::findById(
            \Yii::$app->request->get('id', null)
        );
        if (empty($model)) {
            throw new NotFoundHttpException("Указанная запись не существует или была удалена");
        }
        return $this->renderPartial($this->viewPath . '/view', [
            'model' => $model,
        ]);
    }


    /**
     * Поиск записей по параметрам.
     *
     * @param string $providerId
     *
     * @return string
     */
    public function actionSearch($providerId)
    {
        $searchModel = $this->getSearchModel();
        return $this->render($this->viewPath . '/index', [
            'dataProvider' => new ActiveDataProvider([
                'query'      => $searchModel->search(\Yii::$app->request->get()),
                'pagination' => (\Yii::$app->request->get('page', 1) == 0) ? false : [
                    'pageSize' => 50,
                ],
                'sort'       => [
                    'attributes' => ['created_at'],
                ],
            ]),
            'searchForm'   =>
                $this->renderPartial(
                    $this->viewPath . '/_search-form',
                    [
                        'model' => $searchModel,
                        'providerId' => $providerId,
                    ]
                ),
        ]);
    }


    /**
     * Удаление записи.
     *
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $model = ServiceLog::findById(
            \Yii::$app->request->post('id', null)
        );
        if (empty($model) || !$model->delete()) {
            \Yii::$app->getSession()->setFlash(
                'warning',
                "Указанная запись не существует или не может быть удалена"
            );
        } else {
            \Yii::$app->getSession()->setFlash(
                'danger',
                "Запись [ <strong>$model->id</strong> ] удалена."

            );
        }
        return $this->redirect(\Yii::$app->request->referrer);
    }


    /**
     * Удаление выбранных записей.
     */
    public function actionDeleteSelected()
    {
        $removedRowsCount = ServiceLog::deleteSelected(\Yii::$app->request->post('selected', []));
        if (!$removedRowsCount) {
            \Yii::$app->getSession()->setFlash(
                'warning',
                "Указанные записи не существуют или были удалены."
            );
        } else {
            \Yii::$app->session->setFlash(
                'warning',
                "Удалено <strong>$removedRowsCount</strong> записей."
            );
        }
        return $this->redirect(\Yii::$app->request->referrer);
    }

}
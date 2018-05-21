<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\tracks\controllers;


use modular\common\controllers\Controller;
use modular\common\helpers\ResourceHelper;
use modular\panel\behaviors\FlushRecordsBehavior;
use modular\panel\models\UserRole;
use modular\panel\modules\tracks\models\Track;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * Class DefaultController контроллер уведомлений веб-ресурса системы
 *
 * @package panel\modules\tracks\controllers
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class DefaultController extends Controller
{


    public $viewPath = '@modular/panel/modules/tracks/views/default';


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
                        'actions' => ['index', 'view', 'viewed', 'state',],
                        'roles'   => [UserRole::PM_ACCESS_TRACKS, UserRole::PM_VIEW_RESOURCE_DATA],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['add',],
                        'roles'   => [UserRole::PM_ACCESS_TRACKS, UserRole::PM_MANAGE_RESOURCE_DATA],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['delete'],
                        'roles'   => [UserRole::PM_REMOVE_RESOURCE_DATA],
                    ],
                ],
            ],
            [
                'class'       => FlushRecordsBehavior::className(),
                'recordClass' => Track::className(),
                'interval'    => 7,
                'permission'  => UserRole::PM_REMOVE_RESOURCE_DATA,
            ]
        ];
    }


    /**
     * Возвращает HTML данные отладки просматриваемого уведомления.
     *
     * @param Track $model
     *
     * @return string HTML список с административными данными
     */
    private function _debugData($model)
    {
        $debugData = "";
        if (\Yii::$app->user->can(UserRole::PM_VIEW_DEBUG_DATA) && !empty($model)) {
            // данные отладки
            $debugData .= '<ul class="list-group">';
            foreach ($model->toArray(['request_post', 'request_get', 'user_agent',]) as $field => $value) {
                if (!empty($value) && $value !== "не определён") {
                    $debugData .= '<li class="list-group-item">' .
                        '<p class="list-group-item-heading font-light text-backwards">' . $model->getAttributeLabel($field) . '</p>' .
                        '<p class="list-group-item-text font-book">' . $value . '</p></li>';
                }
            }
            $debugData .= "<li class='list-group-item'>$model->user_ip : $model->controller_id/$model->action_id</li>";
            $debugData .= '</ul>';
        }
        return $debugData;
    }


    public function actionState($providerId)
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;
        return count(Track::findActiveTracksBy($providerId, \Yii::$app->user->id, Track::PRIORITY_NOTICE));
    }


    /**
     * Просмотр списка уведомлений.
     *
     * @return string
     */
    public function actionIndex($resourceId)
    {
        $dataProvider = new ActiveDataProvider([
            'query'      => Track::queryTracksBy($resourceId, \Yii::$app->user->identity->id),
            'pagination' => (\Yii::$app->request->get('page', 1) == 0) ? false : [
                'pageSize' => 50,
            ],
            'sort'       => [
                'attributes' => ['created_at'],
            ],
        ]);
        return $this->render($this->viewPath . '/index', [
            'dataProvider' => $dataProvider,
            'resourceId'     => $resourceId,
            'activeTracks' => Track::countModuleActiveTracks($resourceId, \Yii::$app->user->identity->id),
        ]);
    }


    /**
     * Описание отдельного уведомлений.
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView()
    {
        $model = Track::findOne(['id' => \Yii::$app->request->get('id', null),]);
        if (empty($model)) {
            throw new NotFoundHttpException("Указанная запись не существует или была удалена");
        } else {
            $model->viewed(\Yii::$app->user->identity->getId());
        }
        return $this->renderPartial($this->viewPath . '/view', [
            'model'     => $model,
            'debugData' => ResourceHelper::debugPrint(
                [
                    'value' => $this->_debugData($model),
                    'type'  => ResourceHelper::DEBUG_PRINT_PLAIN,
                ],
                false
            ),
        ]);
    }


    /**
     * Обрабатывает данные и создаёт запрос пользователя к администратору системы.
     *
     * @return string
     */
    /*public function actionAdd()
    {
        $model = new Feedback();
        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                \Yii::$app->session->setFlash('success', 'Ваш вопрос отправлен администратору системы');
                $message = "модуль : " . $model->moduleId
                    . "\r\n\r\n" . $model->message . "\r\n\r\n"
                    . "Пользователь: " . \Yii::$app->user->identity->username . "\r\n"
                    . "Почта: " . $model->email . "\r\n"
                    . "Телефон: " . $model->phone;
                $this->module->tracker->handle([
                    'module_id' => 'tracker',
                    'priority'  => Tracker::TRACK_PRIORITY_NOTICE,
                    'message'   => $message,
                ]);
                $this->module->tracker->sendNotices();
                $model = new Feedback();
            }
        }
        return $this->render('_add', ['model' => $model,]);
    }*/


    /**
     * Устанавливает все уведомления модуля веб-ресурса просмотренными для указанного пользователя.
     *
     * @param string $resourceId
     *
     * @return Response
     */
    public function actionViewed($resourceId)
    {
        Track::allViewedBy(
            $resourceId,
            \Yii::$app->user->identity->getId()
        );
        \Yii::$app->session->setFlash(
            'success',
            'Все уведомления отмечены как просмотренные.'
        );
        return $this->redirect(
            ["/" . $this->module->id . "/$resourceId"]
        );
    }


    /**
     * Удаление записи.
     *
     * @return \yii\web\Response
     */
    public function actionDelete()
    {
        $model = Track::findOne(['id' => \Yii::$app->request->post('id', null)]);
        if (empty($model)) {
            \Yii::$app->getSession()->setFlash(
                'warning',
                'Указанная запись не существует или была удалена.'

            );
        } else {
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
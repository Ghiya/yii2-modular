<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\users\controllers;


use modular\common\controllers\Controller;
use modular\panel\models\User;
use modular\panel\models\UserRole;
use modular\panel\modules\users\models\UserDataForm;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController контроллер записей пользователей панели администрирования системы
 *
 * @package panel\modules\users\controllers
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 *
 */
class DefaultController extends Controller
{


    public $viewPath = '@modular/panel/modules/users/views/default';


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
                        'actions' => ['index', 'view',],
                        'roles'   => [UserRole::PM_MANAGE_RESOURCE_DATA],
                    ],
                    [
                        'allow'         => true,
                        'actions'       => ['view',],
                        'matchCallback' => function () {
                            return
                                \Yii::$app->user->can(UserRole::PM_MANAGE_RESOURCE_DATA) ||
                                \Yii::$app->user->identity->id == \Yii::$app->request->get('id', null);
                        },
                    ],
                    [
                        'allow'         => true,
                        'actions'       => ['update',],
                        'matchCallback' => function () {
                            return
                                \Yii::$app->user->can(UserRole::PM_REMOVE_RESOURCE_DATA) ||
                                \Yii::$app->user->identity->id == \Yii::$app->request->get('id', null);
                        },
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['delete', 'add', 'refresh'],
                        'roles'   => [UserRole::PM_REMOVE_RESOURCE_DATA],
                    ],
                ],
            ]
        ];
    }


    /**
     * Переопределяет роли всех пользователей согласно текущим установкам.
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public function actionRefresh()
    {
        UserRole::refreshAll();
        \Yii::$app->getSession()->setFlash(
            "success",
            "Политика ролей обновлена"
        );
        return $this->redirect(['/' . $this->module->id]);
    }


    /**
     * Просмотр списка записей пользователей системы.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render(
            $this->viewPath . '/index',
            [
                'dataProvider' => new ActiveDataProvider(
                    [
                        'query'      => User::find()->orderBy(['id' => SORT_DESC]),
                        'pagination' => (\Yii::$app->request->get('page', 1) == 0) ? false : [
                            'pageSize' => 50,
                        ],
                        'sort'       => [
                            'attributes' => ['created_at'],
                        ],
                    ]
                )
            ]
        );
    }


    /**
     * Просмотр и обновление записи пользователя системы.
     *
     * @return string
     * @throws NotFoundHttpException если указанная запись не существует или была удалена
     */
    public function actionView()
    {
        $updateUserForm = new UserDataForm(['scenario' => UserDataForm::SCENARIO_UPDATE]);
        $user = User::findById(\Yii::$app->request->get('id', null));
        if (empty($user)) {
            throw new NotFoundHttpException("Указанная запись не существует или была удалена");
        }
        if (\Yii::$app->request->isPost) {
            $updateUserForm->load(\Yii::$app->request->post());
            if ($updateUserForm->validate()) {
                $user->attributes = $updateUserForm->toArray();
                $userUpdated = $user->update(false);
                if ($userUpdated) {
                    $user->addRole($updateUserForm->role);
                    UserRole::refreshAll();
                    \Yii::$app->getSession()->setFlash(
                        'success',
                        "Обновлены данные пользователя [ $user->name ]."
                    );
                } else {
                    \Yii::$app->getSession()->setFlash(
                        'warning',
                        "Не удалось обновить данные пользователя."
                    );
                }
                return $this->redirect(['/' . $this->module->id]);
            }
        } else {
            $updateUserForm->load($user->toArray());
            if (!empty($user->role)) {
                $updateUserForm->role = $user->role->value;
            }
        }
        return $this->render($this->viewPath . '/view', [
            'model'       => $updateUserForm,
            'editAllowed' => \Yii::$app->user->can(UserRole::PM_REMOVE_RESOURCE_DATA)
        ]);
    }


    /**
     * Действие добавления записи нового пользователя системы.
     *
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $addUserForm = new UserDataForm(['scenario' => UserDataForm::SCENARIO_DEFAULT]);
        if (\Yii::$app->request->isPost) {
            $addUserForm->load(\Yii::$app->request->post());
            if ($addUserForm->validate()) {
                $user = new User();
                $user->attributes = $addUserForm->toArray();
                $userAdded = $user->save(false);
                if ($userAdded) {
                    $user->addRole($addUserForm->role);
                    \Yii::$app->getSession()->setFlash(
                        'success',
                        "Добавлен новый пользователь [ $user->name ]."
                    );
                } else {
                    \Yii::$app->getSession()->setFlash(
                        'warning',
                        "Не удалось добавить нового пользователя."
                    );
                }
                return $this->redirect(['/' . $this->module->id]);
            }
        }
        return
            $this->render(
                '_add',
                [
                    'model' => $addUserForm,
                ]
            );
    }


    /**
     * Действие удаления записи пользователя системы.
     *
     * @return \yii\web\Response
     * @throws NotFoundHttpException если указанная запись не существует или была удалена
     */
    public function actionDelete()
    {
        $user = User::findById(\Yii::$app->request->post('id', null));
        if (empty($user)) {
            throw new NotFoundHttpException("Указанная запись не существует или была удалена");
        } else {
            $userRemoved = $user->delete();
            \Yii::$app->getSession()->setFlash(
                $userRemoved ? 'success' : 'warning',
                $userRemoved ?
                    "Удалена запись пользователя [ <strong>$user->username</strong> : $userRemoved ]." :
                    "Не удалось удалить запись пользователя [ <strong>$user->username</strong> ]."

            );
        }
        return $this->redirect(\Yii::$app->request->referrer);
    }

}
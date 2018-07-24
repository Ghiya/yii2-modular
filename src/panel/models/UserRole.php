<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\panel\models;


use yii\db\ActiveRecord;

/**
 * Class UserRole
 * Роль доступа пользователя панели администрирования системы.
 *
 * @property int    $id
 * @property int    $user_id
 * @property string $value
 *
 * @package modular\panel\models
 */
class UserRole extends ActiveRecord
{


    /**
     * @const string RL_ROOT
     */
    const RL_ROOT = 'root';


    /**
     * @const string RL_ADMINISTRATOR
     */
    const RL_ADMINISTRATOR = 'administrator';


    /**
     * @const string RL_ENGINEER
     */
    const RL_ENGINEER = 'engineer';


    /**
     * @const string
     */
    const RL_MANAGER = 'manager';


    /**
     * @const string RL_CALLCENTER
     */
    const RL_CALLCENTER = 'callcenter';


    /**
     * @const st RL_APIring
     */
    const RL_API = 'apiClient';


    /**
     * @const string PM_ACCESS_TRACKS
     */
    const PM_ACCESS_TRACKS = 'accessTracks';


    /**
     * @const string PM_ACCESS_LOGS
     */
    const PM_ACCESS_LOGS = 'accessLogs';


    /**
     * @const string PM_ACCESS_ACTIONS
     */
    const PM_ACCESS_ACTIONS = 'accessActions';


    /**
     * @const string PM_ACCESS_BUNDLES
     */
    const PM_ACCESS_BUNDLES = 'accessBundles';


    /**
     * @const string PM_MANAGE_USERS
     */
    const PM_MANAGE_USERS = 'manageUsers';


    /**
     * @const string PM_ACCESS_API
     */
    const PM_ACCESS_API = 'accessApi';


    /**
     * @const string PM_ACCESS_BILLING
     */
    const PM_ACCESS_BILLING = 'accessBilling';


    /**
     * @const string PM_ACCESS_SMSC_ENGINE
     */
    const PM_ACCESS_SMSC_ENGINE = 'accessSmscEngine';


    /**
     * @const string PM_VIEW_RESOURCE_DATA
     */
    const PM_VIEW_RESOURCE_DATA = 'viewResourceData';


    /**
     * @const string PM_MANAGE_RESOURCE_DATA
     */
    const PM_MANAGE_RESOURCE_DATA = 'manageResourceData';


    /**
     * @const string PM_REMOVE_RESOURCE_DATA
     */
    const PM_REMOVE_RESOURCE_DATA = 'removeResourceData';


    /**
     * @const string PM_VIEW_DEBUG_DATA
     */
    const PM_VIEW_DEBUG_DATA = 'viewDebugData';


    /**
     * @const string PM_MANAGE_ALL
     */
    const PM_MANAGE_ALL = 'manageAllWithFullAccess';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_users_roles';
    }


    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return
            [
                ['user_id', 'required'],
                ['value', 'string', 'length' => [1, 64]],
            ];
    }


    public function getDescription()
    {
        $list = self::rolesList();
        return
            isset($list[$this->value]) ?
                $list[$this->value] : "";
    }


    /**
     * Возвращает список всех ролей пользователей с их названиями.
     *
     * @return array|string
     */
    public static function rolesList()
    {
        return
            [
                self::RL_ROOT          => 'Суперадминистратор',
                self::RL_ADMINISTRATOR => 'Администратор',
                self::RL_ENGINEER      => 'Инженер',
                self::RL_MANAGER       => 'Менеджер',
                self::RL_CALLCENTER    => 'Сотрудник колл-центра',
                self::RL_API           => 'Клиент API',
            ];
    }


    /**
     * Возвращает список всех используемых разрешений ролей доступа пользователей с их названиями.
     *
     * @return array
     */
    public static function permissionsList()
    {
        return
            [
                self::PM_ACCESS_TRACKS        => 'Доступ к данным уведомлений.',
                self::PM_ACCESS_LOGS          => 'Доступ к данным логов запросов.',
                self::PM_ACCESS_BUNDLES       => 'Доступ к данным модулей.',
                self::PM_ACCESS_ACTIONS       => 'Доступ к активностям веб-ресурсов.',
                self::PM_ACCESS_API           => 'Доступ к API системы.',
                self::PM_ACCESS_BILLING       => 'Доступ к данным абонентов биллинговой системы.',
                self::PM_ACCESS_SMSC_ENGINE   => 'Доступ к SMSC.',
                self::PM_VIEW_RESOURCE_DATA   => 'Доступ для просмотра данных модулей ресурсов.',
                self::PM_MANAGE_RESOURCE_DATA => 'Доступ для изменения данных модулей ресурсов.',
                self::PM_REMOVE_RESOURCE_DATA => 'Доступ для удаления данных модулей ресурсов.',
                self::PM_VIEW_DEBUG_DATA      => 'Доступ для просмотра данных отладки.',
                self::PM_MANAGE_USERS         => 'Доступ к управлению пользователями.',
                self::PM_MANAGE_ALL           => 'Полный доступ к системе.',
            ];
    }


    /**
     * Возвращает строковое название разрешения роли доступа пользователя.
     *
     * @param string $permission
     *
     * @return null|string
     */
    public static function getPermissionLabel($permission = '')
    {
        return
            !empty($permission) && isset(self::permissionsList()[$permission]) ?
                (string)self::permissionsList()[$permission] :
                null;
    }


    /**
     * Возвращает список пользователей состоящих в данной роли.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id']);
    }


    /**
     * Возвращает массив принадлежности разрешений к ролям доступа.
     *
     * @return array
     */
    public static function getRolesPermissions()
    {
        return
            [
                self::RL_API           =>
                    [
                        self::PM_ACCESS_API,
                    ],
                self::RL_CALLCENTER    =>
                    [
                        self::PM_ACCESS_BILLING,
                        self::PM_VIEW_RESOURCE_DATA,
                        self::PM_ACCESS_SMSC_ENGINE
                    ],
                self::RL_MANAGER       =>
                    [
                        self::PM_ACCESS_ACTIONS,
                        self::PM_ACCESS_TRACKS
                    ],
                self::RL_ENGINEER      =>
                    [
                        self::PM_ACCESS_LOGS,
                        self::PM_MANAGE_RESOURCE_DATA,
                        self::PM_VIEW_DEBUG_DATA
                    ],
                self::RL_ADMINISTRATOR =>
                    [
                        self::PM_ACCESS_BUNDLES,
                        self::PM_REMOVE_RESOURCE_DATA,
                        self::PM_MANAGE_USERS
                    ],
                self::RL_ROOT          =>
                    [
                        self::PM_MANAGE_ALL,
                    ],
            ];
    }


    /**
     * Возвращает массив иерархии ролей доступа пользователей.
     *
     * @return array
     */
    public static function getRolesHierarchy()
    {
        return
            [
                self::RL_MANAGER       =>
                    [
                        self::RL_CALLCENTER,
                    ],
                self::RL_ENGINEER      =>
                    [
                        self::RL_MANAGER,
                        self::RL_API
                    ],
                self::RL_ADMINISTRATOR =>
                    [
                        self::RL_ENGINEER
                    ],
                self::RL_ROOT          =>
                    [
                        self::RL_ADMINISTRATOR
                    ],
            ];
    }


    /**
     * Обновляет политику RBAC пользователей в зависимости актуальных данных их ролей.
     *
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public static function refreshAll()
    {
        $auth = \Yii::$app->getAuthManager();
        $auth->removeAll();
        // add all existing roles
        foreach (self::rolesList() as $role => $roleLabel) {
            $userRole = $auth->createRole($role);
            $userRole->description = $roleLabel;
            $auth->add($userRole);
        }
        // add all existing permissions
        foreach (self::permissionsList() as $permission => $permissionLabel) {
            $accessPermission = $auth->createPermission($permission);
            $accessPermission->description = $permissionLabel;
            $auth->add($accessPermission);
        }
        // assign permissions to roles if required
        foreach (self::getRolesPermissions() as $role => $permissions) {
            foreach ($permissions as $permission) {
                if (!$auth->hasChild($auth->getRole($role), $auth->getPermission($permission))) {
                    $auth->addChild(
                        $auth->getRole($role),
                        $auth->getPermission($permission)
                    );
                }
            }
        }
        // build roles hierarchy if required
        foreach (self::getRolesHierarchy() as $role => $childRoles) {
            foreach ($childRoles as $childRole) {
                if (!$auth->hasChild($auth->getRole($role), $auth->getRole($childRole))) {
                    $auth->addChild(
                        $auth->getRole($role),
                        $auth->getRole($childRole)
                    );
                }
            }
        }
        // assign roles to users
        foreach (User::findActive() as $user) {
            if (!empty($user->role)) {
                $auth->assign(
                    $auth->getRole($user->role->value),
                    $user->id
                );
            }
        }
    }

}
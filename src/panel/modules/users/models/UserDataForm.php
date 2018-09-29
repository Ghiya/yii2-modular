<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\modules\users\models;


use modular\panel\models\User;
use yii\base\Model;

/**
 * Class UserDataForm форма добавления нового пользователя панели администрирования системы.
 *
 * @package modular\panel\modules\users\models
 */
class UserDataForm extends Model
{


    /**
     * @const string SCENARIO_DEFAULT
     */
    const SCENARIO_DEFAULT = 'addUserScenario';


    /**
     * @const string SCENARIO_UPDATE
     */
    const SCENARIO_UPDATE = 'updateUserScenario';


    /**
     * @var string $username
     */
    public $username;


    /**
     * @var string $password
     */
    public $password;


    /**
     * @var string $name
     */
    public $name;


    /**
     * @var string $email
     */
    public $email;


    /**
     * @var string $role
     */
    public $role;


    /**
     * @var int $status
     */
    public $status = User::STATUS_ACTIVE;


    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }


    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['username', 'password', 'name', 'role', 'status', 'email',],
            self::SCENARIO_UPDATE  => ['username', 'password', 'name', 'role', 'status', 'email',],
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return
            [
                [['username', 'name', 'group', 'status',], 'required',],
                ['password', 'required', 'on' => self::SCENARIO_DEFAULT,],
                ['status', 'in', 'range' => [User::STATUS_ACTIVE, User::STATUS_DELETED, User::STATUS_BLOCKED]],
                ['username', 'string', 'length' => [4, 64]],
                ['password', 'string', 'length' => [6, 12], 'skipOnEmpty' => true,],
                ['name', 'string', 'length' => [3, 255]],
                ['role', 'string', 'length' => [3, 255]],
                ['email', 'email', 'skipOnEmpty' => true],
            ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return
            [
                'username' => 'Логин',
                'password' => 'Пароль',
                'name'     => 'Имя',
                'role'     => 'Роль доступа пользователя',
                'status'   => 'Статус',
            ];
    }


    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return
            [
                'username' => 'логин доступа в систему от 5 до 64 символов',
                'password' => 'пароль доступа в систему от 8 до 12 символов',
                'name'     => 'имя пользователя от 3 до 255 символов',
            ];
    }

}
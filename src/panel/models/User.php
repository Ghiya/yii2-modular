<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\models;


use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


/**
 * User
 * Модель пользователя панели администрирования системы.
 *
 * @property integer       $id
 * @property string        $username
 * @property string        $password_hash
 * @property string        $password_reset_token
 * @property string        $name
 * @property string        $email
 * @property string        $auth_key
 * @property integer       $status
 * @property integer       $created_at
 * @property integer       $updated_at
 * @property-write string  $password
 * @property-read UserRole $role роль доступа пользователя
 *
 * @package modular\panel\models
 */
class User extends ActiveRecord implements IdentityInterface
{


    /**
     * Статус удалённого пользователя.
     */
    const STATUS_DELETED = 0;


    /**
     * Статус активного пользователя.
     */
    const STATUS_ACTIVE = 10;


    /**
     * Статус заблокированного пользователя.
     */
    const STATUS_BLOCKED = 20;


    /**
     * Возвращает список статусов пользователя.
     *
     * @return array
     */
    public static function states()
    {
        return
            [
                self::STATUS_ACTIVE  => 'Пользователь активен',
                self::STATUS_BLOCKED => 'Пользователь заблокирован',
                self::STATUS_DELETED => 'Пользователь удалён',
            ];
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'common__v1_users';
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
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return
            [
                [['username', 'password', 'name', 'email', 'status',], 'safe'],
            ];
    }


    /**
     * Возвращает все активные записи пользователей.
     *
     * @return static[]
     */
    public static function findActive()
    {
        return
            static::findAll(
                [
                    'status' => self::STATUS_ACTIVE,
                ]
            );
    }


    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->removeRoleIfExists();
        return parent::delete();
    }


    /**
     * Возвращает read-only роль доступа пользователя.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(UserRole::class, ['user_id' => 'id']);
    }


    /**
     * Удаляет роль доступа пользователя если она задана.
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function removeRoleIfExists()
    {
        if (!empty($this->role)) {
            $this->role->delete();
        }
    }


    /**
     * Устанавливает новую роль пользователя.
     *
     * @param string $role
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function addRole($role = '')
    {
        $this->removeRoleIfExists();
        $userRole = new UserRole();
        $userRole->attributes =
            [
                'user_id' => $this->id,
                'value'   => $role,
            ];
        $userRole->save(false);
        $this->refresh();
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $authorization = explode(" ", \Yii::$app->request->getHeaders()->get('Authorization', ""));
        if (count($authorization) == 2) {
            list($username, $password) = explode(":", base64_decode(array_pop($authorization)));
            $identity = static::findOne(['username' => $token, 'status' => self::STATUS_ACTIVE]);
            if ($token == $username && !empty($identity) && $identity->validatePassword($password)) {
                return $identity;
            }
        }
        return null;
    }


    /**
     * @param $username
     *
     * @return null|static
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }


    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    /**
     * @param $password
     *
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


    /**
     * @param $password
     *
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash =
            !empty($password) ?
                Yii::$app->security->generatePasswordHash($password) :
                $this->password_hash;
    }


    /**
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}

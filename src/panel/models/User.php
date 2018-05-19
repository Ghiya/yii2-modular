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
 * User модель пользователя панели администрирования системы
 *
 * @property integer  $id
 * @property string   $username
 * @property string   $password_hash
 * @property string   $password_reset_token
 * @property string   $name
 * @property string   $email
 * @property string   $auth_key
 * @property integer  $status
 * @property integer  $created_at
 * @property integer  $updated_at
 * @property string   $password write-only password
 * @property UserRole $role     read-only роль доступа пользователя
 *
 * @package modular\panel\models
 */
class User extends ActiveRecord implements IdentityInterface
{


    /**
     * @const int STATUS_DELETED статус удалённого пользователя
     */
    const STATUS_DELETED = 0;


    /**
     * @const int STATUS_ACTIVE статус активного пользователя
     */
    const STATUS_ACTIVE = 10;


    /**
     * @const int STATUS_BLOCKED статус заблокированного пользователя
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
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_users';
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
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }


    /**
     * @inheritdoc
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
        return static::findAll(
            [
                'status' => self::STATUS_ACTIVE,
            ]
        );
    }


    /**
     * Возвращает запись пользователя с указанным идентификатором.
     *
     * @param int $userId
     *
     * @return User|null
     */
    public static function findById($userId = 0)
    {
        return static::findOne(['id' => $userId,]);
    }


    /**
     * @inheritdoc
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
        return $this->hasOne(UserRole::className(), ['user_id' => 'id']);
    }


    /**
     * Удаляет роль доступа пользователя если она задана.
     *
     * @throws \Exception
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
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * @inheritdoc
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
     * Finds user by username
     *
     * @param string $username
     *
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        return null;
    }


    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        return null;
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }


    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }


    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    /**
     * Validates password
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash =
            !empty($password) ?
                Yii::$app->security->generatePasswordHash($password) :
                $this->password_hash;
    }


    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }


    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        return null;
    }


    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        return null;
    }
}
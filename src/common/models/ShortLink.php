<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace common\models;


use yii\db\ActiveRecord;

/**
 * Class ShortLink модель записей коротких ссылок для панелей администрирования модулей.
 *
 * @property int    $id
 * @property string $hash
 * @property string $link
 * @property int    $valid_till
 *
 * @package common\models
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class ShortLink extends ActiveRecord
{

    /**
     * @const int VALID_TILL_INTERVAL продолжительность действия ссылки
     */
    const VALID_TILL_INTERVAL = 3600 * 48;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_links';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['link', 'string', 'length' => [0, 255],],
            ['link', 'required'],
        ];
    }

    /**
     * Добавляет ссылку и возвращает сформированный хэш.
     *
     * @param string $link
     *
     * @return bool|string вернёт `false` если ссылка не была добавлена
     */
    public function add($link = '')
    {
        $this->link = $link;
        if ($this->validate()) {
            $this->hash = md5($this->link);
            if (!self::hasHash($this->hash)) {
                $this->valid_till = time() + self::VALID_TILL_INTERVAL;
                $this->save(false);
            }
            return $this->hash;
        } else {
            return false;
        }
    }

    /**
     * Возвращает ссылку по указанному хешу.
     *
     * @param string $hash
     *
     * @return string если ссылка не найдена или устарела, то вернёт пустую строку
     */
    public static function findByHash($hash = '')
    {
        $model = static::find()->where(['hash' => $hash,])->filterWhere(['>', 'valid_till', time()])->one();
        return (!empty($model)) ? $model->link : '';
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public static function hasHash($hash = '')
    {
        return static::find()->where(['hash' => $hash,])->filterWhere(['>', 'valid_till', time()])->exists();
    }

}
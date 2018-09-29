<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class LinkedUrl
 * Модель данных URL связанного с пакетом веб-ресурса.
 *
 * @property int              $id
 * @property int              $init_id
 * @property string           $url
 * @property bool             $is_active
 * @property int              $updated_at
 * @property int              $created_at
 * @property-read PackageInit $packageInit
 *
 * @package modular\core\models
 */
class LinkedUrl extends ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_urls';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    /**
     * Возвращает связанный пакет модуля веб-ресурса.
     *
     * @return \yii\db\ActiveQuery|PackageInit
     */
    public function getPackageInit()
    {
        return $this->hasOne(PackageInit::class, ['id' => 'init_id',]);
    }

}

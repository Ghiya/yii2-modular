<?php
/*
 * Copyright (c) 2016 - 2024 Ghiya Mikadze <g.mikadze@lakka.io>
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


    /**
     * Возвращает запись связанного URL пакета модуля системы
     * относительно указанного URL пакета.
     *
     * @param string $packageUrl
     *
     * @return LinkedUrl|null
     */
    public static function findRegisteredByPackageUrl(string $packageUrl): ?LinkedUrl
    {
        return static::findOne(['url' => $packageUrl, 'is_active' => 1,]);
    }


    /**
     * Возвращает запись связанного URL пакета модуля системы
     * относительно указанных URL или IP.
     *
     * @param string|null $urlOrIp
     * @param string|null $port
     *
     * @return array|ActiveRecord|LinkedUrl|null
     */
    public static function findRegisteredByUrlOrIp(?string $urlOrIp, ?string $port): ?LinkedUrl
    {
        return static::find()
            ->where(['url' => $urlOrIp])
            ->orWhere(['url' => "$urlOrIp:$port"])
            ->andWhere(['is_active' => 1])
            ->one();
    }
}

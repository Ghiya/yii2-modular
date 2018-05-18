<?php

namespace modular\common\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class ModuleUrl модель URL связанного с пакетом модуля веб-ресурса.
 *
 * @property int    $id
 * @property int    $init_id
 * @property string $url
 * @property bool   $is_active
 * @property int    $updated_at
 * @property int    $created_at
 * @property ModuleInit $init read-only
 *
 * @package modular\common\models
 * @author  Ghiya Mikadze <gmikadze@v-tell.com>
 */
class ModuleUrl extends ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'modular\common__v1_urls';
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
     * Возвращает связанный пакет модуля веб-ресурса.
     *
     * @return \yii\db\ActiveQuery|ModuleInit
     */
    public function getInit()
    {
        return $this->hasOne(ModuleInit::className(), ['id' => 'init_id',]);
    }

}

<?php

namespace modular\common\models;


use yii\base\Model;


/**
 * Class ResourceSwitch
 *
 * @package modular\common\models
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class ResourceSwitch extends Model
{


    /**
     * @var array $moduleId
     */
    public $moduleId = [];


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
        return [
            [['moduleId',], 'string'],
        ];
    }

}
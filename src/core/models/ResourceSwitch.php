<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\models;


use yii\base\Model;


/**
 * Class ResourceSwitch
 *
 * @package modular\core\models
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
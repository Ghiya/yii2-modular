<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\models;


use yii\base\Model;


/**
 * Class TrackerSwitch
 *
 * @package modular\panel\models
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class TrackerSwitch extends Model
{


    /**
     * @var array $moduleId
     */
    public $moduleId = [];


    /**
     * @var string $timestamp
     */
    public $timestamp = '';


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
            [['moduleId', 'timestamp',], 'string'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'moduleId' => 'Модуль',
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'moduleId' => 'Уведомления выбранного модуля',
        ];
    }


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->moduleId)) {
            $this->moduleId = 'tracker';
        }
    }

}
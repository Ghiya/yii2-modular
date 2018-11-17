<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\behaviors;


use modular\core\Controller;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Class RequestsIndexBehavior
 *
 * @property-read Controller $owner
 *
 * @package modular\core\behaviors
 */
class RequestsIndexBehavior extends Behavior
{


    /**
     * @var array
     */
    public $rules = [];


    /**
     * @var ActiveRecord
     */
    public $indexModel;


    /**
     * @var ActionEvent
     */
    private $_actionEvent;


    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return
            [
                Controller::EVENT_BEFORE_ACTION => function ($event) {
                    $this->_actionEvent = $event;
                    $this->indexRequest();
                }
            ];
    }


    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->rules)) {
            throw new InvalidConfigException("Свойство `rules` является обязательным.");
        }
        if (!is_array($this->rules)) {
            throw new InvalidConfigException("Свойство `rules` может быть только ассоциативным массивом.");
        }
        if (empty($this->indexModel)) {
            throw new InvalidConfigException("Свойство `indexModel` является обязательным.");
        }
        if (!($this->indexModel instanceof ActiveRecord)) {
            throw new InvalidConfigException("Допустимый класс для `indexModel` только ActiveRecord.");
        }
    }


    public function indexRequest()
    {
        if ($this->_getRule()) {
            $this->addIndex();
        }
    }


    protected function addIndex()
    {
        $this->indexModel->setAttributes(
            [
                'session'     => $this->_getSessionId(),
                'resource'    => \Yii::$app->id,
                'user'        => $this->_getUserId(),
                'description' => $this->_getDescription(),
                'index'       => [
                    'route'  => $this->owner->id . "/" . $this->_getActionId(),
                    'params' => \Yii::$app->request->getParams()
                ]
            ]
        );
        $this->indexModel->save();
    }


    /**
     * @return bool|mixed
     */
    private function _getRule()
    {
        return
            in_array($this->_getActionId(), array_keys($this->rules)) ?
                $this->rules[$this->_getActionId()] : false;
    }


    /**
     * @return string
     */
    private function _getDescription()
    {
        return '';
    }


    /**
     * @return int|null|string
     */
    private function _getUserId()
    {
        return
            !empty(\Yii::$app->getUser()) ? \Yii::$app->getUser()->getId() : null;
    }


    /**
     * @return null|string
     */
    private function _getSessionId()
    {
        return
            !empty(\Yii::$app->getSession()) ? \Yii::$app->session->getId() : null;
    }


    /**
     * @return string
     */
    private function _getActionId()
    {
        return $this->_actionEvent->action->id;
    }


}
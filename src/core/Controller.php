<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core;

use modular\core\helpers\ArrayHelper;
use modular\panel\models\User;
use modular\panel\PanelModule;
use modular\resource\ResourceModule;

/**
 * Class Controller
 * Абстрактный базовый класс контроллера модуля ресурса.
 *
 * @property PanelModule|ResourceModule $module
 *
 * @package modular\core
 */
abstract class Controller extends \yii\web\Controller
{


    /**
     * @var string|array
     */
    public $breadcrumb = 'Контроллер';


    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function render($view, $params = [])
    {
        return
            parent::render(
                $this->module instanceof PanelModule ?
                    $this->viewWithRole($view) : $view,
                ArrayHelper::merge(
                    [
                        'breadcrumb' => $this->breadcrumb
                    ],
                    $params
                )
            );
    }


    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function renderPartial($view, $params = [])
    {
        return
            parent::renderPartial(
                $this->module instanceof PanelModule ?
                    $this->viewWithRole($view) : $view,
                $params);
    }


    /**
     * @param string $view
     *
     * @return string
     * @throws \Throwable
     */
    protected function viewWithRole($view = "")
    {
        /** @var User $userIdentity */
        $userIdentity = \Yii::$app->user->getIdentity();
        $roleView = "$view-" . $userIdentity->role->value;
        return
            file_exists(\Yii::getAlias($this->viewPath . "/" . $roleView . ".php")) ?
                $roleView : $view;
    }

}
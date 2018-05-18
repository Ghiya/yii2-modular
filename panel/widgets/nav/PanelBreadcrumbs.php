<?php

namespace panel\widgets\nav;


use yii\widgets\Breadcrumbs;


/**
 * Class PanelBreadcrumbs
 *
 * @package panel\widgets\nav
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class PanelBreadcrumbs extends Breadcrumbs
{


    /**
     * @var array
     */
    public $panelItems = [];


    /**
     * @var array $options
     */
    public $options = ['class' => 'breadcrumb',];


    /**
     * @inheritdoc
     */
    public function init()
    {
        // устанавливает корневой элемент цепочки
        $this->homeLink = ['label' => \Yii::$app->name, 'url' => \Yii::$app->homeUrl,];
        if ( \Yii::$app->controller->module->canGetProperty('title') ) {
            array_unshift($this->links, \Yii::$app->controller->module->title);
        }
        /*foreach ($this->panelItems as $navItem) {
            if (!empty($navItem[ 'active' ])) {
                $this->homeLink = ['label' => $navItem[ 'label' ],];
            }
            if (!empty($navItem[ 'items' ]) && \Yii::$app->controller->action->id != 'index') {
                foreach ($navItem[ 'items' ] as $navSubItem) {
                    if (!empty($navSubItem[ 'active' ])) {
                        $this->homeLink = ['label' => strip_tags($navSubItem[ 'label' ]),];
                    }
                }
            }
        }*/
        parent::init();
    }

}
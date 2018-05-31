<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel;


use modular\core\Application;
use modular\core\models\ModuleInit;
use yii\helpers\ArrayHelper;


/**
 * Class PanelApplication
 * Приложение административных панелей модулей ресурсов.
 *
 * @property array $panelItems   read-only массив элементов меню модулей веб-ресурсов системы
 * @property array $serviceItems read-only массив элементов меню компонентов системы
 *
 * @package modular\panel
 */
class PanelApplication extends Application
{


    /**
     * {@inheritdoc}
     *
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     */
    final public function bootstrap()
    {
        // parent bootstrapping always goes first because of the modules installing as extensions
        parent::bootstrap();
        foreach (
            ModuleInit::findResources(
                [self::PANEL_APP_ID,],
                true
            ) as $moduleInit) {
            $this->registerModule($moduleInit);
        }
    }


    /**
     * Возвращает read-only параметры виджета меню для всех администрируемых модулей веб-ресурсов.
     *
     * @return array
     */
    public function getPanelItems()
    {
        $panelItems = [];
        foreach (ArrayHelper::merge($this->providers, $this->panels, $this->services) as $resource) {
            if (!empty($resource->panelItems)) {
                $panelItems[] = $resource->panelItems;
            }
        }
        return $panelItems;
    }

}
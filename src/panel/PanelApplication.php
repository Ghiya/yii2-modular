<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel;


use common\Application;
use modular\common\models\ModuleInit;
use yii\helpers\ArrayHelper;


/**
 * Class PanelApplication
 * Приложение административных панелей ресурсов системы управления.
 *
 * @property array $panelItems   read-only массив элементов меню модулей веб-ресурсов системы
 * @property array $serviceItems read-only массив элементов меню компонентов системы
 *
 * @package modular\panel
 */
final class PanelApplication extends Application
{


    /**
     * @inheritdoc
     */
    public function bootstrap()
    {
        foreach (
            ModuleInit::findResources(
                [self::PANEL_APP_ID,],
                true
            ) as $moduleInit) {
            $this->registerModule($moduleInit);
        }
        parent::bootstrap();
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
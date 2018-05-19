<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common\modules;

use modular\common\helpers\ArrayHelper;


/**
 * Class Module базовый класс модуля ресурса системы.
 *
 * @property string $safeId         read-only идентификатор модуля с заменой символа `.`
 * @property string $strictId       read-only идентификатор модуля без указания папки расположения
 *
 * @package modular\common\modules
 */
abstract class Module extends \yii\base\Module
{


    /**
     * @var string $title название модуля
     */
    public $title = '';


    /**
     * @var string $description описание модуля
     */
    public $description = '';


    /**
     * @var bool $isProvider если модуль ресурса провайдера данных внешнего сервиса
     */
    public $isProvider = false;


    /**
     * @var bool $isService если модуль системного компонента
     */
    public $isService = false;


    /**
     * @var bool $isResource если модуль веб-ресурса системы
     */
    public $isResource = false;


    /**
     * Возвращает read-only идентификатор модуля с заменой символа `.`;
     *
     * @param string $safeReplace если опционально требуется замена отличная от `-`
     *
     * @return string
     */
    public function getSafeId($safeReplace = "-")
    {
        return preg_match("/\./i", $this->id) ?
            (string)preg_replace("/\./i", $safeReplace, $this->id) :
            $this->id;
    }


    /**
     * Возвращает read-only идентификатор модуля без указания папки расположения.
     * @return string
     */
    public function getStrictId()
    {
        if (preg_match("/\./i", $this->id)) {
            $aModuleId = explode(".", $this->id);
            return (string)array_pop($aModuleId);
        }
        else {
            return $this->id;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::configure($this, $this->getConfig());
    }


    /**
     * @return array
     */
    protected function getConfig()
    {
        return
            file_exists(__DIR__ . '/config/config-local.php') ?
                ArrayHelper::merge(
                    require __DIR__ . '/config/config.php',
                    require __DIR__ . '/config/config-local.php'
                ) :
                require __DIR__ . '/config/config.php';
    }

}
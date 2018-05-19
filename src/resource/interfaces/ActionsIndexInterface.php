<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace resource\interfaces;


/**
 * Interface ActionsIndexInterface
 * интерфейс модуля ресурса с поддержкой индексации действий пользователя
 *
 * @package resource\interfaces
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
interface ActionsIndexInterface
{


    /**
     * Возвращает идентификатор абонента.
     *
     * @return null|string
     */
    public function getIndex();


    /**
     * Возвращает данные просмотра в панели администрирования индексированного действия.
     *
     * @return string в JSON формате
     */
    public function getIndexPanelLink();


    /**
     * Если требуется сохранять активность действия.
     *
     * @return bool
     */
    public function shouldIndexAction();


    /**
     * Возвращает массив описаний действий и контроллеров модуля.
     *
     * @return array
     */
    public static function indexActions();


    /**
     * Возвращает описание действия абонента.
     *
     * @param string $controllerId
     * @param string $actionId
     *
     * @return string
     */
    public function indexDescription($controllerId = '', $actionId = '');


}
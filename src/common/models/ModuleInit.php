<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common\models;


use common\Application;
use modular\common\modules\Module;
use resource\models\ActionsIndex;
use yii\base\ErrorException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;


/**
 * Class ModuleInit модель идентификационных параметров модуля ресурса системы управления.
 *
 * @property string         $section_id      расположение модуля
 * @property string         $module_id       идентификатор
 * @property string         $version         версия
 * @property string         $title           название
 * @property string         $description     описание
 * @property int            $type            тип ресурса
 * @property string         $params          параметры ресурса в JSON формате
 * @property bool           $is_active       если активен
 * @property int            $created_at
 * @property int            $updated_at
 * @property bool           $isProvider      read-only если модель системных параметров модуля провайдера
 * @property bool           $isService       read-only если модель системных параметров служебного компонента
 * @property bool           $isResource      read-only если модель системных параметров модуля веб-ресурса
 * @property Module         $resource        read-only модуль
 * @property string         $moduleId        read-only идентификатор модуля
 * @property string         $resourceAlias   read-only путь к модулю
 * @property string         $resourcePath    read-only пространство имён модуля
 * @property string         $defaultRoute    read-only роутинг по-умолчанию
 * @property array          $bundleParams    параметры модуля ресурса
 * @property ModuleUrl[]    $links           read-only массив связанных URL
 * @property ActionsIndex[] $actions         read-only массив моделей записей действий абонента
 * @property ModuleInit     $relatedResource read-only запись ресурса для панели администрирования
 *
 * @package modular\common\models
 */
class ModuleInit extends ActiveRecord
{


    /**
     * @const int TYPE_RESOURCE тип модуля веб-ресурса
     */
    const TYPE_RESOURCE = 0;


    /**
     * @const int TYPE_PROVIDER тип модуля провайдер данных внешних сервисов
     */
    const TYPE_PROVIDER = 1;


    /**
     * @const int TYPE_SERVICE тип модуля служебного системного сервиса
     */
    const TYPE_SERVICE = 2;


    /**
     * @var Module $_resource модуль ресурса
     */
    private $_resource;


    /**
     * @var string $_moduleId
     */
    private $_moduleId = '';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_resources';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [['section_id', 'module_id', 'version', 'title', 'description'], 'string',],
                ['is_active', 'boolean',]
            ]
        );
    }


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
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'is_active'   => 'Активность',
            'title'       => 'Название',
            'description' => 'Описание',
        ];
    }


    /**
     * Возвращает read-only идентификатор модуля с заменой символа `.`;
     *
     * @param string $safeReplace если опционально требуется замена отличная от `-`
     *
     * @return string
     */
    public function getSafeId($safeReplace = "-")
    {
        return preg_match("/\./i", $this->module_id) ?
            (string)preg_replace("/\./i", $safeReplace, $this->module_id) :
            $this->module_id;
    }


    /**
     * Возвращает массив пользовательских параметров модуля.
     *
     * @return array
     */
    public function getBundleParams()
    {
        return Json::decode($this->params);
    }


    /**
     * Устанавливает данные пользовательских параметров модуля.
     *
     * @param array $bundleParams
     */
    public function setBundleParams($bundleParams = [])
    {
        $this->params = Json::encode(
            !empty($this->params) ?
                ArrayHelper::merge(
                    Json::decode($this->params),
                    $bundleParams
                ) :
                $bundleParams
        );
    }


    /**
     * Возвращает read-only массив моделей URL связанных с пакетом.
     * @return \yii\db\ActiveQuery|ModuleUrl[]
     */
    public function getLinks()
    {
        return $this->hasMany(ModuleUrl::className(), ['init_id' => 'id',]);
    }


    /**
     * Возвращает read-only массив моделей записей действий абонента.
     * @return ActiveQuery
     */
    public function getActions()
    {
        return $this->hasMany(ActionsIndex::className(), ['resource_id' => 'id',]);
    }


    /**
     * Возвращает read-only значение роутинга по-умолчанию для модуля веб-ресурса пакета.
     * @return string
     */
    public function getDefaultRoute()
    {
        return (!empty($this->version)) ? $this->version . '/default/index' : 'default/index';
    }


    /**
     * Если модель системных параметров модуля провайдера API взаимодействия с внешним сервисом.
     * @return bool
     */
    public function getIsProvider()
    {
        return $this->type == self::TYPE_PROVIDER;
    }


    /**
     * Если модель системных параметров модуля служебного компонента.
     * @return bool
     */
    public function getIsService()
    {
        return $this->type == self::TYPE_SERVICE;
    }


    /**
     * Если модель системных параметров модуля веб-ресурса.
     * @return bool
     */
    public function getIsResource()
    {
        return $this->type == self::TYPE_RESOURCE;
    }


    /**
     * Возвращает read-only запись ресурса для панели администрирования.
     * @return null|static
     */
    public function getRelatedResource()
    {
        return ($this->section_id == Application::PANEL_APP_ID) ?
            static::findOne(
                [
                    'section_id' => Application::RESOURCE_APP_ID,
                    'module_id'  => $this->module_id
                ]
            ) :
            null;
    }


    /**
     * Возвращает read-only путь к модулю на сервере.
     * @return string
     */
    public function getResourceAlias()
    {
        return \Yii::getAlias(
            (empty($this->version)) ?
                '@' . $this->section_id . '/' . $this->getSafeId("/") :
                '@' . $this->section_id . '/' . $this->getSafeId("/") . '/' . $this->version
        );
    }


    /**
     * Возвращает read-only путь к модулю веб-ресурса.
     *
     * @return string если файл класса модуля не найден в соответствующей папке, то вернёт путь базового класса модуля
     *                ресурса системы
     */
    public function getResourcePath()
    {
        return (empty($this->version)) ?
            $this->section_id . '\\' .
            $this->getSafeId("\\") :
            $this->section_id . '\\' . $this->getSafeId("\\") . '\\' . $this->version;
    }


    /**
     * Возвращает модель параметров ресурса для URL текущего запроса.
     * @return ModuleInit
     * @throws NotFoundHttpException если пакет не был найден или неактивен
     */
    public static function findResourceByUrl()
    {
        /** @var ModuleUrl $url */
        $url = ModuleUrl::findOne(['url' => $_SERVER['SERVER_NAME'], 'is_active' => 1,]);
        if (!empty($url)) {
            if ($url->init->is_active) {
                return $url->init;
            }
            else {
                throw new NotFoundHttpException('Пакет для указанного URL неактивен.');
            }
        }
        else {
            throw new NotFoundHttpException('Пакет не зарегистрирован или URL неактивен.');
        }
    }


    /**
     * Возвращает модель параметров ресурса по указанным параметрам.
     *
     * @param array $resourceId  символьный идентификатор ресурса или его приложения
     * @param bool  $returnQuery если требуется вернуть запрос а не его результат
     *
     * @return ModuleInit[]|ActiveQuery
     */
    public static function findResourcesById($resourceId = [], $returnQuery = false)
    {
        $resourceId = (array)$resourceId;
        $query = static::find()
            ->andFilterWhere(['or like', 'module_id', $resourceId])
            ->orFilterWhere(['or like', 'section_id', $resourceId]);
        return ($returnQuery) ? $query : $query->all();
    }


    /**
     * Возвращает массив моделей параметров модулей системы.
     *
     * @param array $ids        массив идентификаторов приложениий или модулей
     * @param bool  $activeOnly если в ответе только активные модули
     *
     * @return ModuleInit[]
     */
    public static function findResources($ids = [], $activeOnly = true)
    {
        return static::findResourcesById($ids, true)
            ->andFilterWhere(['is_active' => $activeOnly ? 1 : null,])
            ->all();
    }


    /**
     * Возвращает read-only модуль ресурса модели.
     * @return Module
     * @throws ErrorException если модуль ресурса не был добавлен в приложение
     */
    public function getResource()
    {
        if (empty($this->_resource)) {
            if (\Yii::$app->hasModule($this->moduleId)) {
                $this->_resource = \Yii::$app->getModule($this->moduleId);
            }
            else {
                throw new ErrorException("Не удалось идентифицировать модуль `$this->moduleId` приложения `$this->section_id`");
            }
        }
        return $this->_resource;
    }


    /**
     * Возвращает read-only идентификатор модуля.
     * @return string
     */
    public function getModuleId()
    {
        if (empty($this->_moduleId)) {
            if ($this->isProvider) {
                $this->_moduleId = "$this->section_id.$this->module_id";
            }
            else {
                $this->_moduleId = (Dispatcher::app()->isBackend) ? $this->module_id : $this->version;
            }
            $this->_moduleId = (Dispatcher::app()->isBackend) ? $this->module_id : $this->version;
        }
        return $this->_moduleId;
    }

}
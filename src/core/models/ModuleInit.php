<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\models;


use modular\core\Application;
use modular\core\Module;
use modular\panel\PanelModule;
use modular\resource\models\ActionsIndex;
use modular\resource\ResourceModule;
use yii\base\ErrorException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;


/**
 * Class ModuleInit
 * Модель идентификационных параметров модуля ресурса.
 *
 * @property string              $module_id       идентификатор
 * @property string              $version         версия
 * @property string              $title           название
 * @property string              $description     описание
 * @property string              $params          параметры ресурса в JSON формате
 * @property bool                $is_active       если активен
 * @property int                 $created_at
 * @property int                 $updated_at
 * @property-read string         $routeId         идентификатор для URI роутинга
 * @property-read bool           $isProvider      если модель системных параметров модуля провайдера
 * @property-read bool           $isService       если модель системных параметров служебного компонента
 * @property-read bool           $isResource      если модель системных параметров модуля веб-ресурса
 * @property-read Module         $resource        модуль
 * @property-read string $appId идентификатор модуля в приложении
 * @property-read string         $resourceAlias   путь к модулю
 * @property-read string         $resourcePath    пространство имён модуля
 * @property-read array          $bundleParams    параметры модуля ресурса
 * @property-read ModuleUrl[] $urls массив связанных URL
 * @property-read ActionsIndex[] $actions         массив моделей записей действий абонента
 * @property-read ModuleInit     $relatedResource запись ресурса для панели администрирования
 *
 * @package modular\core\models
 */
class ModuleInit extends ActiveRecord
{


    /**
     * @var Module $_resource модуль ресурса
     */
    private $_resource;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_init';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return
            [
                [['module_id', 'version', 'title', 'description', 'params',], 'string',],
                ['is_active', 'boolean',]
            ];
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
            TimestampBehavior::class,
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
     * Возвращает массив моделей URL связанных с пакетом.
     *
     * @return \yii\db\ActiveQuery|ModuleUrl[]
     */
    public function getUrls()
    {
        return $this->hasMany(ModuleUrl::class, ['init_id' => 'id',]);
    }


    /**
     * Возвращает read-only массив моделей записей действий абонента.
     * @return ActiveQuery
     */
    public function getActions()
    {
        return $this->hasMany(ActionsIndex::class, ['resource_id' => 'id',]);
    }


    /**
     * Возвращает путь папки для хранения логов модуля.
     *
     * @return bool|string
     */
    protected function getLogFolderPath()
    {
        $path = \Yii::getAlias('@common/logs/resources');
        if (!is_readable($path)) {
            mkdir($path);
        }
        $path = "$path/" . $this->module_id;
        if (!is_readable($path)) {
            mkdir($path);
        }
        $path = "$path/" . date("Y-m-d");
        if (!is_readable($path)) {
            mkdir($path);
        }
        return $path;
    }


    /**
     * Возвращает полный путь для актуального файла лога.
     *
     * @return string
     */
    protected function getLogFile()
    {
        return !empty($this->version) ?
            $this->getLogFolderPath() . "/$this->version" . date("_H_00") . ".log" :
            $this->getLogFolderPath() . "/" . date("H_00") . ".log";
    }


    /**
     * Возвращает параметры конфигурации Yii2 компонента лога.
     *
     * @return array
     */
    public function getLogParams()
    {
        return
            [
                'logFile'        => $this->getLogFile(),
                'exportInterval' => 1,
                'levels'         => ['error', 'info', 'trace', 'warning',],
                'categories'     => [],
                'prefix'         => function () {
                    return "[" . \Yii::$app->request->userIP . "]";
                },
            ];
    }


    /**
     * Возвращает строковое значение идентификатора для URI роутинга контроллера модуля используемого по-умолчанию.
     *
     * @return string
     */
    public function getRouteId()
    {
        //return (!empty($this->version)) ? $this->version : $this->module_id;
    }



    /**
     * Возвращает путь к модулю веб-ресурса на сервере.
     *
     * @return string
     */
    /*public function getResourceAlias()
    {
        return
            \Yii::getAlias(
                (empty($this->version)) ?
                    "@$this->module_id/" . Application::RESOURCE_APP_ID :
                    "@$this->module_id/" . Application::RESOURCE_APP_ID . "/$this->version"
            );
    }*/


    /**
     * Возвращает путь к модулю веб-ресурса.
     *
     * @return string
     */
    /*public function getResourcePath()
    {
        return (empty($this->version)) ?
            "$this->module_id\\" . Application::RESOURCE_APP_ID :
            "$this->module_id\\" . Application::RESOURCE_APP_ID . "\\$this->version";
    }*/


    /**
     * @return bool|string
     */
    /*public function getPanelAlias() {
        return
            \Yii::getAlias(
                "@$this->module_id/" . Application::PANEL_APP_ID
            );
    }*/


    /**
     * @return array|ModuleInit|ModuleInit[]
     * @throws ServerErrorHttpException
     */
    public static function getItems() {
        /** @var ModuleUrl $url */
        $url = ModuleUrl::findOne(['url' => $_SERVER['SERVER_NAME'], 'is_active' => 1,]);
        if ( !empty($url) ) {
            if (empty($url->init)) {
                throw new ServerErrorHttpException('Undefined resource for the `' . $_SERVER['SERVER_NAME'] . '`.');
            }
            return $url->init;
        }
        else {
            return static::find()->all();
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
    /*public static function findResourcesById($resourceId = [], $returnQuery = false)
    {
        $resourceId = (array)$resourceId;
        $query = static::find()
            ->andFilterWhere(['or like', 'module_id', $resourceId])
            ->orFilterWhere(['or like', 'section_id', $resourceId]);
        return ($returnQuery) ? $query : $query->all();
    }*/


    /**
     * Возвращает массив моделей параметров модулей системы.
     *
     * @param array $ids        массив идентификаторов приложениий или модулей
     * @param bool  $activeOnly если в ответе только активные модули
     *
     * @return ModuleInit[]
     */
    /*public static function findResources($ids = [], $activeOnly = true)
    {
        return static::findResourcesById($ids, true)
            ->andFilterWhere(['is_active' => $activeOnly ? 1 : null,])
            ->all();
    }*/


    /**
     * Возвращает read-only модуль ресурса модели.
     * @return Module
     * @throws ErrorException если модуль ресурса не был добавлен в приложение
     */
    /*public function getResource()
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
    }*/


    /**
     * @return object|ResourceModule|PanelModule
     * @throws \yii\base\InvalidConfigException
     */
    public function appendModule() {
        if ( file_exists($this->getPath() . "/Module.php") ) {
            $moduleId = Application::isPanel() ? $this->module_id : $this->version;
            \Yii::$app->setModule(
                $moduleId,
                [
                    'class' => $this->getClass(),
                    'title'        => $this->title,
                    'description'  => $this->description,
                    'bundleParams' => $this->toArray()
                ]
            );
            return \Yii::$app->getModule($moduleId);
        }
        return null;
    }

    public function getPath() {
        return
            \Yii::getAlias(
                Application::isPanel() ?
                    "@$this->module_id/" . \Yii::$app->id :
                    "@$this->module_id/" . \Yii::$app->id . "/$this->version"
            );
    }

    public function getClass() {
        return
            Application::isPanel() ?
                "$this->module_id\\" . \Yii::$app->id . "\\Module" :
                "$this->module_id\\" . \Yii::$app->id . "\\$this->version\Module";
    }

}
<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\models;


use modular\core\Application;
use modular\core\helpers\ArrayHelper;
use modular\resource\models\ActionsIndex;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\ServerErrorHttpException;


/**
 * Class PackageInit
 * Модель идентификационных параметров пакета веб-ресурса.
 *
 * @property string         $module_id   идентификатор
 * @property string         $version     версия
 * @property string         $title       название
 * @property string         $description описание
 * @property string         $params      параметры ресурса в JSON формате
 * @property-read LinkedUrl $linkedUrls
 * @property bool           $is_active
 * @property int            $created_at
 * @property int            $updated_at
 *
 * @package modular\core\models
 */
class PackageInit extends ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'common__v1_init';
    }


    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    /**
     * {@inheritdoc}
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
     * Возвращает массив моделей URL связанных с пакетом.
     *
     * @return \yii\db\ActiveQuery|LinkedUrl[]
     */
    public function getLinkedUrls()
    {
        return $this->hasMany(LinkedUrl::class, ['init_id' => 'id',]);
    }


    /**
     * Возвращает массив моделей записей действий абонента.
     *
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
     * Возвращает параметры конфигурации компонента лога.
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
     * Возвращает параметры инициализации модуля.
     *
     * @return array|PackageInit|PackageInit[]
     * @throws ServerErrorHttpException
     */
    public static function getParams()
    {
        if (Application::isPanel()) {
            return static::find()->all();
        }
        else {
            /** @var LinkedUrl $url */
            $url = LinkedUrl::findOne(['url' => $_SERVER['SERVER_NAME'], 'is_active' => 1,]);
            if (!empty($url)) {
                if (empty($url->packageInit)) {
                    throw new ServerErrorHttpException('Undefined resource for the `' . $_SERVER['SERVER_NAME'] . '`.');
                }
                return $url->packageInit;
            }
            else {
                throw new ServerErrorHttpException('URL `' . $_SERVER['SERVER_NAME'] . '` is not registered.');
            }
        }
    }


    /**
     * Идентификатор модуля в приложении.
     *
     * @return string
     */
    public function getModuleId()
    {
        return Application::isPanel() ? $this->module_id : $this->version;
    }


    /**
     * Массив локальных параметров модуля.
     *
     * @return array
     */
    public function getLocalParams()
    {
        return
            ArrayHelper::merge(
                (array)Json::decode($this->params),
                file_exists(
                    $this->getPath() . '/config/config-local.php'
                ) ?
                    require $this->getPath() . '/config/config-local.php' : []
            );
    }


    /**
     * Пусть на сервере к папке модуля.
     *
     * @return bool|string
     */
    public function getPath()
    {
        return
            \Yii::getAlias(
                Application::isPanel() ?
                    "@$this->module_id/" . \Yii::$app->id :
                    "@$this->module_id/" . \Yii::$app->id . "/$this->version"
            );
    }


    /**
     * Строковое название класса модуля в пространстве имён.
     *
     * @return string
     */
    public function getClass()
    {
        if (file_exists($this->getPath() . "/Module.php")) {
            return
                Application::isPanel() ?
                    "$this->module_id\\" . \Yii::$app->id . "\\Module" :
                    "$this->module_id\\" . \Yii::$app->id . "\\$this->version\Module";
        }
        return 'modular\\' . \Yii::$app->id . '\\' . ucfirst(\Yii::$app->id) . 'Module';
    }


    /**
     * Возвращает массив параметров инициализации модуля.
     *
     * @return array
     */
    public function fields()
    {
        return
            [
                'title',
                'description',
                'urls'   => function () {
                    $urls = [];
                    foreach($this->getLinkedUrls()->all() as $url) {
                        $urls[] = $url->toArray(['url', 'is_active']);
                    };
                    return $urls;
                },
                'cid'   => 'module_id',
                'class' => function () {
                    return $this->getClass();
                }
            ];
    }

}
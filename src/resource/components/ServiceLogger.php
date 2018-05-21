<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\resource\components;


use modular\common\helpers\Html;
use modular\common\modules\Module;
use modular\common\models\ServiceLog;
use yii\base\Object;

/**
 * Class ServiceLogger
 * Компонент логирования запросов провайдеров внешних сервисов.
 *
 * @property Module $module
 *
 * @package modular\resource\components
 */
class ServiceLogger extends Object
{


    /**
     * @const int LOG_STORE_TIMEOUT максимальное время хранения записей логов ( в секундах )
     */
    const LOG_STORE_TIMEOUT = 259200;


    /**
     * @const string LOG_FORMAT_XML XML формат лога провайдера
     */
    const LOG_FORMAT_XML = 'xml';


    /**
     * @const string LOG_FORMAT_JSON JSON формат лога провайдера
     */
    const LOG_FORMAT_JSON = 'json';


    /**
     * @const string LOG_FORMAT_RAW RAW формат лога провайдера
     */
    const LOG_FORMAT_RAW = 'raw';


    /**
     * @const int TYPE_REQUEST лог запроса
     */
    const TYPE_REQUEST = 0;


    /**
     * @const int TYPE_RESPONSE лог ответа
     */
    const TYPE_RESPONSE = 1;


    /**
     * @var string $providerId идентификатор проавйдера данных внешних сервисов
     */
    public $providerId = '';


    /**
     * @var string $format формат записей логов провайдера
     */
    public $format = '';


    /**
     * @var string $logPath абсолютный путь на сервере для хранения файлов логов
     */
    public $logPath = '@common/logs/providers';


    /**
     * @var string $logTimestampFormat формат отображения метки создания записи в тексте лога
     */
    public $logTimestampFormat = "php:d.m.Y / H:i:s";


    /**
     * @var ServiceLog $_model
     */
    private $_model;


    /**
     * @var string $_description
     */
    private $_description = '';


    /**
     * @var int $_timestamp
     */
    private $_timestamp = 0;


    /**
     * Возвращает временную метку записи лога.
     *
     * @return int
     */
    protected function getTimestamp()
    {
        if (empty($this->_timestamp)) {
            $this->_timestamp = time();
        }
        return $this->_timestamp;
    }


    /**
     * Возвращает путь до папки логов `год-месяц`.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function shortLogPath()
    {
        return (string)\Yii::getAlias(
            $this->logPath . \Yii::$app->formatter->asDatetime($this->getTimestamp(), "php:/Y-m")
        );
    }


    /**
     * Возвращает путь до папки логов `день`.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function fullLogPath()
    {
        return (string)\Yii::getAlias(
            $this->shortLogPath() . \Yii::$app->formatter->asDatetime($this->getTimestamp(), "php:/d")
        );
    }


    /**
     * Возвращает полный путь до файла лога записи.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function getLogFilename()
    {
        return (string)\Yii::getAlias(
            $this->fullLogPath() .
            "/" . md5($this->getModel()->id) .
            "." . $this->providerId .
            "-" . $this->module->params['bundleParams']['module_id'] .
            '-' . $this->module->params['bundleParams']['version'] .
            ".log"
        );
    }


    /**
     * Добавляет в лог данные запроса к API биллинговой системы.
     *
     * @param array  $params
     * @param string $description
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function addRequest($params = [], $description = '')
    {
        if (!empty($params)) {
            $this->_description = $description;
            $this->getModel()->created_at = $this->getTimestamp();
            $this->getModel()->filename = $this->getLogFilename();
            $this->getModel()->update(false);
            if (!file_exists($this->shortLogPath())) {
                mkdir($this->shortLogPath());
                mkdir($this->fullLogPath());
            }
            elseif (!file_exists($this->fullLogPath())) {
                mkdir($this->fullLogPath());
            }
            file_put_contents(
                $this->getLogFilename(),
                "<pre>\r\n" .
                "/**\r\n * API: $description" .
                "\r\n * Создан: " . \Yii::$app->formatter->asDatetime($this->getTimestamp(),
                    $this->logTimestampFormat) .
                "\r\n */\r\n</pre>\r\n",
                FILE_APPEND | LOCK_EX
            );
        }
    }


    /**
     * @todo реализовать формат JSON
     *
     * Добавляет в лог данные запроса/ответа для биллинговой системы.
     *
     * @param     $trace
     * @param int $type
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function addTrace($trace, $type = self::TYPE_REQUEST)
    {
        ob_start();
        switch ($type) {
            case self::TYPE_REQUEST :
                echo "<pre>\r\n// Запрос\r\n\r\n";
                break;
            case self::TYPE_RESPONSE :
                echo "<pre>\r\n// Ответ\r\n\r\n";
                break;
            default :
                echo "<pre>\r\n\r\n";
                break;
        }
        switch ($this->format) {

            case self::LOG_FORMAT_XML :
                $dom = new \DOMDocument();
                $dom->loadXML($trace);
                $dom->formatOutput = true;
                echo Html::encode($dom->saveXML());
                break;

            case self::LOG_FORMAT_JSON :
                print_r($trace);
                break;

            default :
                var_dump($trace);
                break;
        }
        echo "\r\n</pre>\r\n\r\n";
        file_put_contents(
            $this->getLogFilename(),
            ob_get_contents(),
            FILE_APPEND | LOCK_EX
        );
        ob_end_clean();
    }


    /**
     * @return \yii\base\Module|Module
     */
    protected function getModule()
    {
        return \Yii::$app->controller->module;
    }


    /**
     * @return ServiceLog
     */
    protected function getModel()
    {
        if (empty($this->_model)) {
            $this->_model = new ServiceLog();
            $this->_model->provider_id = $this->providerId;
            $this->_model->format = $this->format;
            $this->_model->bundle_id = $this->module->params['bundleParams']['id'];
            $this->_model->save(false);
        }
        return $this->_model;
    }

}
<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\models;


use modular\core\models\PackageInit;
use yii\db\ActiveRecord;

/**
 * Class ServiceLog модель записей логов запросов в биллинг.
 *
 * @property int         $id
 * @property int         $bundle_id
 * @property string      $provider_id
 * @property string      $format
 * @property string      $description
 * @property string      $filename
 * @property string      $trace
 * @property int         $created_at
 * @property PackageInit $bundle
 * @property string      $createdAt read-only форматированная дата создания записи
 *
 * @package modular\core\models
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class ServiceLog extends ActiveRecord
{


    /**
     * @var string $filenameEmptyText текст в панели администрирования при отсутствии файла лога на сервере
     */
    public $filenameEmptyText = "Файл лога не найден или был удалён";


    /**
     * @var string $_folder
     */
    private $_folder;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'common__v1_logs';
    }


    /**
     * Возвращает запись лога по указанному идентификатору.
     *
     * @param int $id
     *
     * @return static|null
     */
    public static function findById($id = 0)
    {
        return static::findOne(['id' => $id,]);
    }


    /**
     * @todo remove if explicit
     *
     * Возвращает все записи логов относительно указанных идентификаторов.
     *
     * @param array $ids
     *
     * @return static[]|null
     */
    public static function findByIds($ids = [])
    {
        return !empty($ids) ?
            static::find()->where(['in', 'id', $ids])->all()
            : null;
    }


    /**
     * Удаляет записи с указанными идентификаторами и возвращает количество удалённых элементов.
     *
     * @param array $ids
     *
     * @return int
     */
    public static function deleteSelected($ids = [])
    {
        return !empty($ids) ?
            static::deleteAll(['in', 'id', $ids]) : 0;

    }


    /**
     * Возвращает связанную модель веб-ресурса выполняющего запрос.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBundle()
    {
        return $this->hasOne(PackageInit::className(), ['id' => 'bundle_id']);
    }


    /**
     * @inheritdoc
     */
    public function viewFields()
    {
        return [
            [
                'label'  => false,
                'format' => 'raw',
                'value'  => function () {
                    if (file_exists($this->filename)) {
                        return
                            "<pre>\r\n" .
                            "/**\r\n * Файл лога: " . $this->filename .
                            "\r\n */\r\n</pre>\r\n" .
                            file_get_contents($this->filename);
                    }
                    else {
                        return $this->filenameEmptyText;
                    }
                },
            ],
        ];
    }


    /**
     * Возвращает read-only форматированную дату создания записи.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getCreatedAt()
    {
        return !empty($this->created_at) ?
            \Yii::$app->formatter->asDatetime($this->created_at, "php:H:i:s / d.m.Y") : "";
    }


    /**
     * Если директория хранения файла лога на сервере пустая.
     *
     * @return bool
     */
    protected function getIsFolderEmpty()
    {
        return ($this->getFolder() !== null && count(scandir($this->getFolder())) == 2);
    }


    /**
     * Возвращает директорию хранения файла лога на сервере.
     *
     * @return null|string
     */
    protected function getFolder()
    {
        if (empty($this->_folder)) {
            $this->_folder = !empty($this->filename) && is_readable(dirname($this->filename)) ? dirname($this->filename) : null;
        }
        return $this->_folder;
    }


    /**
     * @inheritdoc
     */
    public function delete()
    {
        // remove related log file
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
        // remove related log folder if it is empty
        if ($this->getIsFolderEmpty()) {
            rmdir($this->getFolder());
        }
        return parent::delete();
    }

}
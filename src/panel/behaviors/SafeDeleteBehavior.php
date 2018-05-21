<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\panel\behaviors;


use yii\base\Behavior;
use yii\base\ErrorException;
use yii\base\ModelEvent;
use yii\db\BaseActiveRecord;


/**
 * Class SafeDeleteBehavior поведение модели с функционалом безопасного удаления записи.
 *
 * > Note: Если существуют связанные модели необходимо прикрепить данное поведение к каждой из них.
 *
 * Безопасное удаление реализовано через изменение соответствующего свойства-триггера модели. Предполагается, что в
 * таблице базы данных есть поле с названием по умолчанию `safe_delete`. Для изменения этого названия можно указать
 * собственное в параметрах поведения
 *
 * ```php
 *      [
 *          'class' => SafeDeleteBehavior::className(),
 *          'safeDeleteAttribute' => '<table_field_name>',
 *      ]
 * ```
 *
 * Обрабатывает событие [[\yii\db\BaseActiveRecord::EVENT_BEFORE_DELETE]] и прерывает удаление, если запись ещё не в
 * корзине.
 *
 * > Warning: Если запись уже была удалена в корзину или в таблице отсутствует поле триггера безопасного уведомления,
 * то запись будет удалена обычным образом.
 *
 * @property BaseActiveRecord $owner
 *
 * @package panel\behaviors
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class SafeDeleteBehavior extends Behavior
{


    /**
     * @const int STATE_DEFAULT значение аттрибута триггера безопасного удаления если запись ещё не удалялась
     */
    const STATE_DEFAULT = 0;

    /**
     * @const int STATE_DELETED значение аттрибута триггера безопасного удаления если запись уже была удалена в корзину
     */
    const STATE_DELETED = 1;


    /**
     * @var string $safeDeleteAttribute аттрибут модели триггера безопасного удаления
     */
    public $safeDeleteAttribute = 'safe_delete';


    /**
     * @var array $uniqueFields массив с названиями полей для проверки на уникальность при восстановлении записи
     */
    public $uniqueFields = [];


    /**
     * @inheritdoc
     *
     * Прикрепляет обработку события удаления записи [[\yii\db\BaseActiveRecord::EVENT_BEFORE_DELETE]].
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'safeActions',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'safeActions',
        ];
    }


    /**
     * Производит безопасное удаление записи обновляя соответствующий триггер.
     *
     * @param ModelEvent $event
     *
     * @throws ErrorException если аттрибут безопасного удаления модели не определён
     */
    public function safeActions($event)
    {
        if (!$this->owner->hasAttribute($this->safeDeleteAttribute)) {
            throw new ErrorException('Аттрибут безопасного удаления модели не определён.');
        }

        switch ($event->name) {
            case BaseActiveRecord::EVENT_BEFORE_DELETE :
                if ($this->owner->getAttribute($this->safeDeleteAttribute) === self::STATE_DEFAULT) {
                    $this->owner->updateAttributes([$this->safeDeleteAttribute => self::STATE_DELETED,]);
                    $event->isValid = false;
                } else {
                    $this->owner->updateAttributes([$this->safeDeleteAttribute => self::STATE_DEFAULT,]);
                    $event->isValid = true;
                }
                break;

            case BaseActiveRecord::EVENT_BEFORE_UPDATE :

                $safeDeleteRequestParam = \Yii::$app->request->isGet ?
                    \Yii::$app->request->get($this->safeDeleteAttribute, null) :
                    \Yii::$app->request->post($this->safeDeleteAttribute, null);
                if ($safeDeleteRequestParam !== null && $this->owner->getAttribute($this->safeDeleteAttribute) === self::STATE_DELETED) {
                    if ($this->checkUnique($this->uniqueFields)) {
                        $this->owner->updateAttributes([$this->safeDeleteAttribute => self::STATE_DEFAULT]);
                    }
                }
                break;

            default :
                break;
        }

    }


    /**
     * Проверяет на уникальность поля восстанавливаемой записи и возвращает результат подтверждения.
     *
     * @param array $uniqueFields названия полей записи для проверки
     *
     * @return bool если поля не указаны, то всегда вернёт `true`
     */
    protected function checkUnique($uniqueFields = [])
    {
        $checkUniqueResult = true;
        if (!empty($uniqueFields)) {
            /** @var BaseActiveRecord $instance */
            $instance = \Yii::createObject([
                'class' => $this->owner->className(),
            ]);
            foreach ($uniqueFields as $uniqueField) {
                if ($this->owner->hasAttribute($uniqueField)) {
                    if ($this->isDuplicated($instance, $uniqueField)) {
                        $checkUniqueResult = false;
                        $this->owner->addError($uniqueField, 'Field `' . $uniqueField . '` has duplicated entry.');
                    }
                }
            }
        }
        return $checkUniqueResult;
    }


    /**
     * Проверяет наличие записи дублирующейся по уникальному полю и возвращает результат проверки.
     *
     * @param BaseActiveRecord $instance    модель записи
     * @param string           $uniqueField название уникального поля
     *
     * @return bool
     */
    protected function isDuplicated(BaseActiveRecord $instance, $uniqueField = '')
    {
        return (!empty($instance) && !empty($uniqueField)) ?
            $instance::find()->where([
                $uniqueField               => $this->owner->getAttribute($uniqueField),
                $this->safeDeleteAttribute => self::STATE_DEFAULT,
            ])->one() !== null :
            false;
    }

}
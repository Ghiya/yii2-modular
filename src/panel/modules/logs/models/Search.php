<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\panel\modules\logs\models;

use modular\core\models\ServiceLog;


/**
 * @todo    реализовать выбор даты в зависимости от существующих значений верменных меток записей
 *
 * Class Search модель поиска по записям логов провайдеров данных внешних сервисов
 *
 * @property array $days
 * @property array $months
 * @property array $years
 *
 * @package modular\panel\modules\logs\models
 */
class Search extends ServiceLog
{


    /**
     * @var string $day
     */
    public $day;


    /**
     * @var string $day
     */
    public $month;


    /**
     * @var string $year
     */
    public $year;


    /**
     * @var string $providerId
     */
    public $providerId;


    /**
     * @var array $_dateFilterRange
     */
    private $_dateFilterRange = [];


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['provider_id', 'string', 'skipOnEmpty' => true,],
            ['bundle_id', 'integer', 'skipOnEmpty' => true,],
            ['day', 'integer',],
            ['month', 'integer',],
            ['year', 'integer',],
            [["day", "month", "year"], "required"],
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
    public function attributeLabels()
    {
        return [
            'bundle_id'  => 'Модуль веб-ресурса',
            'created_at' => 'Дата создания',
            'day'        => 'День',
            'month'      => 'Месяц',
            'year'       => 'Год',
        ];
    }


    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        parent::afterValidate();
        if (!$this->hasErrors()) {
            $this->_dateFilterRange = [
                \Yii::$app->formatter->asTimestamp(
                    "$this->year-$this->month-$this->day 00:00:00"
                ),
                \Yii::$app->formatter->asTimestamp(
                    "$this->year-$this->month-$this->day 23:59:59"
                )
            ];
        }
    }


    /**
     * @param array $params
     *
     * @return $this|\yii\db\Query
     */
    public function search($params = [])
    {
        $query = static::find()->orderBy(['created_at' => SORT_DESC,]);
        $this->load($params);
        if ($this->validate()) {
            foreach ($params as $attribute => $value) {
                if (!empty($value)) {
                    if (!in_array($attribute, ["day", "month", "year", "providerId"])) {
                        $query->andWhere([$attribute => $value,]);
                    }
                }
            }
            $query
                ->andFilterWhere(
                    [
                        'provider_id' => $this->providerId,
                    ]
                )
                ->andFilterWhere(
                    [
                        'between',
                        'created_at',
                        $this->_dateFilterRange[ 0 ],
                        $this->_dateFilterRange[ 1 ]
                    ]
                );
        }
        return $query;
    }


    /**
     * @return array
     */
    public function getDays()
    {
        return range(1, 31, 1);
    }


    /**
     * @return array
     */
    public function getMonths()
    {
        return range(1, 12, 1);
    }


    /**
     * @return array
     */
    public function getYears()
    {
        return range(2016, 2018, 1);
    }

}
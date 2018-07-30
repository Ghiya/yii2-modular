<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\models;


use modular\core\helpers\ArrayHelper;
use yii\db\ActiveQuery;


/**
 * Class SearchTrackData
 *
 * @package modular\core\tracker\models
 */
class SearchTrackData extends TrackData
{


    /**
     * @var int
     */
    public $trackId;


    /**
     * @var string
     */
    public $cid;


    /**
     * @var string
     */
    public $from;


    /**
     * @var string
     */
    public $to;


    /**
     * @var int
     */
    public $range = 8;


    /**
     * @var bool
     */
    public $fullRange = false;


    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return
            [
                self::SCENARIO_DEFAULT => ['from', 'to', 'cid', 'trackId'],
            ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return
            ArrayHelper::merge(
                parent::attributeLabels(),
                [
                    'trackId' => 'Идентификатор'
                ]
            );
    }


    /**
     * @return array
     */
    public function fields()
    {
        return
            $this->fullRange ?
                [
                    'from' => function ($model) {
                        /** @var SearchTrackData $model */
                        return strtotime("- $model->range days");
                    },
                    'to'   => function ($model) {
                        /** @var SearchTrackData $model */
                        return time();
                    }
                ] :
                [
                    'from' => function ($model) {
                        /** @var SearchTrackData $model */
                        return $model->getCurrentFrom();
                    },
                    'to'   => function ($model) {
                        /** @var SearchTrackData $model */
                        return $model->getCurrentTo();
                    }
                ];
    }


    /**
     * @param string $id
     * @param bool   $countTracks
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getRanges($id = "", $countTracks = false)
    {
        $formatter = \Yii::$app->formatter;
        $ranges = array_pad([], $this->range, []);
        foreach (array_keys($ranges) as $index) {
            $ranges[$index] =
                $index == 0 ?
                    [
                        'from' =>
                            $formatter->asTimestamp(
                                date(
                                    "Y-m-d 00:00:00.000000"
                                )
                            ),
                        'to'   => time(),
                        'date' => 'сегодня',
                    ] :
                    [
                        'from' =>
                            $formatter->asTimestamp(
                                date(
                                    "Y-m-d 00:00:00.000000",
                                    strtotime("- " . $index . " days")
                                )
                            ),
                        'to'   =>
                            $formatter->asTimestamp(
                                date(
                                    "Y-m-d 00:00:00.000000",
                                    strtotime("- " . ($index - 1) . " days")
                                )
                            ),
                        'date' =>
                            $formatter->asDatetime(
                                strtotime("- $index days"),
                                "php:d.m"
                            )
                    ];
        }
        if ($countTracks) {
            foreach ($ranges as $index => $range) {
                $ranges[$index] =
                    ArrayHelper::merge(
                        $ranges[$index],
                        [
                            'total'   => self::listQuery($id, \Yii::$app->user->id, $range)->count(),
                            'active'  => $this->countActive($range),
                            'current' =>
                                (
                                    $formatter->asDatetime(
                                        $range['from'],
                                        "php:d.m"
                                    ) == $formatter->asDatetime(
                                        !empty($this->from) ?
                                            $this->from :
                                            date("Y-m-d 00:00:00"),
                                        "php:d.m"
                                    )
                                )
                        ]
                    );
            }
        }
        return $ranges;
    }


    /**
     * @return string
     */
    protected function getCurrentFrom()
    {
        return
            empty($this->from) ?
                \Yii::$app->formatter->asTimestamp(date("Y-m-d 00:00:00")) : $this->from;
    }


    /**
     * @return string
     */
    protected function getCurrentTo()
    {
        return
            empty($this->to) ?
                (string)time() : $this->to;
    }


    /**
     * Помечает все уведомления модуля веб-ресурса как прочтённые указанным пользователем.
     *
     * @param string $moduleId   идентификатор модуля
     * @param int    $userId     идентификатор пользователя
     * @param array  $dateFilter парамтеры фильтрации уведомлений по дате
     */
    public static function allViewedBy($moduleId = "", $userId = 0, $dateFilter = [])
    {
        /** @var static[] $tracks */
        $tracks = self::listQuery($moduleId, $userId, $dateFilter)->all();
        foreach ($tracks as $track) {
            $track->viewed($userId);
        }
    }


    /**
     * Добавляет просмотр для указанного пользователя или группы пользователей.
     *
     * @param int|array $user идентификатор пользователя или массив идентификаторов
     */
    public function viewed($user = 0)
    {
        if (!empty($user)) {
            if (is_array($user)) {
                foreach ($user as $id) {
                    $this->viewedBy = $id;
                }
            }
            else {
                $this->viewedBy = $user;
            }
        }
    }


    /**
     * Добавляет доступ для указанного пользователя или группы пользователей.
     *
     * @param int|array $user идентификатор пользователя или массив идентификаторов
     */
    public function allowed($user = 0)
    {
        if (!empty($user)) {
            if (is_array($user)) {
                foreach ($user as $id) {
                    $this->allowedFor = $id;
                }
            }
            else {
                $this->allowedFor = $user;
            }
        }
    }


    /**
     * Возвращает количество непросмотренных уведомлений модуля веб-ресурса для активного пользователя.
     *
     * @param bool $filterDate
     * @param bool $filterResource
     *
     * @return int
     */
    public function countActive($filterDate = true, $filterResource = true)
    {
        return
            self::listQuery(
                $filterResource ? $this->cid : null,
                \Yii::$app->getUser()->getId(),
                $filterDate ? is_array($filterDate) ? $filterDate : $this->toArray() : null
            )
                ->andWhere(
                    "`viewed_by` IS NULL OR `viewed_by` NOT REGEXP '-" . \Yii::$app->getUser()->getId() . "-'"
                )
                ->count();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListQuery()
    {
        return
            self::listQuery(
                $this->cid,
                \Yii::$app->user->identity->getId(),
                !empty($this->cid) ? $this->toArray() : null
            );
    }


    /**
     * @return ActiveQuery
     */
    public function getFilterQuery()
    {
        return
            self::listQuery(
                $this->cid,
                \Yii::$app->user->identity->getId(),
                $this->toArray()
            )
                ->andWhere(
                    ['id' => $this->trackId]
                );
    }

}
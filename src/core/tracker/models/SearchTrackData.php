<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\core\tracker\models;


use modular\core\helpers\ArrayHelper;


/**
 * Class SearchTrackData
 *
 * @package modular\core\tracker\models
 */
class SearchTrackData extends TrackData
{


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
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return
            [
                self::SCENARIO_DEFAULT => ['from', 'to'],
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
     * @return array
     */
    public function fields()
    {
        return
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
                            'count'  => self::listQuery($id, \Yii::$app->user->id, $range)->count(),
                            'active' =>
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
     * Возвращает количество непросмотренных уведомлений модуля веб-ресурса для указанного пользователя.
     *
     * @param int  $userId
     * @param bool $filterDate
     *
     * @return int
     */
    public function countActive($userId = 0, $filterDate = true)
    {
        return
            self::listQuery(
                $this->id,
                !empty($userId) ?
                    $userId : \Yii::$app->getUser()->getId(),
                $filterDate ? $this->toArray() : null
            )
                ->andWhere("`viewed_by` IS NULL OR `viewed_by` NOT REGEXP '-$userId-'")
                ->count();
    }

}
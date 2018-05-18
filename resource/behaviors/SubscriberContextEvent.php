<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace resource\behaviors;


use yii\base\Event;

/**
 * Class SubscriberContextEvent
 *
 * @package resource\behaviors
 */
class SubscriberContextEvent extends Event
{


    /**
     * @var string $msisdn
     */
    public $msisdn = '';

}
<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\events;

use modular\core\tracker\models\TrackData;


/**
 * Class WarningTrack
 * Событие с параметрами предупреждающего уведомления модуля веб-ресурса.
 *
 * @package modular\core\tracker\events
 */
class WarningTrack extends Track
{


    /**
     * @var int
     */
    public $priority = TrackData::PRIORITY_WARNING;

}
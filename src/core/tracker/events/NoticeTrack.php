<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\tracker\events;

use modular\core\tracker\models\TrackData;


/**
 * Class NoticeTrack
 * Событие с параметрами информационного уведомления модуля веб-ресурса.
 *
 * @package modular\core\tracker\events
 */
class NoticeTrack extends Track
{


    /**
     * @var int
     */
    public $priority = TrackData::PRIORITY_NOTICE;


}
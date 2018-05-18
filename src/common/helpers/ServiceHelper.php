<?php
/**
 * @copyright Copyright (c) 2018. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common\helpers;


use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class ServiceHelper вспомогательный класс ресурсов и административных панелей.
 *
 * @package modular\common\helpers
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class ServiceHelper extends Object
{


    /**
     * Проверяет совпадение IP пользователя для указанных адресов.
     *
     * @param array|string $allowedIps может быть строкой или массивом
     *
     * @return bool
     */
    public static function userIpCheck($allowedIps)
    {
        if (is_string($allowedIps)) {
            return self::_match($allowedIps);
        } else {
            if (ArrayHelper::isIndexed($allowedIps)) {
                foreach ($allowedIps as $ip) {
                    if (self::_match($ip)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    /**
     * Проверяет совпадение пользовательского IP с указанным.
     *
     * @param string $ip
     *
     * @return bool
     */
    private static function _match($ip = '')
    {
        return
            preg_match(
                "/([0-9\*]{1,3}\.){3}[0-9\*]{1,3}/i",
                $ip
            ) ?
                (boolean)preg_match(
                    "/" . preg_replace("/\*{1}/i", ".+", $ip) . "/i",
                    \Yii::$app->request->userIP
                ) :
                false;
    }

}
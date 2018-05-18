<?php
/**
 * Copyright (c) 2018. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common;

use Yii;
use yii\base\Component;
use yii\helpers\Html;


/**
 * Class Core сервисный компонент вспомогательных методов
 *
 * @package modular\common
 */
class Log extends Component
{


    public static function e($value, $params = null)
    {
        if (!empty($params)) {
            $title = (in_array('title',
                    $params) && !empty($params['title'])) ? "$" . $params['title'] : (is_string($params)) ? $params : "";
            $showType = (is_array($params) && in_array('showType', $params));
            $formatOutput = (is_array($params) && in_array('formatOutput', $params));
            $returnOutput = (is_array($params) && in_array('returnOutput', $params));
            $stopHere = (is_array($params) && in_array('stopHere', $params));
        }
        else {
            $title = "";
            $showType = false;
            $returnOutput = false;
            $formatOutput = false;
            $stopHere = false;
        }
        if ($returnOutput) {
            ob_start();
        }
        if ($formatOutput) {
            echo '<pre>';
        }
        if (!empty($value)) {

            // bool
            if (is_bool($value)) {
                echo ($showType) ? 'bool' : '';
                echo ($value) ? $title . ' true' : $title . ' false';
            }
            else {
                // array
                if (is_array($value)) {
                    echo print_r($value, true);
                }
                else {
                    // integer
                    if (is_integer($value)) {
                        echo ($showType) ? 'int' : '';
                        echo $title . ' ' . $value;
                    }
                    else {
                        // double
                        if (is_double($value)) {
                            echo ($showType) ? 'double' : '';
                            echo $title . ' ' . $value;
                        }
                        else {
                            // string
                            if (is_string($value)) {
                                echo ($showType) ? 'string' : '';
                                echo $title . ' ' . $value;
                            } // objects
                            else {
                                echo $title . "\r\n";
                                var_dump($value);
                            }
                        }
                    }
                }
            }
        }
        else {
            echo $title . ' value is null';
        }
        if ($formatOutput) {
            echo "</pre>";
        }
        echo "\r\n\r\n";
        if ($returnOutput) {
            $output = Html::encode(ob_get_contents());
            return $output;
        }
        if ($stopHere) {
            Yii::$app->end();
        }
    }

}

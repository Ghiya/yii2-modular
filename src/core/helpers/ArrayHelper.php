<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\core\helpers;

use yii\base\InvalidArgumentException;
use yii\helpers\UnsetArrayValue;


/**
 * Class ArrayHelper
 * @package modular\core\helpers
 */
class ArrayHelper extends \yii\helpers\ArrayHelper
{


    /**
     * Convert array values according to the defined template expression.
     * Supposed for using both parameters, in other cases do nothing.
     *
     * @param array           $array
     * @param string|callable $template expression to replace value with ( by default `{value}` )
     *
     * @return array
     */
    public static function normalizeValues(array $array = [], $template = "{value}")
    {
        if (self::isAssociative($array)) {
            foreach ($array as $key => $value) {
                if (isset($value)) {
                    $array[$key] =
                        !is_callable($template) ?
                            preg_replace("/{value}/i", $value, $template) :
                            call_user_func($template, $key, $value);
                }
            }
        }
        return $array;
    }


    /**
     * Removes matching string values from the associative array.
     * By default removes empty strings.
     *
     * @param array  $array target array
     * @param string $regex regular expression for the value to match
     *
     * @return array
     */
    public static function trimValues(array $array = [], $regex = "")
    {
        if (self::isAssociative($array)) {
            foreach ($array as $key => $value) {
                if (
                    is_string($value) &&
                    (
                        empty($regex . $value)
                        || !empty($regex)
                        && preg_match("/$regex/i", $value)
                    )
                ) {
                    $array =
                        ArrayHelper::merge(
                            $array,
                            [
                                $key => new UnsetArrayValue()
                            ]
                        );
                }
            }
        }
        else {
            foreach ($array as $index => $item) {
                $array[$index] = self::trimValues($item, $regex);
            }
        }
        return $array;
    }


    /**
     * Сливает два массива с сохранением строковых значений первого.
     *
     * @param array $target
     * @param array $source
     *
     * @return array
     */
    public static function mergeStrings(array $target = [], array $source = [])
    {
        $merged = [];
        foreach (self::merge($target, $source) as $k => $v) {
            if (is_string($v)) {
                $merged[$k] =
                    isset($source[$k]) ?
                        $target[$k] . ' ' . $source[$k] :
                        $target[$k];
            }
            elseif (is_array($v)) {
                $merged[$k] = self::mergeStrings($merged[$k], $v);
            }
            else {
                $merged[$k] = $v;
            }
        }
        return $merged;
    }


    /**
     * {@inheritdoc}
     */
    public static function map($array, $from, $to, $group = null)
    {
        return parent::map(self::isAssociative($array) ? [$array] : $array, $from, $to, $group);
    }


    /**
     * @param array  $target
     * @param array  $source
     * @param string $key
     *
     * @return array
     */
    public static function link($target = [], $source = [], $key = "")
    {
        $linked = [];
        if (!empty($target)) {
            $targetIndexed = self::index($target, $key);
            $sourceIndexed = self::index($source, $key);
            foreach ($targetIndexed as $index => $targetSlice) {
                $linked[] =
                    ArrayHelper::merge(
                        $targetSlice,
                        isset($sourceIndexed[$index]) ? $sourceIndexed[$index] : []
                    );
            }
        }
        return $linked;
    }


    /**
     * @deprecated Use [[\modular\core\helpers\ArrayHelper::renameKeys()]] instead.
     *
     * Переименовывает указанные ключи массива или набора массивов.
     * В случае ошибки вернёт пустой массив.
     *
     * @param array $array
     * @param       $from
     * @param       $to
     *
     * @return array
     */
    public static function rename(array $array = [], $from, $to)
    {
        $from = (array)$from;
        $to = (array)$to;
        if (!empty($array)) {
            $renamed = [];
            if (!empty($from) && count($from) + count($to) + count($array) == count($array) / 3) {
                if (self::isIndexed($array)) {
                    foreach (self::extract($array, $from) as $index => $originalArray) {
                        $originalValues = array_values($originalArray);
                        $renamed[$index] = array_combine($to, $originalValues);
                    }
                }
                else {
                    $originalValues = array_values(self::extractKeys($array, $from));
                    $renamed = array_combine($to, $originalValues);
                }
            }
            return $renamed;
        }
        return $array;
    }


    /**
     * Переименовывает указанные ключи массива или набора массивов.
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     */
    public static function renameKeys(array $array = [], array $keys = [])
    {
        if (self::isIndexed($array)) {
            foreach ($array as $index => $sub) {
                $array[$index] = self::renameKeys($sub, $keys);
            }
        }
        else {
            foreach ($keys as $original => $renamed) {
                if (isset($array[$original])) {
                    if (is_callable($renamed)) {
                        $array[$original] = call_user_func($renamed, $array[$original]);
                    }
                    elseif (is_array($renamed) && count($renamed) == 2) {
                        if (is_string($renamed[0]) && is_callable($renamed[1])) {
                            $array[$renamed[0]] = call_user_func($renamed[1], $array[$original]);
                            unset($array[$original]);
                        }
                    }
                    else {
                        $array[$renamed] = $array[$original];
                        unset($array[$original]);
                    }
                }
            }
        }
        return $array;
    }


    /**
     * Возвращает срез ассоциативного массива содержащий указанные ключи.
     *
     * @param array        $array
     * @param array|string $keys
     * @param string       $valueTemplate
     *
     * @return array
     */
    public static function extractKeys(array $array = [], $keys, $valueTemplate = '')
    {
        // possible string to array
        $keys = (array)$keys;
        // keys must be strings
        array_walk($keys, function ($value) {
            if (is_int($value)) {
                throw new InvalidArgumentException("Property `keys` must contain only strings.");
            }
        });
        $extracted = [];
        if (!empty($array)) {
            if (self::isAssociative($array)) {
                $extracted = array_intersect_key($array, array_combine($keys, array_pad([], count($keys), 0)));
                if (!empty($valueTemplate)) {
                    foreach ($extracted as $key => $value) {
                        $extracted[$key] = preg_replace('/\{value\}/i', $value, $valueTemplate);
                    }
                }
            }
        }
        return $extracted;
    }


    /**
     * Возвращает срез набора массивов содержащий указанные ключи.
     *
     * @param array        $arraySet
     * @param array|string $keys
     * @param string       $valueTemplate
     *
     * @return array
     */
    public static function extract($arraySet, $keys, $valueTemplate = '')
    {
        $keys = (array)$keys;
        $extracted = [];
        if (!empty($arraySet)) {
            if (self::isIndexed($arraySet)) {
                foreach ($arraySet as $array) {
                    if (self::isIndexed($array)) {
                        $extracted[] = self::extract($array, $keys, $valueTemplate);
                    }
                    else {
                        $extracted[] = self::extractKeys($array, $keys, $valueTemplate);
                    }
                }
            }
            else {
                $extracted[] = self::extractKeys($arraySet, $keys, $valueTemplate);
            }
        }
        return $extracted;
    }


    /**
     * @param array $arraySet
     * @param bool  $appendValues
     *
     * @return array
     */
    public static function collapse(array $arraySet = [], $appendValues = true)
    {
        $collapsed = [];
        if (!empty($arraySet)) {
            if (self::isIndexed($arraySet)) {
                foreach ($arraySet as $array) {
                    if (!$appendValues) {
                        $collapsed = self::merge(
                            $collapsed,
                            $array
                        );
                    }
                    else {
                        foreach ($array as $key => $value) {
                            if (isset($collapsed[$key])) {
                                if (is_numeric($value)) {
                                    $collapsed[$key] += $value;
                                }
                                elseif (is_string($value)) {
                                    $collapsed[$key] .= $value;
                                }
                            }
                            else {
                                $collapsed[$key] = $value;
                            }
                        }
                    }
                }
            }
        }
        return $collapsed;
    }

}
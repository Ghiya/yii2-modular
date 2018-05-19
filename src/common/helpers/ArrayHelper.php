<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\common\helpers;


/**
 * Class ArrayHelper
 * @package modular\common\helpers
 */
class ArrayHelper extends \yii\helpers\ArrayHelper
{


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
            if (!empty($from) && count($from) == count($to)) {
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
        $keys = (array)$keys;
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
    public static function extract(array $arraySet = [], $keys, $valueTemplate = '')
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
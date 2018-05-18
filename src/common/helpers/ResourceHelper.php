<?php
/**
 * Copyright (c) 2018. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace modular\common\helpers;


use DOMDocument;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;


/**
 * Class ResourceHelper объект вспомогательных методов системы управления ресурсами
 *
 * @package modular\common\helpers
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class ResourceHelper extends Object
{


    /**
     * @const string DEBUG_PRINT_JSON
     */
    const DEBUG_PRINT_JSON = 'json';

    /**
     * @const string DEBUG_PRINT_XML
     */
    const DEBUG_PRINT_XML = 'xml';

    /**
     * @const string DEBUG_PRINT_PLAIN
     */
    const DEBUG_PRINT_PLAIN = 'plain';


    /**
     * Возвращает индексированный или ассоциативный массив из исходного массива вида
     * ```php
     *      [
     *          [
     *              <key> => <value>,
     *              ...
     *              <key> => <value>,
     *          ],
     *          ...
     *          [
     *              <key> => <value>,
     *              ...
     *              <key> => <value>,
     *          ],
     *      ],
     * ```
     * относительно значения указанного ключа в элементе.
     * Значение по ключу в обрабатываемом массиве должно быть строковым или числовым, в остальных случаях будет
     * возвращён пустой массив. Если тип значения ключа в элементе исходного массива является строковым и не указан
     * дополнительный параметр принудительного индексирования, то массив результата будет ассоциативным, в остальных
     * случаях создаётся индексированный массив.
     *
     * например
     * ```php
     *      $array = [
     *          [
     *              'id' => 1,
     *              'value' => 'some value',
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *          [
     *              'id' => 2,
     *              'value' => ['an array value',],
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *          [
     *              'id' => 3,
     *              'value' => null,
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *          [
     *              'id' => 1,
     *              'value' => null,
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *          [
     *              'id' => 5,
     *              'value' => null,
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *      ];
     *
     *      $mappedArray = ResourceHelper::mapByKey('id', $array, );
     *      // result
     *      Array(4) [
     *          [1] => [
     *              [
     *                  'id' => 1,
     *                  'value' => 'some value',
     *                  'updated' => 1490248726,
     *                  'created' => 1490246553,
     *              ],
     *              [
     *                  'id' => 1,
     *                  'value' => null,
     *                  'updated' => 1490248726,
     *                  'created' => 1490246553,
     *              ],
     *          ],
     *          [2] => [
     *              'id' => 2,
     *              'value' => ['an array value',],
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *          [3] => [
     *              'id' => 3,
     *              'value' => null,
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *          [5] => [
     *              'id' => 5,
     *              'value' => null,
     *              'updated' => 1490248726,
     *              'created' => 1490246553,
     *          ],
     *      ];
     * ```
     *
     * @param string $key            строковый ключ элемента по которому будет произведена индексация
     *                               если не указан или некорректного типа, будет возвращён пустой массив
     * @param array  $array          индексный массив с элементами в виде ассоциативных массивов
     *                               если не указан или некорректного типа, будет возвращён пустой массив
     * @param bool   $enablePush     опционально, если false то при совпадений ключей сохраняется последний из
     *                               обработанных элементов исходного массива, иначе - создаётся индексированный массив
     * @param bool   $alwaysIndex    опционально, если возвращать индексированный массив для любого типа значения ключа
     *
     * @return array сортированный или пустой массив в случае ошибок
     */
    public static function mapByKey($key, $array, $enablePush = true, $alwaysIndex = true)
    {
        $resultMap = [];
        $resultMapKey = null;
        if (ArrayHelper::isIndexed($array)) {
            // перебираем исходный массив
            for ($index = 0; $index < count($array); $index++) {
                $currentIndexItem = $array[ $index ];
                // если элемент не пуст, является ассоциативным массивом и существуют указанный ключ со значением
                if (!empty($currentIndexItem) && !empty($currentIndexItem[ $key ]) && ArrayHelper::isAssociative($currentIndexItem)) {
                    // если это первый из обрабатываемых элементов
                    if (empty($resultMapKey)) {
                        // если первое значение ключа stirng и не указано всегда индексировать, то приводим к string и создаём ассоциативный массив
                        // в остальных случая приводим к integer и создаём индексированный массив
                        $currentIndexItemKeyCasted = (is_string($currentIndexItem[ $key ]) && !$alwaysIndex) ?
                            settype($currentIndexItem[ $key ], "string") :
                            settype($currentIndexItem[ $key ], "integer");
                    } // для всех последующих элементов
                    else {
                        // если предыдущее значение ключа индексации string устанавливаем ключ ассоциативного массива результата
                        // иначе - устанавливаем индекс индексированного массива результата
                        $currentIndexItemKeyCasted = (is_string($resultMapKey) && !$alwaysIndex) ?
                            settype($currentIndexItem[ $key ], "string") :
                            settype($currentIndexItem[ $key ], "integer");
                    }
                    // если приведение типа произошло без ошибок
                    if ($currentIndexItemKeyCasted) {
                        $resultMapKey = $currentIndexItem[ $key ];
                        // если в массиве результата элемент с текущим индексом не существует
                        // или не разрешено дописывание
                        if (empty($resultMap[ $resultMapKey ]) || !$enablePush) {
                            // создаём новый элемент из элемента исходного массива или заменяем предыдущий элемент
                            $resultMap[ $resultMapKey ] = $currentIndexItem;
                        } else {
                            // если элемент один, то есть это ассоциативный элемент исходного массива
                            // преобразуем элемент массива результата в индексный массив
                            if (ArrayHelper::isAssociative($resultMap[ $resultMapKey ])) {
                                $resultMap[ $resultMapKey ] = [$resultMap[ $resultMapKey ],];
                            }
                            // дописываем в результат элемент исходного массива
                            $resultMap[ $resultMapKey ][] = $currentIndexItem;
                        }
                    }
                }
            }
        }
        return $resultMap;
    }


    /**
     * Выводит значения в удобном для отладки виде.
     *
     * @param array $params
     * @param bool  $formatOutput
     *
     * @return string|null
     */
    public static function debugPrint($params, $formatOutput = true)
    {
        $debugValue = "";
        if (!empty($params)) {
            if ($formatOutput) {
                $debugValue = "<pre>\r\n";
            }
            if (is_array($params)) {
                $params = ArrayHelper::merge([
                    'value' => '',
                    'title' => '',
                    'type' => self::DEBUG_PRINT_PLAIN,
                ], $params);

                if (!empty($params[ 'value' ])) {

                    switch ($params[ 'type' ]) {

                        case self::DEBUG_PRINT_XML :
                            $debugValue .= (!empty($params[ 'title' ])) ? "/** " . $params[ 'title' ] . " */\r\n\r\n" : "\r\n\r\n";
                            $dom = new DOMDocument();
                            $dom->loadXML($params[ 'value' ]);
                            $dom->formatOutput = true;
                            $debugValue .= Html::encode($dom->saveXML());
                            break;

                        case self::DEBUG_PRINT_JSON :
                            $debugValue .= (!empty($params[ 'title' ])) ? "/** " . $params[ 'title' ] . " */\r\n\r\n" : "\r\n\r\n";
                            $debugValue .= Html::encode(Json::encode($params['value'],
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                            break;

                        case self::DEBUG_PRINT_PLAIN :
                            $debugValue .= (!empty($params[ 'title' ])) ? "/** " . $params[ 'title' ] . " */\r\n\r\n" : "\r\n\r\n";
                            $debugValue .= Html::encode($params[ 'value' ]);
                            break;

                        default :
                            break;
                    }
                }

            } elseif (is_string($params)) {
                $debugValue .= "/** */\r\n\r\n";
                $debugValue .= Html::encode($params);
            } else {
                ob_start();
                var_dump($params);
                $debugValue .= ob_get_clean();
            }
            if ($formatOutput) {
                $debugValue .= "</pre>\r\n";
            }
        }
        if ($formatOutput) {
            echo $debugValue;
            return null;
        } else {
            return $debugValue;
        }
    }

}
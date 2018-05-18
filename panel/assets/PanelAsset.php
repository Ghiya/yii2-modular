<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace panel\assets;


use yii\web\AssetBundle;
use yii\web\View;


/**
 * Class PanelAsset JS/CSS панели администрирования системы.
 *
 * @package panel\assets
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class PanelAsset extends AssetBundle
{


    public $basePath = null;


    public $baseUrl = null;


    public $sourcePath = "@panel/assets/src";


    public $css = YII_DEBUG ?
        [
            'extra/font-awesome/css/font-awesome.min.css',
            'extra/icheck/skins/flat/red.css',
            'extra/icheck/skins/flat/grey.css',
            'extra/jRange/jquery.range.css',
            'extra/jquery-select/css/nice-select.css',
            'css/main.css',
            // panel css always goes last to prevent css conflicts
            'css/panel.css',
            'css/panel.portrait.css',
            'css/panel.landscape.css',
        ] :
        [
            'extra/font-awesome/css/font-awesome.min.css',
            'extra/icheck/skins/flat/red.css',
            'extra/icheck/skins/flat/grey.css',
            'css/panel.min.compiled.css',
        ];


    public $cssOptions = [
        'rel'      => 'stylesheet',
        'type'     => 'text/css',
        'position' => View::POS_HEAD,
    ];


    public $js = YII_DEBUG ?
        [
            'extra/icheck/icheck.js',
            'extra/jRange/jquery.range.js',
            'extra/jquery-select/js/jquery.nice-select.js',
            // panel css always goes last to prevent js conflicts
            'js/panel.js',
            'js/panel.required.js',
            'js/panel.included.js',
        ] :
        [
            'extra/icheck/icheck.min.js',
            'extra/jRange/jquery.range-min.js',
            'extra/jquery-select/js/jquery.nice-select.min.js',
            'js/panel.min.obf.js',
        ];


    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];


    public $publishOptions = [
        'forceCopy' => true,
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}

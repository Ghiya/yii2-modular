<?php

namespace panel\assets;

use yii\web\AssetBundle;


/**
 * Class UIAsset JS/CSS бэкенд приложения системы управления веб-ресурсами
 *
 * @package panel\assets
 * @author  Mikadze Ghiya <ghiya@mikadze.me>
 */
class UIAsset extends AssetBundle
{

    public $basePath   = '@webroot';


    public $baseUrl    = '@web';


    public $css        = [
        '//fonts.googleapis.com/css?family=Lobster&subset=latin,cyrillic',
        '//fonts.googleapis.com/css?family=Open+Sans:400,600&subset=latin,cyrillic',
        '//fonts.googleapis.com/css?family=PT+Mono&subset=latin,cyrillic',
        'plugins/font-awesome/css/font-awesome.min.css',
        'ui/css/engine.css',
    ];


    public $cssOptions = [
        'type' => 'text/css',
    ];


    public $js         = [
        'ui/js/engine.js',
    ];


    public $jsOptions  = [
        'type' => 'text/javascript',
        'position' => 1,
    ];


    public $depends    = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}

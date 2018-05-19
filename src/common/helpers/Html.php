<?php
/**
 * Copyright (c) 2018 Ghiya Mikadze <ghiya@mikadze.me>
 */


namespace modular\common\helpers;


/**
 * Class Html
 * @package modular\common\helpers
 */
class Html extends \yii\helpers\Html
{


    /**
     * @param string $text
     *
     * @return string
     */
    public static function progressButton($text = "Processing...")
    {
        return
            Html::tag(
                'div',
                Html::tag(
                    'div',
                    Html::tag(
                        'div',
                        Html::tag(
                            'p',
                            $text,
                            [
                                'class' => 'm-0 text-uppercase'
                            ]
                        ) . "\r\n",
                        [
                            'role'          => 'progressbar',
                            "aria-valuenow" => "100",
                            "aria-valuemin" => "0",
                            "aria-valuemax" => "100",
                            "style"         => "width: 100%",
                            'class'         => 'progress-bar bg-info h-100 progress-bar-striped progress-bar-animated'
                        ]
                    ) . "\r\n",
                    [
                        'class' => 'progress'
                    ]
                ) . "\r\n",
                [
                    'class' => 'w-100 d-none btn-progress'
                ]);
    }


    /**
     * @param string $content
     * @param array  $options
     * @param string $progressText
     *
     * @return string
     */
    public static function buttonWithProgress($content = '', $options = [], $progressText = "")
    {
        return
            self::button(
                $content,
                $options
            )
            . "\r\n"
            . self::progressButton($progressText);
    }


    /**
     * @param string $content
     * @param array  $options
     * @param string $progressText
     *
     * @return string
     */
    public static function submitButtonWithProgress($content = '', $options = [], $progressText = "")
    {
        return
            self::submitButton(
                $content,
                $options
            )
            . "\r\n"
            . self::progressButton($progressText);
    }

}
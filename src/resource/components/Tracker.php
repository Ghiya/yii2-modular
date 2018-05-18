<?php
/**
 * @copyright Copyright (c) 2017. Ghiya Mikadze <ghiya@mikadze.me>
 */

namespace resource\components;


use common\Dispatcher;
use common\models\ModuleInit;
use resource\modules\_default\Module;
use panel\modules\tracks\models\Track;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\mail\MailerInterface;
use yii\swiftmailer\Mailer;


/**
 * Class Tracker системный компонент трекера уведомлений веб-ресурсов системы управления веб-ресурсами
 *
 * @property ModuleInit|null $relatedBundle  read-only активный пакет системы
 * @property Mailer          $mailer         read-only компонент отправки электронной почты
 * @property \SplQueue       $noticeQueue    read-only объект очереди уведомлений
 * @property array           $noticeDefaults read-only общие параметры уведомления и параметры по-умолчанию
 * @property string          $moduleId       read-only идентификатор модуля
 * @property string          $moduleVersion  read-only версия модуля
 * @property string          $moduleTitle    read-only название модуля
 *
 * @package resource\components
 * @author  Ghiya Mikadze <ghiya@mikadze.me>
 */
class Tracker extends Component
{


    /**
     * @var string $notifyParamMessage ключ параметра триггера отправки уведомлений через СМС и параметра отправителя в
     *      массиве [[Tracker::notifyParams['sender']]]
     */
    public $notifyParamMessage = 'message';


    /**
     * @var string $notifyParamEmail ключ параметра триггера отправки уведомлений по электронной почте и параметра
     *      отправителя в массиве [[Tracker::notifyParams['sender']]]
     */
    public $notifyParamEmail = 'email';


    /**
     * @var array $notifyParams массив параметров обработки уведомления
     */
    public $notifyParams = [
        'sender'    => [],
        'notify'    => [],
        'observers' => [],
    ];


    /**
     * @var \SplPriorityQueue $_noticeQueue
     */
    private $_noticeQueue;


    /**
     * @var Module $_module
     */
    private $_module;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_module = \Yii::$app->controller->module;
    }


    /**
     * Возвращает read-only свойство объекта очереди уведомлений.
     *
     * @return \SplQueue
     */
    public function getNoticeQueue()
    {
        if (empty($this->_noticeQueue)) {
            $this->_noticeQueue = new \SplQueue();
        }
        return $this->_noticeQueue;
    }


    /**
     * Возвращает компонент отправки сообщений электронной почты.
     *
     * @return Object|MailerInterface
     */
    public function getMailer()
    {
        return \Yii::createObject([
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'htmlLayout'       => '@resource/mail/layouts/html',
            'textLayout'       => '@resource/mail/layouts/text',
        ]);
    }


    /**
     * Возвращает read-only идентификатор модуля.
     *
     * @return string
     */
    public function getModuleId()
    {
        return
            !empty($this->_module->params['bundleParams']) ?
                $this->_module->params['bundleParams']['module_id'] :
                \Yii::$app->id;
    }


    /**
     * Возвращает read-only версию модуля.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return
            !empty($this->_module->params['bundleParams']) ?
                $this->_module->params['bundleParams']['version'] :
                \Yii::$app->version;
    }


    /**
     * Возвращает read-only название модуля.
     *
     * @return string
     */
    public function getModuleTitle()
    {
        return $this->_module->title;
    }


    /**
     * Возвращает read-only общие параметры уведомления и параметры по-умолчанию.
     *
     * @return array
     */
    public function getNoticeDefaults()
    {
        return [
            'session_id'    => (!empty(\Yii::$app->session)) ? \Yii::$app->session->id : null,
            'module_id'     => $this->moduleId,
            'controller_id' => !empty(\Yii::$app->controller) ? \Yii::$app->controller->id : '',
            'action_id'     => !empty(\Yii::$app->controller->action) ? \Yii::$app->controller->action->id : '',
            'version'       => $this->moduleVersion,
            'priority'      => Track::PRIORITY_WARNING,
            'request_post'  => (!empty(\Yii::$app->request->post())) ?
                http_build_query(\Yii::$app->request->post(), '', "<br/>") :
                'не определён',
            'request_get'   => (!empty(\Yii::$app->request->get())) ?
                http_build_query(\Yii::$app->request->get(), '', "<br/>") :
                'не определён',
            'user_ip'       => \Yii::$app->request->userIP,
            'user_agent'    => \Yii::$app->request->userAgent,
        ];
    }


    /**
     * Метод обрабатывает данные уведомления и добавляет его в очередь.
     *
     * @param array $notice     массив данных обрабатываемого уведомления
     * @param array $notify     массив параметров отправки
     * @param array $observers  массив данных получателей
     * @param bool  $saveToDb   сохранять уведомление в БД
     * @param array $allowedFor доступ по идентификатору пользователя
     */
    public function handle($notice = [], $notify = [], $observers = [], $saveToDb = true, $allowedFor = null)
    {
        // создаём модель уведомления и добавляем её в очередь
        if (!empty($notice)) {
            $trackModel = new Track();
            $trackModel->trackerParams = $this->notifyParams;
            foreach (ArrayHelper::merge($this->noticeDefaults, $notice) as $attribute => $value) {
                $trackModel->setAttribute($attribute, $value);
            }
            if (!empty($notify)) {
                $trackModel->trackerParams['notify'] = $notify;
            } elseif ($notify === false) {
                $trackModel->trackerParams['notify'] = [];
            }
            if (!empty($observers)) {
                $trackModel->trackerParams['observers'] = $observers;
            } elseif ($observers === false) {
                $trackModel->trackerParams['observers'] = [];
            }
            if (!empty($allowedFor)) {
                $trackModel->allowed($allowedFor);
            }
            $trackModel->shouldBeSaved = $saveToDb;
            $this->noticeQueue->enqueue($trackModel);
            // пишем в лог
            if ($trackModel->priority == Track::PRIORITY_WARNING) {
                \Yii::warning($notice['message'], __METHOD__);
            } else {
                \Yii::trace($notice['message'], __METHOD__);
            }
        }
    }


    /**
     * Используя параметры отправки специфичные для каждого уведомления, отправляет их из очереди всем указанным
     * получателям. Если очередь пуста, то не отправляет ничего.
     */
    public function sendNotices()
    {
        while (!$this->noticeQueue->isEmpty()) {
            /** @var Track $trackModel */
            $trackModel = $this->noticeQueue->dequeue();
            if ($trackModel->shouldBeSaved) {
                $trackModel->save(false);
                $subject = "[ Ticket: $trackModel->id ] $this->moduleTitle : $trackModel->decodedPriority";
            } else {
                $subject = "$this->moduleTitle : $trackModel->decodedPriority";
            }
            // отправляем по почте
            if (!empty($trackModel->mailTo) && $trackModel->hasNotifyParam($this->notifyParamEmail) && $this->mailer !== null && !empty($this->notifyParams['sender'][$this->notifyParamEmail]))
            {
                \Yii::trace('[ ' . $trackModel->id . ' ] отправка уведомления по электронной почте адресатам : ' . Json::encode($trackModel->mailTo),
                    __METHOD__);
                $emails = [];
                // формируем почтовые уведомления
                foreach ($trackModel->mailTo as $mailTo)
                {
                    $emails[] = $this->mailer
                        ->compose(
                            $this->decodePriorityMailViewPath($trackModel->priority),
                            [
                                'resource' => $this->moduleTitle,
                                'subject'  => $subject,
                                'notice'   => $trackModel->messageParams(),
                            ]
                        )
                        ->setFrom($this->notifyParams['sender'][$this->notifyParamEmail])
                        ->setTo($mailTo)
                        ->setSubject($subject);
                }
                // отправляем уведомление на все адреса разработчиков
                $this->mailer->sendMultiple($emails);
            }
            // отправляем через СМС
            if (!empty($trackModel->messageTo) && $trackModel->hasNotifyParam($this->notifyParamMessage) && !empty($trackModel->trackerParams['sender'][$this->notifyParamMessage])) {
                \Yii::trace('[ ' . $trackModel->id . ' ] отправка уведомления через СМС адресатам : ' . Json::encode($trackModel->messageTo),
                    __METHOD__);
                //Dispatcher::smsc()->useLog = false;
                Dispatcher::smsc()->msgFrom = $trackModel->trackerParams['sender'][$this->notifyParamMessage];
                // отправляем уведомление на все телефоны разработчиков
                foreach ($trackModel->messageTo as $msgTo) {
                    Dispatcher::smsc()
                        ->sendMessage(
                            [
                                'to'   => $msgTo,
                                'text' => "[ Ticket: $trackModel->id ] $this->moduleTitle : $trackModel->decodedPriority",
                            ]
                        );
                }
            }
        }
    }


    /**
     * @param int $priority
     *
     * @return string
     */
    protected function decodePriorityMailViewPath($priority)
    {
        switch ($priority) {
            case Track::PRIORITY_WARNING :
                return '@resource/mail/tracker/warning-html';
                break;

            case Track::PRIORITY_NOTICE :
                return '@resource/mail/tracker/notice-html';
                break;

            default :
                return '@resource/mail/tracker/notice-html';
                break;
        }
    }

}
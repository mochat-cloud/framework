<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Framework\WeWork;

use EasyWeChat\Kernel\Exceptions\InvalidConfigException;
use EasyWeChat\Work\Server\Guard;
use Hyperf\Di\Annotation\AnnotationCollector;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Annotation\WeChatEventHandler;
use MoChat\Framework\WeWork\EventHandler\AbstractEventHandler;

class Callback extends AbstractAction
{
    /**
     * @var WeWork
     */
    protected $client;

    /**
     * @var array
     */
    private $msgHandlers;

    /**
     * @var array
     */
    private $normalHandlers;

    /**
     * @var array
     */
    private $eventHandlers;

    /**
     * @var Guard
     */
    private $wxServer;

    public function __construct()
    {
        $this->client = make(WeWork::class);

        $this->setWxServer();
        $this->setMsgHandlers();
        $this->registerMsgHandler();
    }

    public function handle()
    {
        try {
            $response = $this->wxServer->serve();
            return $response->getContent();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getMsgHandlers(): array
    {
        return $this->msgHandlers;
    }

    public function getWxServer(): Guard
    {
        return $this->client->app()->server;
    }

    public function setWxServer(array $config = []): void
    {
        $this->wxServer = $this->client->app($config)->server;
    }

    protected function registerMsgHandler(): void
    {
        if (empty($this->normalHandlers) && empty($this->eventHandlers)) {
            return;
        }

        try {
            $message                                                      = $this->wxServer->getMessage();
            $wxEventPathArr                                               = [];
            isset($message['MsgType']) && $wxEventPathArr['MsgType']      = $message['MsgType'];
            isset($message['Event']) && $wxEventPathArr['Event']          = $message['Event'];
            isset($message['ChangeType']) && $wxEventPathArr['EventType'] = $message['ChangeType'];
            isset($message['EventKey']) && $wxEventPathArr['EventType']   = $message['EventKey'];

            $wxEventPathStr = implode('/', $wxEventPathArr);
            if (isset($this->eventHandlers[$wxEventPathStr])) {
                /** @var AbstractEventHandler $eventHandler */
                $eventHandler = make($this->eventHandlers[$wxEventPathStr]);
                $callMsg      = $eventHandler->handle($message);
                $this->wxServer->push(function () use ($callMsg) {
                    return $callMsg;
                });
                return;
            }

            /** @var AbstractEventHandler $normalHandler */
            foreach ($this->normalHandlers as $normalHandler) {
                $type = method_exists($normalHandler, 'handlerType') ? $normalHandler::handlerType() : '*';
                $this->wxServer->push($normalHandler, $type);
            }
        } catch (\RuntimeException $e) {
            throw new InvalidConfigException($e->getMessage());
        }
    }

    protected function setMsgHandlers(): void
    {
        $handlers = AnnotationCollector::getClassesByAnnotation(WeChatEventHandler::class);
        if (empty($handlers)) {
            return;
        }
        $this->msgHandlers = $handlers;

        $normalHandlers      = [];
        $this->eventHandlers = [];
        /** @var WeChatEventHandler $ann */
        foreach ($handlers as $handler => $ann) {
            if ($ann->eventPath) {
                $this->eventHandlers[$ann->eventPath] = $handler;
            } else {
                $normalHandlers[$handler] = $ann->sort;
            }
        }
        asort($normalHandlers);
        $this->normalHandlers = array_keys($normalHandlers);
    }
}

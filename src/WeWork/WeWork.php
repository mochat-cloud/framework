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

use EasyWeChat\Work\Application;
use EasyWeChat\OpenWork\Application as OpenWorkApplication;
use Hyperf\Contract\ConfigInterface;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Provider\WeWork\AbstractProvider;

/**
 * @method Application app(array $config = []) 获取wework.app
 * @method OpenWorkApplication openApp(array $config = []) 获取openWework.app
 * @method array getWxConfig() 获取 wx.config
 */
class WeWork
{
    /**
     * @var array wework配置
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('framework.wework');
    }

    public function __call($name, $arguments)
    {
        $weWork = $this->provider();

        if (method_exists($weWork, $name)) {
            return call_user_func_array([$weWork, $name], $arguments);
        }

        throw new CommonException(ErrorCode::SERVER_ERROR, 'WeWork::Method not defined. method:' . $name);
    }

    /**
     * @param null|string $name ...
     * @return AbstractProvider ...
     */
    public function provider(?string $name = null): AbstractProvider
    {
        $name || $name = $this->defaultProvider();
        if (empty($this->config['providers'][$name])) {
            throw new CommonException(ErrorCode::SERVER_ERROR, "can not find weWork.provider of {$name}");
        }

        $config = $this->config['providers'][$name];
        return make(
            $config['name'],
            [
                'config' => $this->config,
                'name'   => $name,
            ]
        );
    }

    public function defaultProvider(): string
    {
        if (empty($this->config['default']['provider'])) {
            throw new CommonException(ErrorCode::SERVER_ERROR, 'can not find weWork.default provider');
        }
        return $this->config['default']['provider'];
    }
}

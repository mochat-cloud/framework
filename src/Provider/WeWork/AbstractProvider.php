<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Framework\Provider\WeWork;

use EasyWeChat\Factory;
use EasyWeChat\Work\Application;
use EasyWeChat\OpenWork\Application as OpenWorkApplication;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array 微信配置
     */
    protected $wxConfig;

    /**
     * @var mixed
     */
    protected $service;

    /**
     * @var string ...
     */
    protected $name;

    public function __construct(array $config, string $name, RequestInterface $request)
    {
        ## init.wxConfig
        $this->wxConfig = $config['config'];

        $this->name    = $name;
        $this->service = make($config['providers'][$name]['service']);

        $this->request = $request;

        ## loadConfig.wxConfig
        [$toUserName, $agentIds] = $this->callbackParams();
        isDiRequestInit() && $this->setWxConfig($this->config($toUserName, $agentIds));
    }

    /**
     * @param array $wxConfig 微信配置
     * @return Application wework.app
     */
    public function app($wxConfig = []): Application
    {
        empty($wxConfig) || $this->setWxConfig($wxConfig);
        $app = Factory::work($this->wxConfig);

        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (strpos($method, 'appRebind') === false) {
                continue;
            }
            $app = $this->{$method}($app);
        }

        return $app;
    }

    /**
     * @param array $wxConfig 微信配置
     * @return OpenWorkApplication
     */
    public function openApp($wxConfig = []): OpenWorkApplication
    {
        empty($wxConfig) || $this->setWxConfig($wxConfig);
        $app = Factory::openWork($this->wxConfig);

        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (strpos($method, 'appRebind') === false) {
                continue;
            }
            $app = $this->{$method}($app);
        }

        return $app;
    }

    /**
     * @return array wx.config
     */
    public function getWxConfig(): array
    {
        return $this->wxConfig;
    }

    /**
     * @return string ...
     */
    public function getName(): string
    {
        return $this->name;
    }

    protected function callbackParams(): array
    {
        if (isDiRequestInit()) {
            $agentId    = $this->request->input('AgentID');
            $agentIds   = is_string($agentId) ? [$agentId] : $agentId;
            $toUserName = $this->request->post('ToUserName') ?: $this->request->query('ToUserName');
            return [$toUserName, $agentIds];
        }
        return [null, null];
    }

    protected function appRebindServerRequest(Application $app): Application
    {
        if (! isDiRequestInit()) {
            return $app;
        }

        $get         = $this->request->getQueryParams();
        $post        = $this->request->getParsedBody();
        $cookie      = $this->request->getCookieParams();
        $uploadFiles = $this->request->getUploadedFiles() ?? [];
        $server      = $this->request->getServerParams();
        $xml         = $this->request->getBody()->getContents();
        $files       = [];
        /** @var \Hyperf\HttpMessage\Upload\UploadedFile $v */
        foreach ($uploadFiles as $k => $v) {
            $files[$k] = $v->toArray();
        }

        $request          = new Request($get, $post, [], $cookie, $files, $server, $xml);
        $request->headers = new HeaderBag($this->request->getHeaders());
        $app->rebind('request', $request);

        return $app;
    }

    protected function appRebindClientRequest(Application $app): Application
    {
        $handler = new CoroutineHandler();
        // 设置 HttpClient，部分接口直接使用了 http_client。
        $httpConfig            = $app['config']->get('http', []);
        $httpConfig['handler'] = $stack = HandlerStack::create($handler);
        $app->rebind('http_client', new Client($httpConfig));

        // 部分接口在请求数据时，会根据 guzzle_handler 重置 Handler
        $app['guzzle_handler'] = $handler;

        // oauth
        $app->oauth->setGuzzleOptions([
            'http_errors' => false,
            'handler'     => $stack,
        ]);

        return $app;
    }

    protected function appRebindCache(Application $app): Application
    {
        $app['cache'] = ApplicationContext::getContainer()->get(CacheInterface::class);
        return $app;
    }

    /**
     * @param array $wxConfig wx.config
     */
    protected function setWxConfig(array $wxConfig): void
    {
        $this->wxConfig = array_merge($this->wxConfig, $wxConfig);
    }

    /**
     * @return array app配置
     */
    abstract protected function config(?string $wxCorpId = null, ?array $agentId = null): array;
}

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

use MoChat\Framework\Contract\WeWork\AgentConfigurable;

class AgentProvider extends AbstractProvider
{
    /**
     * @var AgentConfigurable
     */
    protected $service;

    /**
     * @return array app配置
     */
    protected function config(?string $wxCorpId = null, ?array $agentId = null): array
    {
        return $this->service->agentConfig($wxCorpId, $agentId);
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Framework\Service;

abstract class AbstractService
{
    /**
     * @var \Hyperf\Database\Model\Model
     */
    protected $model;

    public function __construct()
    {
        $modelClass  = str_replace(['\Service', 'Service'], ['\Model', ''], get_class($this));
        $this->model = make($modelClass);
    }
}

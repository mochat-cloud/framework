<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Framework\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;

/**
 * @Aspect
 */
class MySqlGrammarAspect extends AbstractAspect
{
    public $classes = [
        'Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing',
    ];

    public $annotations = [
    ];

    /**
     * 兼容mysql8.
     * @return string ...
     */
    public function process(): string
    {
        return 'select `column_key` as `column_key`, `column_name` as `column_name`, `data_type` as `data_type`, `column_comment` as `column_comment`, `extra` as `extra`, `column_type` as `column_type` from information_schema.columns where `table_schema` = ? and `table_name` = ? order by ORDINAL_POSITION';
    }
}

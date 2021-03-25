<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Framework\Database;

use Hyperf\Database\Schema\Grammars\MySqlGrammar as BaseMySqlGrammar;

class MySqlGrammar extends BaseMySqlGrammar
{
    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(): string
    {
        return 'select `column_key` as `column_key`, `column_name` as `column_name`, `data_type` as `data_type`, `column_comment` as `column_comment`, `extra` as `extra`, `column_type` as `column_type` from information_schema.columns where `table_schema` = ? and `table_name` = ? order by ORDINAL_POSITION';
    }
}

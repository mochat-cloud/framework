<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
if (! function_exists('readFileName')) {
    /**
     * 取出某目录下所有php文件的文件名.
     * @param string $path 文件夹目录
     * @return array 文件名
     */
    function readFileName(string $path): array
    {
        $data = [];
        if (! is_dir($path)) {
            return $data;
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..', '.DS_Store'])) {
                continue;
            }
            $data[] = preg_replace('/(\w+)\.php/', '$1', $file);
        }
        return $data;
    }
}

if (! function_exists('responseDataFormat')) {
    function responseDataFormat($code, string $message = '', array $data = []): array
    {
        return [
            'code' => $code,
            'msg'  => $message,
            'data' => $data,
        ];
    }
}

if (! function_exists('isDiRequestInit')) {
    function isDiRequestInit(): bool
    {
        try {
            \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class)->input('test');
            $res = true;
        } catch (\TypeError $e) {
            $res = false;
        }
        return $res;
    }
}

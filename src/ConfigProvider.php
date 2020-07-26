<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfSanctum;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'database',
                    'description' => 'database file.',
                    'source' => __DIR__ . '/../publish/database.php',
                    'destination' => BASE_PATH . '/migrations/' . date('Y_m_d_') . mt_rand(1, 99999) . '_sanctum.php',
                ],
            ],
        ];
    }
}

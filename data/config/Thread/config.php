<?php

return [
    'channel' => [
        'models'   => [],
        'plugins'  => [],
        'channels' => [
            'master' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => 2,
                    'endpoint'  => 'tcp://%localhost%:2080'
                ]
            ],
            'slave' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => 1,
                    'endpoint'  => 'tcp://%localhost%:2081'
                ]
            ],
            'console' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => 1,
                    'endpoint'  => 'tcp://%localhost%:2061'
                ]
            ]
        ]
    ],
    'command' => [
        'models'   => [],
        'plugins'  => [],
        'commands' => []
    ],
    'core' => [
        'project' => [
            'main.alias' => 'Main',
            'main.name'  => 'Main',
        ],
        'tolerance' => [
            'parent.keepalive' => 15.0,
            'child.keepalive'  => 15.0
        ]
    ],
    'error' => [
        'handlers' => [],
        'plugins'  => [],
        'manager'  => [
            'params' => [
                'timeout'         => 4.0,
                'retriesLimit'    => 10,
                'retriesInterval' => 2.0
            ],
            'handlers' => [],
            'plugins'  => []
        ],
        'supervisor' => [
            'params' => [
                'timeout'         => 4.0,
                'retriesLimit'    => 10,
                'retriesInterval' => 2.0
            ],
            'handlers' => [],
            'plugins'  => []
        ]
    ],
    'filesystem' => [
        'cloud' => []
    ],
    'log' => [
        'messagePattern' => "[%datetime% %level_name%.%channel%]%message%\n\n",
        'datePattern'    => "Y-m-d H:i:s",
        'filePattern'    => "%datapath%/log/%level%/kraken.%date%.log",
        'fileLocking'    => false,
        'filePermission' => 0755
    ],
    'loop' => [
        'model' => 'Kraken\Loop\Model\SelectLoop'
    ],
    'runtime' => [
        'manager' => [
            'process' => [
                'class'  => 'Kraken\Runtime\Container\Manager\ProcessManagerNull',
                'config' => []
            ],
            'thread' => [
                'class'  => 'Kraken\Runtime\Container\Manager\ThreadManagerBase',
                'config' => []
            ]
        ]
    ]
];

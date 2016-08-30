<?php

return [
    'channel' => [
        'models'   => [],
        'plugins'  => [],
        'channels' => [
            'master' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => 1,
                    'endpoint'  => 'tcp://%host.main%:2060'
                ]
            ],
            'slave' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => 2,
                    'endpoint'  => 'tcp://%host.main%:2061'
                ]
            ]
        ]
    ],
    'command' => [
        'models'   => [],
        'plugins'  => [],
        'commands' => []
    ],
    'config' => [
        'mode' => 'merge', // replace||merge||isolate
        'dirs' => []
    ],
    'core' => [
        'project' => [
            'main.alias' => 'A',
            'main.name'  => 'Common',
        ],
        'cli' => [
            'title' => 'php'
        ],
        'ini' => [
            'memory_limit' => '128M'
        ],
        'tolerance' => [
            'parent.keepalive' => 0.0,
            'child.keepalive'  => 0.0
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
        'processManager' => [
            'supervisorName' => null
        ],
        'threadManager' => [
            'supervisorName' => null
        ]
    ]
];

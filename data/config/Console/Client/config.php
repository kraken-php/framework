<?php

return [
    'channel' => [
        'models'   => [],
        'plugins'  => [],
        'channels' => [
            'console' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => 2,
                    'endpoint'  => 'tcp://%localhost%:2060'
                ]
            ]
        ]
    ],
    'command' => [
        'models'   => [],
        'plugins'  => []
    ],
    'core' => [
        'project' => [
            'main.alias' => 'Main',
            'main.name'  => 'Main',
        ]
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
    ]
];

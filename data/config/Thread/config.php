<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * Default configuration for containers extending Runtime\Container\ThreadContainer.
 *
 * For more information visit: http://kraken-php.com/docs
 * ---------------------------------------------------------------------------------------------------------------------
 */
return [
    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Additional configuration file to load.
     * -----------------------------------------------------------------------------------------------------------------
     * This variable should contain array of { resource, mode } strucutres. Resource-key should be valid path of
     * additional configuration file, and mode-key should be one of "merge" or "replace". The first option performs full
     * recursive merge of given configuration, replace performs non-recursive variation.
     *
     * IMPORTANT: This set of options cannot be parametrized by other than default variables.
     * EXAMPLE:   [[ "resource" => "%datapath%/customDir/customFile", "mode" => "merge" ]]
     * -----------------------------------------------------------------------------------------------------------------
     */
    'imports' => [],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Additional variables to use throughout configuration file.
     * -----------------------------------------------------------------------------------------------------------------
     * This variable should contain associative array of key=>value variables. All variables declared here can be used
     * in other parts of configuration by using notation %variableName%.
     *
     * IMPORTANT: There are few default variables provided that does not need to be declared here, but can be used.
     * - runtime   : corresponds to Runtime unit type,
     * - parent    : contains alias of current container's parent,
     * - alias     : equals to alias of current container,
     * - name      : equals to name of current container,
     * - basepath  : contains absolute path to project root,
     * - datapath  : contains absolute path to project data directory.
     * -----------------------------------------------------------------------------------------------------------------
     */
    'vars' => [],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Context variables to pass to this container's children.
     * -----------------------------------------------------------------------------------------------------------------
     * This variable should contain associative array of key=>value variables. All variables declared here will be able
     * to be used in this container's children by using %inherited.variableName% notation.
     * -----------------------------------------------------------------------------------------------------------------
     */
    'context' => [
        'master.endpoint' => '%func.genEndpoint%'
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Project configuration.
     * -----------------------------------------------------------------------------------------------------------------
     */
    'project' => [
        'config' => [
            'main.alias' => '%env.project_root%',
            'main.name'  => '%env.project_name%',
        ],
        'tolerance' => [
            'parent.keepalive' => 15.0,
            'child.keepalive'  => 15.0
        ]
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Channel service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Provider\ChannelProvider
     * - Framework\Provider\Runtime\ChannelProvider
     * - Framework\Provider\Runtime\ChannelConsoleProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'channel' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of additional Channel model definitions to load into service factory method.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of strings. Each string should be valid className for custom model
         * implementing Channel\ChannelModelInterface .
         * -------------------------------------------------------------------------------------------------------------
         */
        'models'   => [],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of additional Channel plugins to plug into service factory.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of string. Each string should be valid className for plugin with custom
         * logic implementing Util\Factory\FactoryPluginInterface .
         * -------------------------------------------------------------------------------------------------------------
         */
        'plugins'  => [],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of all Channel buses to register & start.
         * -------------------------------------------------------------------------------------------------------------
         * There HAS to be at least master, slave and console buses for internal communication. Custom communication
         * should declare additional buses.
         * -------------------------------------------------------------------------------------------------------------
         */
        'channels' => [
            'master' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => '%channel.connector%',
                    'endpoint'  => '%inherited.master.endpoint%'
                ]
            ],
            'slave' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => '%channel.binder%',
                    'endpoint'  => '%func.genEndpoint%'
                ]
            ],
            'console' => [
                'class'  => 'Kraken\Channel\Model\Socket\Socket',
                'config' => [
                    'type'      => '%channel.binder%',
                    'endpoint'  => 'tcp://%localhost%:2061'
                ]
            ]
        ]
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Command service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Provider\CommandProvider
     * - Framework\Provider\Runtime\CommandProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'command' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of custom Command definitions to load into service factory method.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of strings. Each string should be valid className for custom command
         * implementing Runtime\Command\CommandInterface .
         *
         * EXAMPLE: [ "App\\Command\\Model\\CustomModel" ]
         * -------------------------------------------------------------------------------------------------------------
         */
        'models'   => [],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of Command plugins to plug into service factory.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of strings. Each string should be valid className for custom plugin
         * implementing Util\Factory\FactoryPluginInterface .
         *
         * EXAMPLE: [ "App\\Command\\Plugin\\CustomPlugin" ]
         * -------------------------------------------------------------------------------------------------------------
         */
        'plugins'  => [],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of custom Commands to register
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of commands in form of commandAlias=>commandClassName. Each command
         * should implement Runtime\Command\CommandInterface .
         *
         * EXAMPLE: [ "app:command" => "App\\Command\\MyCustomCommand" ]
         * -------------------------------------------------------------------------------------------------------------
         */
        'commands' => []
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Supervision service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Provider\SupervisorProvider
     * - Framework\Remote\Provider\SupervisorProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'supervision' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of custom Solver definitions to load into service factory method.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of strings. Each string should be valid className for custom solver
         * implementing Supervision\SolverInterface .
         *
         * EXAMPLE: [ "App\\Supervision\\Solver\\CustomSolver" ]
         * -------------------------------------------------------------------------------------------------------------
         */
        'solvers'  => [],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of Solver plugins to plug into service factory.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of strings. Each string should be valid className for custom plugin
         * implementing Util\Factory\FactoryPluginInterface .
         *
         * EXAMPLE: [ "App\\Supervision\\Plugin\\CustomPlugin" ]
         * -------------------------------------------------------------------------------------------------------------
         */
        'plugins'  => [],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * Configuration for base Supervision manager.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain following configuration params:
         * - params
         * - handlers
         * - plugins
         * -------------------------------------------------------------------------------------------------------------
         */
        'base'     => [

            'params' => [
                'timeout'         => 4.0,
                'retriesLimit'    => 10,
                'retriesInterval' => 2.0
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * Error & Exception handlers to register for base Supervision manager.
             * ---------------------------------------------------------------------------------------------------------
             * This variable should contain array associative array in which key should be valid className for
             * an Error or Exception to to catch and handler is valid className or array of classNames for solvers to
             * execute.
             *
             * EXAMPLE: [ "App\\Exception\\SomeException" => "App\\Supervision\\Solver\\CustomSolver" ]
             * ---------------------------------------------------------------------------------------------------------
             */
            'handlers' => [],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * List of Solver plugins to plug into base Supervision solvers factory.
             * ---------------------------------------------------------------------------------------------------------
             * This variable should contain array of strings. Each string should be valid className for custom plugin
             * implementing Util\Factory\FactoryPluginInterface .
             *
             * EXAMPLE: [ "App\\Supervision\\Plugin\\CustomPlugin" ]
             * ---------------------------------------------------------------------------------------------------------
             */
            'plugins'  => []
        ],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * Configuration for remote Supervision manager.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain following configuration params:
         * - params
         * - handlers
         * - plugins
         * -------------------------------------------------------------------------------------------------------------
         */
        'remote'   => [

            'params' => [
                'timeout'         => 4.0,
                'retriesLimit'    => 10,
                'retriesInterval' => 2.0
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * Error & Exception handlers to register for base Supervision manager.
             * ---------------------------------------------------------------------------------------------------------
             * This variable should contain array associative array in which key should be valid className for
             * an Error or Exception to to catch and handler is valid className or array of classNames for solvers to
             * execute.
             *
             * EXAMPLE: [ "App\\Exception\\SomeException" => "App\\Supervision\\Solver\\CustomSolver" ]
             * ---------------------------------------------------------------------------------------------------------
             */
            'handlers' => [],

            /**
             * -------------------------------------------------------------------------------------------------------------
             * List of Solver plugins to plug into base Supervision solvers factory.
             * -------------------------------------------------------------------------------------------------------------
             * This variable should contain array of strings. Each string should be valid className for custom plugin
             * implementing Util\Factory\FactoryPluginInterface .
             *
             * EXAMPLE: [ "App\\Supervision\\Plugin\\CustomPlugin" ]
             * -------------------------------------------------------------------------------------------------------------
             */
            'plugins'  => []
        ]
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Filesystem service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Provider\FilesystemProvider
     * - Framework\Runtime\Provider\FilesystemProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'filesystem' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * List of cloud storage drivers to register.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of cloud drivers in form of { class, config } structure. Each class-key
         * should correspond to one of Filesystem\Factory factory-classes and config should contain config for its
         * creation method.
         *
         * EXAMPLE: [[ "class" => "Kraken\\Filesystem\\Factory\\LocalFactory", "config" => [ "path" => $path ] ]]
         * -------------------------------------------------------------------------------------------------------------
         */
        'cloud' => []
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Log service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Provider\LogProvider
     * - Framework\Runtime\Provider\LogProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'log' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * Log levels to register.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain array of allowed log levels. Each level should be a string with PSR-compatible
         * log level.
         * -------------------------------------------------------------------------------------------------------------
         */
        'levels' => [
            'EMERGENCY',
            'WARNING',
            'NOTICE',
            'INFO',
            'DEBUG'
        ],

        /**
         * -------------------------------------------------------------------------------------------------------------
         * Default configuration for logger.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain params:
         * - messagePattern
         * - dataPattern
         * - filePattern
         * - fileLocking
         * - filePermission
         * -------------------------------------------------------------------------------------------------------------
         */
        'config' => [
            'messagePattern' => "[%datetime% %level_name%.%channel%]%message%\n\n",
            'datePattern'    => "Y-m-d H:i:s",
            'filePattern'    => "%datapath%/log/%level%/kraken.%date%.log",
            'fileLocking'    => false,
            'filePermission' => 0755
        ]
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Loop service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Provider\LoopProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'loop' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * Loop model to use.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain className of object that implements Loop\LoopModelInterface
         * -------------------------------------------------------------------------------------------------------------
         */
        'model' => 'Kraken\Loop\Model\SelectLoop'
    ],

    /**
     * -----------------------------------------------------------------------------------------------------------------
     * Runtime service configuration.
     * -----------------------------------------------------------------------------------------------------------------
     * used by:
     * - Framework\Runtime\Provider\RuntimeProvider
     * - Framework\Runtime\Provider\RuntimeBootProvider
     * - Framework\Runtime\Provider\RuntimeManagerProvider
     * -----------------------------------------------------------------------------------------------------------------
     */
    'runtime' => [
        /**
         * -------------------------------------------------------------------------------------------------------------
         * Runtime manager configuration.
         * -------------------------------------------------------------------------------------------------------------
         * This variable should contain params:
         * - process : { class, config }
         * - thread  : { class, config }
         * -------------------------------------------------------------------------------------------------------------
         */
        'manager' => [
            'process' => [
                'class'  => 'Kraken\Runtime\Container\Manager\ProcessManagerNull',
                'config' => []
            ],
            'thread' => [
                'class'  => 'Kraken\Runtime\Container\Manager\ThreadManagerNull',
                'config' => []
            ]
        ]
    ]
];

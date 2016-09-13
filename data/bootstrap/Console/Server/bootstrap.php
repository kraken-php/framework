<?php
/**
 * Bootstrap file for Console Server.
 * Any modifications in this file should be done with exceptional care.
 */

$core = new \Kraken\Root\Console\Server\Core\ServerCore(
    realpath(__DIR__ . '/../../../')
);

$providers = $core->getDefaultProviders();
$providers = array_merge($providers,
    [
        /**
         * List of service providers that needs to be registered for your application to work correctly.
         */
    ]
);

$aliases = $core->getDefaultAliases();
$aliases = array_merge($aliases,
    [
        /**
         * List of aliases that needs to be registered in IoC container for specified interfaces.
         */
    ]
);

$core->registerProviders($providers);
$core->registerAliases($aliases);

return $core;

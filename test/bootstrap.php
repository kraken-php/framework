<?php

require __DIR__.'/../vendor/autoload.php';

if (!defined('PHPUNIT_COMPOSER_INSTALL'))
{
    define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/../vendor/autoload.php');
}

if (!defined('TEST_USER'))
{
    define('TEST_USER', getenv('TEST_USER') ? getenv('TEST_USER') : get_current_user());
}

if (!defined('TEST_PASSWORD'))
{
    define('TEST_PASSWORD', getenv('TEST_PASSWORD') ? getenv('TEST_PASSWORD') : '1234');
}

if (!class_exists('Error'))
{
    class Error extends Exception
    {}
}

date_default_timezone_set('UTC');

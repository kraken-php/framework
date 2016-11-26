<?php

namespace Kraken\_Module\Log;

use Kraken\Log\Handler\HandlerInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerFactory;
use Kraken\Util\Support\StringSupport;
use Kraken\Test\TModule;
use Monolog\Processor\TagProcessor;

class LoggerTest extends TModule
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $messagePattern;

    /**
     * @var string
     */
    private $datePattern;

    /**
     *
     */
    private $rootPath;

    /**
     * @var string
     */
    private $filePattern;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->name = 'app';
        $this->messagePattern = "[%datetime% %level_name%.%channel%][%context.A%,%context.B%]%extra% %message%\n";
        $this->datePattern = 'Y-m-d H:i:s';
        $this->rootPath = __DIR__ . '/_Log';
        $this->filePattern = __DIR__ . '/_Log/%level%/app.%date%.log';

        $this->createDirStructure();
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->destroyDirStructure();

        parent::tearDown();
    }

    /**
     * @dataProvider levelsProvider
     * @param int $level
     */
    public function testApiLog_RecordsMessage_($level)
    {
        $logger = $this->createLogger();
        $message1 = "FIRST_MESSAGE";
        $message2 = "SPLIT_MESSAGE\nWITH_NEWLINES";

        $logger->log($level, $message1);
        $logger->log($level, $message2);

        $this->assertLog($level, [ $message1, $message2 ]);
    }

    /**
     * @dataProvider levelsProvider
     * @param int $level
     */
    public function testApiLog_RecordsMessage_WithContext($level)
    {
        $logger = $this->createLogger();

        $message1 = "FIRST_MESSAGE";
        $message2 = "SPLIT_MESSAGE\nWITH_NEWLINES";

        $context1 = [ 'A' => 'A', 'B' => 'B' ];
        $context2 = [ 'A' => 'X' ];

        $logger->log($level, $message1, $context1);
        $logger->log($level, $message2, $context2);

        $this->assertLog($level, [ $message1, $message2 ], [ $context1, $context2 ]);
    }


    /**
     * @dataProvider levelsProvider
     * @param int $level
     */
    public function testCaseAllLevelsMethods_RecordMessage($level)
    {
        $logger = $this->createLogger();
        $message1 = "FIRST_MESSAGE";
        $message2 = "SPLIT_MESSAGE\nWITH_NEWLINES";

        $func = $this->getLevelName($level);

        call_user_func_array([ $logger, $func ], [ $message1 ]);
        call_user_func_array([ $logger, $func ], [ $message2 ]);

        $this->assertLog($level, [ $message1, $message2 ]);
    }

    /**
     * @dataProvider levelsProvider
     * @param int $level
     */
    public function testCaseAllLevelMethods_RecordMessage_WithContext($level)
    {
        $logger = $this->createLogger();

        $message1 = "FIRST_MESSAGE";
        $message2 = "SPLIT_MESSAGE\nWITH_NEWLINES";

        $context1 = [ 'A' => 'A', 'B' => 'B' ];
        $context2 = [ 'A' => 'X' ];

        $func = $this->getLevelName($level);

        call_user_func_array([ $logger, $func ], [ $message1, $context1 ]);
        call_user_func_array([ $logger, $func ], [ $message2, $context2 ]);

        $this->assertLog($level, [ $message1, $message2 ], [ $context1, $context2 ]);
    }

    /**
     *
     */
    public function testApiLog_LogsMessageUsingFirstLowestLevelHandler()
    {
        $logger = $this->createSimpleLogger();

        $message = "SOME_MESSAGE";

        $logger->log(Logger::CRITICAL, $message);

        $this->assertLog([ Logger::CRITICAL, Logger::ERROR ], [ $message ]);
    }

    /**
     *
     */
    public function testApiLog_ReturnsFalse_WhenLogLevelDoesNotMatchAnyHandler()
    {
        $logger = $this->createSimpleLogger();

        $message = "SOME_MESSAGE";

        $this->assertFalse($logger->log(Logger::INFO, $message));
    }

    /**
     *
     */
    public function testApiLog_ReturnsTrue_WhenLogLevelDoesMatchSomeHandler()
    {
        $logger = $this->createSimpleLogger();

        $message = "SOME_MESSAGE";

        $this->assertTrue($logger->log(Logger::WARNING, $message));
    }

    /**
     *
     */
    public function testApiGetLevels_ReturnsSupportedLevels()
    {
        $logger = $this->createSimpleLogger();

        $this->assertSame($this->getLevels(), $logger->getLevels());
    }

    /**
     *
     */
    public function testApiGetLevelName_ReturnsLevelName()
    {
        $logger = $this->createSimpleLogger();
        $levels = $this->getLevels();

        foreach ($levels as $levelName=>$levelValue)
        {
            $this->assertSame($levelName, $logger->getLevelName($levelValue));
        }
    }

    /**
     *
     */
    public function testApiGetLevelName_ReturnsNull_WhenInvalidLevelValueSet()
    {
        $logger = $this->createSimpleLogger();

        $this->assertSame(null, $logger->getLevelName('NonExistant'));
    }

    /**
     * @return int[][]
     */
    public function levelsProvider()
    {
        return [
            [ Logger::EMERGENCY ],
            [ Logger::ALERT ],
            [ Logger::CRITICAL ],
            [ Logger::ERROR ],
            [ Logger::WARNING ],
            [ Logger::NOTICE ],
            [ Logger::INFO ],
            [ Logger::DEBUG ]
        ];
    }

    /**
     * @param int|int[] $level
     * @param string[] $messages
     * @param string[][] $contexts
     */
    public function assertLog($level, $messages = [], $contexts = [])
    {
        if (is_array($level))
        {
            $mLevel = $level[0];
            $dLevel = $level[1];
        }
        else
        {
            $mLevel = $dLevel = $level;
        }

        $fullMessage = '';

        foreach ($messages as $key=>$message)
        {
            $context = isset($contexts[$key]) ? $contexts[$key] : [];
            $fullMessage .= $this->createMessage($mLevel, $message, $context);
        }

        $this->assertRegExp('#' . $fullMessage . '#si', $this->getLog($dLevel));
    }

    /**
     * @param int $level
     * @return string
     */
    public function getLog($level)
    {
        $path = $this->filePath($this->filePattern, $this->getLevelName($level));

        return file_get_contents($path);
    }

    /**
     * @param int $level
     * @param string $message
     * @param string[] $context
     * @return string
     */
    public function createMessage($level, $message, $context = [])
    {
        $sub = '%context.A%,%context.B%';

        foreach ([ 'A', 'B' ] as $key)
        {
            $val = isset($context[$key]) ? $context[$key] : '';
            $sub = str_replace("%context.$key%", $val, $sub);
        }

        $extra = "\{\"tags\":\{\"tag1\":\"T1\",\"tag2\":\"T2\"\}\}";

        return StringSupport::parametrize(
            "\[%datetime% %level_name%.%channel%\]\[$sub\]$extra %message%\n",
            [
                'message'       => $message,
                'channel'       => $this->name,
                'datetime'      => date('Y-m-d') . ' ([0-9]{2}):([0-9]{2}):([0-9]{2})',
                'level_name'    => $this->getLevelName($level, true)
            ]
        );
    }

    /**
     * @return Logger[][]
     */
    public function loggersProvider()
    {
        return [
            [ $this->createLogger() ]
        ];
    }

    /**
     * @return Logger
     */
    public function createSimpleLogger()
    {
        $loggers = [
            $this->createHandler(Logger::ERROR),
            $this->createHandler(Logger::WARNING),
            $this->createHandler(Logger::NOTICE)
        ];

        $processors = [
            new TagProcessor([ 'tag1' => 'T1', 'tag2' => 'T2' ])
        ];

        return new Logger(
            $this->name,
            $loggers,
            $processors
        );
    }

    /**
     * @return Logger
     */
    public function createLogger()
    {
        $loggers = [
            $this->createHandler(Logger::EMERGENCY),
            $this->createHandler(Logger::ALERT),
            $this->createHandler(Logger::CRITICAL),
            $this->createHandler(Logger::ERROR),
            $this->createHandler(Logger::WARNING),
            $this->createHandler(Logger::NOTICE),
            $this->createHandler(Logger::INFO),
            $this->createHandler(Logger::DEBUG)

        ];

        $processors = [
            new TagProcessor([ 'tag1' => 'T1', 'tag2' => 'T2' ])
        ];

        return new Logger(
            $this->name,
            $loggers,
            $processors
        );
    }

    /**
     * @return array
     */
    public function getLevels()
    {
        return [
            'DEBUG'     => 100,
            'INFO'      => 200,
            'NOTICE'    => 250,
            'WARNING'   => 300,
            'ERROR'     => 400,
            'CRITICAL'  => 500,
            'ALERT'     => 550,
            'EMERGENCY' => 600
        ];
    }

    /**
     * @param int $loggerLevel
     * @return HandlerInterface
     */
    private function createHandler($loggerLevel)
    {
        $factory = new LoggerFactory();
        $level = $this->getLevelName($loggerLevel);

        $formatter = $factory->createFormatter(
            'LineFormatter',
            [
                $this->messagePattern,
                $this->datePattern,
                true
            ]
        );

        $filePermission = 0755;
        $fileLocking = false;
        $filePath = $this->filePattern;

        $loggerHandler = $factory->createHandler(
            'StreamHandler',
            [
                $this->filePath($filePath, $level),
                $loggerLevel,
                false,
                $filePermission,
                $fileLocking
            ]
        );
        $loggerHandler
            ->setFormatter($formatter);

        return $loggerHandler;
    }

    /**
     * @param int $level
     * @param bool $uppercase
     * @return string
     */
    private function getLevelName($level, $uppercase = false)
    {
        switch ($level)
        {
            case Logger::EMERGENCY: $name = 'emergency'; break;
            case Logger::ALERT:     $name = 'alert';     break;
            case Logger::CRITICAL:  $name = 'critical';  break;
            case Logger::ERROR:     $name = 'error';     break;
            case Logger::WARNING:   $name = 'warning';   break;
            case Logger::NOTICE:    $name = 'notice';    break;
            case Logger::INFO:      $name = 'info';      break;
            case Logger::DEBUG:     $name = 'debug';     break;
            default:                $name = '';
        }

        return $uppercase ? mb_strtoupper($name) : $name;
    }

    /**
     * @param string $path
     * @param string $level
     * @return string
     */
    private function filePath($path, $level)
    {
        return StringSupport::parametrize($path, [
            'level' => $level,
            'date'  => date('Y-m-d'),
            'time'  => date('H:i:s')
        ]);
    }

    /**
     *
     */
    private function createDirStructure()
    {
        $this->destroyDirStructure();

        $chmod = 0755;
        mkdir($this->rootPath, $chmod);
        mkdir($this->rootPath . '/debug',     $chmod);
        mkdir($this->rootPath . '/info',      $chmod);
        mkdir($this->rootPath . '/notice',    $chmod);
        mkdir($this->rootPath . '/warning',   $chmod);
        mkdir($this->rootPath . '/error',     $chmod);
        mkdir($this->rootPath . '/critical',  $chmod);
        mkdir($this->rootPath . '/alert',     $chmod);
        mkdir($this->rootPath . '/emergency', $chmod);
    }

    /**
     *
     */
    private function destroyDirStructure()
    {
        $this->rrmdir($this->rootPath);
    }

    /**
     * @param $dir
     */
    private function rrmdir($dir)
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);

            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (is_dir($dir."/".$object))
                    {
                        $this->rrmdir($dir . "/" . $object);
                    }
                    else
                    {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}

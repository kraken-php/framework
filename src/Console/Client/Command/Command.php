<?php

namespace Kraken\Console\Client\Command;

use Kraken\Runtime\Runtime;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Error;
use Exception;

abstract class Command extends SymfonyCommand
{
    /**
     * @var CommandHandlerInterface
     */
    protected $manager;

    /**
     * @param CommandHandlerInterface $manager
     */
    public function __construct(CommandHandlerInterface $manager)
    {
        parent::__construct(null);

        $this->manager = $manager;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->manager);
    }

    /**
     *
     */
    abstract protected function config();

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    abstract protected function command(InputInterface $input, OutputInterface $output);

    /**
     * @param int|string $flags
     * @return int
     */
    protected function validateCreateFlags($flags)
    {
        if (
            $flags === Runtime::CREATE_DEFAULT
            || $flags === Runtime::CREATE_FORCE_SOFT
            || $flags === Runtime::CREATE_FORCE_HARD
            || $flags === Runtime::CREATE_FORCE
        ){
            return $flags;
        }

        if ($flags === 'CREATE_DEFAULT')
        {
            return Runtime::CREATE_DEFAULT;
        }

        if ($flags === 'CREATE_FORCE_SOFT')
        {
            return Runtime::CREATE_FORCE_SOFT;
        }

        if ($flags === 'CREATE_FORCE_HARD')
        {
            return Runtime::CREATE_FORCE_HARD;
        }

        if ($flags === 'CREATE_FORCE')
        {
            return Runtime::CREATE_FORCE;
        }

        throw new InvalidArgumentException('Given flag option is invalid.');
    }

    /**
     * @param int|string $flags
     * @return int
     */
    protected function validateDestroyFlags($flags)
    {
        if (
            $flags === Runtime::DESTROY_KEEP
            || $flags === Runtime::DESTROY_FORCE_SOFT
            || $flags === Runtime::DESTROY_FORCE_HARD
            || $flags === Runtime::DESTROY_FORCE
        ){
            return $flags;
        }

        if ($flags === 'DESTROY_KEEP')
        {
            return Runtime::DESTROY_KEEP;
        }

        if ($flags === 'DESTROY_DEFAULT')
        {
            return Runtime::DESTROY_KEEP;
        }

        if ($flags === 'DESTROY_FORCE_SOFT')
        {
            return Runtime::DESTROY_FORCE_SOFT;
        }

        if ($flags === 'DESTROY_FORCE_HARD')
        {
            return Runtime::DESTROY_FORCE_HARD;
        }

        if ($flags === 'DESTROY_FORCE')
        {
            return Runtime::DESTROY_FORCE;
        }

        throw new InvalidArgumentException('Given flag option is invalid.');
    }

    /**
     *
     */
    protected function onStart()
    {
        echo "Executing command: " . $this->getName() ." ... ";
    }

    /**
     * @param $value
     */
    protected function onSuccess($value)
    {
        if (is_array($value))
        {
            $this->successData($this->onMessage($value));
        }
        else
        {
            $this->successMessage($this->onMessage($value));
        }
    }

    /**
     * @param Error|Exception $ex
     */
    protected function onFailure($ex)
    {
        $this->failureMessage(get_class($ex), $ex->getMessage());
    }

    /**
     *
     */
    protected function onStop()
    {
        return;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function onMessage($value)
    {
        if (is_array($value))
        {
            return [ $value ];
        }
        else
        {
            return $value;
        }
    }

    /**
     * @internal
     */
    protected function configure()
    {
        $this->config();
    }

    /**
     * @internal
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->onStart();
        $promise = call_user_func_array([ $this->manager, 'handle' ], $this->command($input, $output));
        $promise
            ->then(
                function($value) {
                    $this->onSuccess($value);
                },
                function($ex) {
                    $this->onFailure($ex);
                }
            )
            ->always(
                function() {
                    $this->onStop();
                }
            )
            ->always(
                function() {
                    exit;
                }
            );
    }

    /**
     * @param mixed[] $input
     */
    protected function successData($input)
    {
        print("Success\n");

        $data = [];
        $header = [];
        $lengths = [];
        $mbLengths = [];
        foreach ($input[0] as $key=>$value)
        {
            $header[] = $key;
            $lengths[$key] = strlen($key);
            $mbLengths[$key] = mb_strlen($key);
        }
        $data[] = $header;

        foreach ($input as $row)
        {
            foreach ($row as $key=>$value)
            {
                if ($value === null)
                {
                    $value = 'null';
                    $row[$key] = $value;
                }

                $len = strlen($value);
                $mbLen = mb_strlen($value);

                if ($len > $lengths[$key])
                {
                    $lengths[$key] = $len;
                }

                if ($mbLen > $mbLengths[$key])
                {
                    $mbLengths[$key] = $mbLen;
                }
            }

            $data[] = $row;
        }

        $mask = '|';
        foreach ($lengths as $length)
        {
            $mask .= ' %-' . $length . '.' . $length . 's |';
        }
        $mask .= PHP_EOL;

        $mbMask = '|';
        foreach ($mbLengths as $mbLength)
        {
            $mbMask .= ' %-' . $mbLength . '.' . $mbLength . 's |';
        }
        $mbMask .= PHP_EOL;

        call_user_func_array('printf', array_merge([ $mbMask ], $header));
        call_user_func_array('printf', array_merge([ $mbMask ], $data[1]));

        for ($i=2; $i<count($data); $i++)
        {
            call_user_func_array('printf', array_merge([ $mask ], $data[$i]));
        }
    }

    /**
     * @param $message
     * @return string
     */
    protected function successMessage($message)
    {
        echo "Success\n" . $message . PHP_EOL;
    }

    /**
     * @param $exception
     * @param $message
     * @return string
     */
    protected function failureMessage($exception, $message)
    {
        echo "Failure\n" . $exception . " => " . $message . PHP_EOL;
    }
}

<?php

namespace Kraken\Console\Client;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Error;
use Exception;

abstract class ConsoleCommand extends Command
{
    /**
     * @var ConsoleCommandHandlerInterface
     */
    protected $manager;

    /**
     * @param ConsoleCommandHandlerInterface $manager
     */
    public function __construct(ConsoleCommandHandlerInterface $manager)
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
        foreach ($input[0] as $key=>$value)
        {
            $header[] = $key;
            $lengths[$key] = strlen($key);
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
                if ($len > $lengths[$key])
                {
                    $lengths[$key] = $len;
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


        foreach ($lengths as $length)
        {
            print('+' . str_repeat('-', $length+2));
        }
        print('+' . PHP_EOL);

        call_user_func_array('printf', array_merge([ $mask ], $header));

        foreach ($lengths as $length)
        {
            print('+' . str_repeat('-', $length+2));
        }
        print('+' . PHP_EOL);

        for ($i=1; $i<count($data); $i++)
        {
            call_user_func_array('printf', array_merge([ $mask ], $data[$i]));
        }

        foreach ($lengths as $length)
        {
            print('+' . str_repeat('-', $length+2));
        }
        print('+' . PHP_EOL);
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

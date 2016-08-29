<?php

namespace Kraken\Console\Client\Command;

use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Promise\Promise;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Error;
use Exception;

abstract class Command extends SymfonyCommand implements CommandInterface
{
    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var string
     */
    protected $receiver;

    /**
     * @var bool
     */
    protected $async;

    /**
     * @param ChannelBaseInterface $channel
     * @param string $receiver
     */
    public function __construct(ChannelBaseInterface $channel, $receiver)
    {
        parent::__construct(null);

        $this->channel  = $channel;
        $this->receiver = $receiver;
        $this->async    = true;

        $this->construct();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->destruct();

        unset($this->receiver);
        unset($this->channel);
        unset($this->async);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isAsync()
    {
        return $this->async;
    }

    /**
     * This method will be invoked during command construction.
     */
    protected function construct()
    {}

    /**
     * This method will be invoked during command destruction.
     */
    protected function destruct()
    {}

    /**
     * This method should contain command configuration.
     */
    abstract protected function config();

    /**
     * This method should contain command logic.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed[]
     */
    abstract protected function command(InputInterface $input, OutputInterface $output);

    /**
     * @param int|string $flags
     * @return int
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
        echo "Executing : " . $this->getName() ." ... ";
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function onSuccess($value)
    {
        if (is_array($value))
        {
            return $this->successData($this->onMessage($value));
        }
        else
        {
            return $this->successMessage($this->onMessage($value));
        }
    }

    /**
     * @param Error|Exception $ex
     * @return mixed
     */
    protected function onFailure($ex)
    {
        return $this->failureMessage(get_class($ex), $ex->getMessage());
    }

    /**
     * @param Error|Exception $ex
     * @return mixed
     */
    protected function onCancel($ex)
    {
        return $this->cancelMessage(get_class($ex), $ex->getMessage());
    }

    /**
     *
     */
    protected function onStop()
    {
        exit(0);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function onMessage($value)
    {
        return is_array($value) ? [ $value ] : $value;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->config();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->onStart();

        $promise = Promise::doResolve($this->command($input, $output));
        $promise
            ->then(
                function($value) {
                    return $this->onSuccess($value);
                },
                function($ex) {
                    return $this->onFailure($ex);
                },
                function($ex) {
                    return $this->onCancel($ex);
                }
            )
            ->always(
                function() {
                    $this->onStop();
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    protected function informServer($commandParent, $commandName, $commandParams = [])
    {
        $protocol = $this->channel->createProtocol(
            new RuntimeCommand($commandName, $commandParams)
        );

        if ($commandParent !== null)
        {
            $protocol->setDestination($commandParent);
        }

        $req = new Request(
            $this->channel,
            $this->receiver,
            $protocol,
            [
                'timeout'         =>  2,
                'retriesLimit'    => 10,
                'retriesInterval' =>  1
            ]
        );

        return $req->call();
    }

    /**
     * @param mixed[] $input
     */
    protected function successData($input)
    {
        print("Success\n");

        $header = [];
        $data = [];
        $lengths = [];
        foreach ($input[0] as $key=>$value)
        {
            $header[$key] = $key;
            $lengths[$key] = 0;
        }
        array_unshift($input, $header);

        foreach ($input as &$row)
        {
            foreach ($row as $key=>$value)
            {
                if ($value === null)
                {
                    $value = 'null';
                    $row[$key] = $value;
                }

                $mbLen = mb_strlen($value);

                if ($mbLen > $lengths[$key])
                {
                    $lengths[$key] = $mbLen;
                }
            }
        }
        unset($row);

        foreach ($input as &$row)
        {
            foreach ($row as $key=>$value)
            {
                $mbLen = mb_strlen($value);

                if ($mbLen < $lengths[$key])
                {
                    $value .= str_repeat(' ', $lengths[$key]-$mbLen);
                    $row[$key] = $value;
                }
            }

            $data[] = $row;
        }
        unset($row);

        $output = "";

        foreach ($data as &$row)
        {
            $output .= "|";

            foreach ($row as $key=>$value)
            {
                $output .= " " . $value . " |";
            }

            $output .= "\n";
        }
        unset($row);

        print($output);
    }

    /**
     * @param $message
     * @return string
     */
    protected function successMessage($message)
    {
        echo "success!\nResponse  : \033[42m\033[1;37m" . $message . "\033[0m\n";
    }

    /**
     * @param $exception
     * @param $message
     * @return string
     */
    protected function failureMessage($exception, $message)
    {
        echo "failure!\nResponse  : \033[41m\033[1;37m" . $message . "\033[0m\nReason    : \033[41m\033[1;37m" . $exception . "\033[0m\n";
    }

    /**
     * @param $exception
     * @param $message
     * @return string
     */
    protected function cancelMessage($exception, $message)
    {
        echo "cancel!\nResponse   : \033[41m\033[1;37m" . $message . "\033[0m\nReason    : \033[41m\033[1;37m" . $exception . "\033[0m\n";
    }
}

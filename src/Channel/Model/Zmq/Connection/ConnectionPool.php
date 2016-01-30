<?php

namespace Kraken\Channel\Model\Zmq\Connection;

class ConnectionPool
{
    /**
     * @var callable
     */
    protected $now;

    /**
     * @var mixed[]
     */
    protected $connectionPool;

    /**
     * @var float
     */
    protected $keepaliveOffset;

    /**
     * @var float
     */
    protected $heartbeatOffset;

    /**
     * @param float $offset
     */
    public function __construct($offset = 3600000.0, $heartbeat = 200.0)
    {
        $this->connectionPool = [];
        $this->keepaliveOffset = $offset;
        $this->heartbeatOffset = $heartbeat;
        $this->resetNow();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->connectionPool = [];
        unset($this->now);
    }

    /**
     *
     */
    public function erase()
    {
        $this->connectionPool = [];
    }

    /**
     * @return string[]
     */
    public function getConnected()
    {
        $conns = [];

        // there is no need for timestamp validation since messages to inactive clients are lost either way
        foreach ($this->connectionPool as $connID=>$conn)
        {
            $conns[] = $connID;
        }

        return $conns;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function setConnection($id)
    {
        if (!$this->existsConnection($id))
        {
            $this->connectionPool[$id] = $this->register();
            $ret = true;
        }
        else
        {
            $this->connectionPool[$id] = $this->register($this->connectionPool[$id]);
            $ret = false;
        }

        return $ret;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function removeConnection($id)
    {
        if ($this->existsConnection($id))
        {
            unset($this->connectionPool[$id]);
            return true;
        }

        return false;
    }

    /**
     * @param string $id
     * @return mixed[]
     */
    public function getConnection($id)
    {
        return $this->connectionPool[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function existsConnection($id)
    {
        return isset($this->connectionPool[$id]);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function validateConnection($id)
    {
        if (!$this->existsConnection($id))
        {
            return false;
        }

        return $this->validateIn($this->generateConst(), $this->getConnection($id));
    }

    /**
     * @return string[]
     */
    public function removeInvalid()
    {
        $toDel = [];
        $const = $this->generateConst();

        foreach ($this->connectionPool as $cid=>$cdata)
        {
            if (!$this->validateIn($const, $cdata))
            {
                $toDel[] = $cid;
            }
        }

        foreach ($toDel as $cid)
        {
            unset($this->connectionPool[$cid]);
        }

        return $toDel;
    }

    /**
     * @param string $id
     * @param string $property
     * @param mixed $value
     */
    public function setConnectionProperty($id, $property, $value)
    {
        if (!$this->existsConnection($id))
        {
            $this->setConnection($id);
        }

        $this->connectionPool[$id][$property] = $value;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isHeartbeatNeeded($id)
    {
        if (!$this->existsConnection($id))
        {
            return true;
        }

        return $this->validateOut($this->generateConst(), $this->getConnection($id));
    }

    /**
     * @param $id
     * @return bool
     */
    public function registerHeartbeat($id)
    {
        if (!$this->existsConnection($id))
        {
            return false;
        }

        $this->connectionPool[$id]['timestampOut'] = $this->getNow() + $this->heartbeatOffset;

        return true;
    }

    /**
     * @return float
     */
    public function getNow()
    {
        $callback = $this->now;
        return $callback();
    }

    /**
     * @param callable $callback
     */
    public function setNow(callable $callback)
    {
        $this->now = $callback;
    }

    /**
     *
     */
    public function resetNow()
    {
        $this->now = function() {
            return round(microtime(true)*1000);
        };
    }

    /**
     * @param array $current
     * @return mixed[]
     */
    protected function register($current = [])
    {
        return [
            'timestampIn'  => $this->getNow() + $this->keepaliveOffset,
            'timestampOut' => isset($current['timestampOut']) ? $current['timestampOut'] : 0
        ];
    }

    /**
     * @param mixed[] $const
     * @param mixed[] $data
     * @return bool
     */
    protected function validateIn($const, $data)
    {
        return $data['timestampIn'] === 0 || ($const['timestampIn'] - $data['timestampIn']) <= 0;
    }

    /**
     * @param mixed[] $const
     * @param mixed[] $data
     * @return bool
     */
    protected function validateOut($const, $data)
    {
        return $data['timestampOut'] === 0 || ($const['timestampOut'] - $data['timestampOut']) > 0;
    }

    /**
     * @return mixed[]
     */
    protected function generateConst()
    {
        $now = $this->getNow();

        return [
            'timestampIn'  => $now,
            'timestampOut' => $now
        ];
    }
}

<?php

namespace Kraken\Channel\Model\Socket\Connection;

/**
 * @codeCoverageIgnore
 */
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
     * @param Connection $conn
     * @return bool
     */
    public function setConnection(Connection $conn)
    {
        $id = $conn->id;

        if ($this->existsConnection($id))
        {
            $this->connectionPool[$id] = $this->register($conn, $this->connectionPool[$id]);
            return false;
        }
        else
        {
            $this->connectionPool[$id] = $this->register($conn);
            return true;
        }
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
     * @return Connection
     */
    public function getConnection($id)
    {
        return $this->connectionPool[$id]['conn'];
    }

    /**
     * @param string $id
     * @return mixed[]
     */
    public function getData($id)
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

        return $this->validateIn($this->generateValidationConst(), $this->getData($id));
    }

    /**
     * @return string[]
     */
    public function removeInvalid()
    {
        $toDel = [];
        $const = $this->generateValidationConst();

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

        return $this->validateOut($this->generateValidationConst(), $this->getData($id));
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
    protected function register(Connection $conn, $current = [])
    {
        return [
            'conn'         => $conn,
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
    protected function generateValidationConst()
    {
        $now = $this->getNow();

        return [
            'timestampIn'  => $now,
            'timestampOut' => $now
        ];
    }
}

<?php

namespace Kraken\Redis\Command;

interface ManageInterface
{
    public function clientList();
    public function clientGetName();
    public function clientPause();
    public function clientReply($operation);
    public function clientSetName($connetionName);
    public function clusterAddSlots(...$slots);
    public function clusterCountFailureReports($nodeId);
    public function clusterCountKeysInSlot($slot);
    public function clusterDelSlots(...$slots);
    public function clusterFailOver($operation);
    public function clusterForget($nodeId);
    public function clusterGetKeyInSlot($slot,$count);
    public function clusterInfo();
    public function clusterKeySlot($key);
    public function clusterMeet($ip,$port);
    public function clusterNodes();
    public function clusterReplicate($nodeId);
    public function clusterReset($mode);
    public function clusterSaveConfig();
    /**
     * @doc https://redis.io/commands/cluster-set-config-epoch
     * @since 3.0.0
     * @param $configEpoch
     * @return mixed
     */
    public function clusterSetConfigEpoch($configEpoch);
    /**
     * @doc https://redis.io/commands/cluster-setslot
     * @since 3.0.0
     * @param $command
     * @param $nodeId
     * @return mixed
     */
    public function clusterSetSlot($command,$nodeId);
    /**
     * @doc https://redis.io/commands/cluster-slaves
     * @since 3.0.0
     * @param $nodeId
     * @return mixed
     */
    public function clusterSlaves($nodeId);
    /**
     * @doc https://redis.io/commands/cluster-slots
     * @return mixed
     */
    public function clusterSlots();
    public function flushAll($isAsync);
    public function flushDb($isAsync);
}
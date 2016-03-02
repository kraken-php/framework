<?php

namespace Kraken\Loop;

use Kraken\Loop\Flow\FlowController;

interface LoopExtendedInterface extends LoopInterface
{
    /**
     * @return LoopModelInterface
     */
    public function model();

    /**
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function flush($all = false);

    /**
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function export(LoopExtendedInterface $loop, $all = false);

    /**
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function import(LoopExtendedInterface $loop, $all = false);

    /**
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function swap(LoopExtendedInterface $loop, $all = false);

    /**
     *
     */
    public function tick();

    /**
     *
     */
    public function start();

    /**
     *
     */
    public function stop();


    /**
     * @param mixed $flowController
     */
    public function setFlowController($flowController);

    /**
     * @return FlowController
     */
    public function getFlowController();
}

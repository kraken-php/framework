<?php

namespace Kraken\Loop;

use Kraken\Loop\Flow\FlowController;

interface LoopExtendedInterface extends LoopInterface
{
    /**
     * @return LoopModelInterface
     */
    public function getModel();

    /**
     * Perform a single iteration of the event loop.
     */
    public function tick();

    /**
     * Run the loop until there are no more tasks to perform.
     */
    public function start();

    /**
     * Instruct a running event loop to stop.
     */
    public function stop();


    /**
     * Set FlowController used by model.
     *
     * @param mixed $flowController
     */
    public function setFlowController($flowController);

    /**
     * Return FlowController used by model.
     *
     * @return FlowController
     */
    public function getFlowController();

    /**
     * Erase loop.
     *
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function erase($all = false);

    /**
     * Export loop not fired handlers and/or streams to another loop model.
     *
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function export(LoopExtendedInterface $loop, $all = false);

    /**
     * Import handlers and/or streams from another loop model.
     *
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function import(LoopExtendedInterface $loop, $all = false);

    /**
     * Swap handlers and/or stream between loop models.
     *
     * @param LoopExtendedInterface $loop
     * @param bool $all
     * @return LoopExtendedInterface
     */
    public function swap(LoopExtendedInterface $loop, $all = false);
}

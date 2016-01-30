<?php

namespace Kraken\Error;

interface ErrorManagerAwareInterface
{
    /**
     * @param ErrorManagerInterface $manager
     */
    public function setErrorManager(ErrorManagerInterface $manager);

    /**
     * @return ErrorManagerInterface
     */
    public function getErrorManager();

    /**
     * @return ErrorManagerInterface
     */
    public function errorManager();
}

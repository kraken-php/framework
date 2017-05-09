<?php

namespace Kraken\Stream;

use Kraken\Loop\LoopResourceInterface;

interface AsyncStreamWriterInterface extends StreamWriterInterface, LoopResourceInterface
{}

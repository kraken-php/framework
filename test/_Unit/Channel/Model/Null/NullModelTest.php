<?php

namespace Kraken\_Unit\Channel\Model\Null;

use Kraken\Channel\Model\Null\NullModel;
use Kraken\Test\TUnit;

class NullModelTest extends TUnit
{
    /**
     *
     */
    public function testApiStart_ReturnsTrue()
    {
        $model = $this->createModel();
        $this->assertTrue($model->start());
    }

    /**
     *
     */
    public function testApiStop_ReturnsTrue()
    {
        $model = $this->createModel();
        $this->assertTrue($model->stop());
    }

    /**
     *
     */
    public function testApiUnicast()
    {
        $model = $this->createModel();
        $this->assertTrue($model->unicast($id = 'id', $text = 'text', $flags = 'flags'));
    }

    /**
     *
     */
    public function testApiBroadcast()
    {
        $model = $this->createModel();
        $this->assertSame([], $model->broadcast($text = 'text'));
    }

    /**
     *
     */
    public function testApiIsConnected()
    {
        $model = $this->createModel();
        $this->assertFalse($model->isConnected($id = 'id'));
    }

    /**
     *
     */
    public function testApiGetConnected()
    {
        $model = $this->createModel();
        $this->assertSame([], $model->getConnected());
    }

    /**
     *
     */
    public function createModel()
    {
        return new NullModel();
    }
}

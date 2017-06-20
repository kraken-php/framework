<?php

namespace Kraken\_Unit\Redis;

use Kraken\Console\Client\ClientInterface;
use Kraken\Loop\Loop;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Test\TUnit;
use Kraken\Redis\Client;

class ClientTest extends TUnit
{
    /**
     * @var Client
     */
    private $case;

    public function __construct()
    {
        parent::__construct();
    }


    public function setUp()
    {
        $this->case = $this->createClient();
    }
    /**
     * @group passed
     */
    public function testSet()
    {
        $this->case->run(function (Client $client) {
            $client->set('test','test contents')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals('OK', $ret);
    }
    /**
     * @group passed
     */
    public function testGet()
    {
        $this->case->run(function (Client $client) {
            $client->set('test','test contents');
            $client->get('test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals('test contents', $ret);
    }
    /**
     * @group passed
     */
    public function testAppend()
    {
        $this->case->run(function (Client $client) {
            $client->set('test', 'test contents');
            $client->append('test', ' plus')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(strlen('test contents plus'), $ret);
    }
    /**
     * @group passed
     */
    public function testPing()
    {
        $this->case->run(function (Client $client) {
            $client->ping()->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals('PING', $ret);
    }
    /**
     * @group passed
     */
    public function testExists()
    {
        $this->case->run(function (Client $client) {
            $client->touch('test');
            $client->exists('test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }
    /**
     * @group passed
     */
    public function testInfo()
    {
        $this->case->run(function (Client $client) {
            $client->info()->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertNotEmpty($ret);
    }
    /**
     * @group passed
     */
    public function testTouch()
    {
        $this->case->run(function (Client $client) {
            $client->touch('test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }
    /**
     * @group passed
     */
    public function testRename()
    {
        $this->case->run(function (Client $client) {
            $client->touch('test');
            $client->rename('test','new_test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals('OK', $ret);
    }

    /**
     * @group passed
     */
    public function testRenameNx()
    {
        $this->case->run(function (Client $client) {
            $client->set('test', 'ok')->then(function ($_) use ($client) {
                $client->renameNx('test', 'new')->then(function ($resp) use ($client) {
                    global $ret;
                    $ret = $resp;
                    $client->renameNx('test', 'o_new')->then(null, function (\Exception $e) {
                        //fix : can't throw $e
                        global $ret2;
                        $ret2 = $e;
                    })->then(function ($_) use ($client) {
                        $client->flushAll();
                    });
                });
            });
        });
        global $ret,$ret2;
        $this->assertEquals(1, $ret);
        $this->assertInstanceOf(\Exception::class, $ret2);
    }
    /**
     * @group passed
     */
    public function testTtl()
    {
        $this->case->run(function (Client $client) {
            $client->touch('test');
            $client->ttl('test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(-2, $ret);
    }
    /**
     * @group passed
     */
    public function testDel()
    {
        $this->case->run(function (Client $client) {
            $client->set('test','test');
            $client->del('test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }
    /**
     * @group passed
     */
    public function testExpire()
    {
        $this->case->run(function (Client $client) {
            $client->set('test','test');
            $client->expire('test',1);
            $client->ttl('test')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }
    /**
     * @group passed
     */
    public function testExpireAt()
    {
        $this->case->run(function (Client $client) {
            $client->set('test', 'test');
            $client->expireAt('test', time() + 10)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushAll();
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }

    /**
     * @group passed
     */
    public function testType()
    {
        $this->case->run(function (Client $client) {
            $client->set('test','str')->then(function ($_) use ($client) {
                $client->type('test')->then(function ($resp) {
                    global $ret2;
                    $ret2 = $resp;
                })->then(function ($_) use ($client) {
                    $client->del('test')->then(function ($_) use ($client) {
                        $client->type('test')->then(function ($resp) {
                            global $ret;
                            $ret = $resp;
                        });
                    });
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret,$ret2;
        $this->assertEquals('none', $ret);
        $this->assertEquals('string', $ret2);
    }

    /**
     * @group passed
     */
    public function testPersist()
    {
        $this->case->run(function (Client $client) {
            $client->set('test', 'ok', ['ex',100])->then(function ($_) use ($client) {
                $client->persist('test')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
            },function (\Exception $e) {
                die($e->getMessage());
            })->then(function ($_) use ($client) {
                $client->flushAll();
            });
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }
    /**
     * @group passed
     */
    public function testRPush()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list',1,2,3,4,5)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(5, $ret);
    }

    /**
     * @group passed
     */
    public function testLRange()
    {
        $this->case->run(function (Client $client) {
            $list = 'test_list';
            $client->lPush($list, 1, 2, 3, 4, 5)->then(function ($_) use ($client, $list) {
                $client->lRange($list)->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret;
        $this->assertNotEmpty($ret);
    }
    /**
     * @group passed
     */
    public function testLPush()
    {
        $this->case->run(function (Client $client) {
            $client->lPush('test_list_left',1,2,3,4,5)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushAll();
        });
        global $ret;
        $this->assertEquals(5, $ret);
    }

    /**
     * @group passed
     */
    public function testLPushX()
    {
        $this->case->run(function (Client $client) {
            $client->lPush('test_list',1,2,3,4,5)->then(function ($_) use ($client) {
                $client->lPushX('test_list', 6)->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret;
        $this->assertEquals(6, $ret);
    }

    /**
     * @group passed
     */
    public function testRPushX()
    {
        $this->case->run(function (Client $client) {
            $client->rPushX('test_xr_list','ok')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals(0, $ret);
    }

    /**
     * @group passed
     */
    public function testRPop()
    {
        $this->case->run(function (Client $client) {
           $client->lPush('test_list',1,2,3)->then(function ($_) use ($client) {
               $client->rPop('test_list')->then(function ($resp) use ($client) {
                   global $ret;
                   $ret = $resp;
               })->then(function ($_) use ($client) {
                   $client->flushAll();
               });
           });
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }

    /**
     * @group passed
     */
    public function testLPop()
    {
        $this->case->run(function (Client $client) {
            $client->lPush('test_list',1,2,3)->then(function ($_) use ($client) {
                $client->lPop('test_list')->then(function ($resp) use ($client) {
                    global $ret;
                    $ret = $resp;
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret;
        $this->assertEquals(3, $ret);
    }

    /**
     * @group passed
     */
    public function testBRPopLPush()
    {
        $this->case->run(function (Client $client) {
            $client->lPush('test_src',1,2,3);
            $client->lPush('test_tgt',4,5,6);
            $client->brPopLPush('test_src', 'test_tgt', 1)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            })->then(function ($_) use ($client){
                $client->lRange('test_tgt')->then(function ($resp) {
                    global $ret2;
                    $ret2 = $resp;
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret,$ret2;
        $this->assertEquals(1, $ret);
        $this->assertEquals([1,6,5,4], $ret2);
    }

    /**
     * @group passed
     */
    public function testBLPop()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list',1,2,3);
            $client->blPop(['test_list'], 3)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            })->then(function ($_) use ($client) {
                $client->flushAll();
            });
        });
        global $ret;
        $this->assertEquals(['key'=>'test_list','value'=>1], $ret);
    }

    /**
     * @group passed
     */
    public function testBRPop()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list',1,2,3);
            $client->brPop(['test_list'], 3)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            })->then(function ($_) use ($client) {
                $client->flushAll();
            });
        });
        global $ret;
        $this->assertEquals(['key'=>'test_list','value'=>3], $ret);
    }

    /**
     * @group passed
     */
    public function testLIndex()
    {
        $this->case->run(function (Client $client) {
            $client->lPush('test_list','world');
            $client->lPush('test_list','hello');
            $client->lIndex('test_list', 0)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->lIndex('test_list', -1)->then(function ($resp) {
                global $ret2;
                $ret2 = $resp;
            });
            $client->lIndex('test_list', 3)->then(function ($resp) {
                global $ret3;
                $ret3 = $resp;
            });
            $client->flushAll();
        });
        global $ret,$ret2,$ret3;
        $this->assertEquals('hello', $ret);
        $this->assertEquals('world', $ret2);
        $this->assertEquals(null, $ret3);
    }

    /**
     * @group passed
     */
    public function testLLen()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list', 1,2,3);
            $client->lLen('test_list')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushAll();
        });
        global $ret;
        $this->assertEquals(3, $ret);
    }

    /**
     * @group passed
     */
    public function testLRem()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list','hello');
            $client->rPush('test_list','hello');
            $client->rPush('test_list','foo');
            $client->rPush('test_list','hello');
            $client->lRem('test_list', -2, 'hello');
            $client->lRange('test_list')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushAll();
        });
        global $ret;
        $this->assertEquals(['hello','foo'], $ret);
    }

    /**
     * @group passed
     */
    public function testLSet()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list', 1);
            $client->rPush('test_list', 2);
            $client->rPush('test_list', 3);
            $client->lSet('test_list', 0, 4);
            $client->lSet('test_list', -2, 5);
            $client->lRange('test_list')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushAll();
        });
        global $ret;
        $this->assertEquals([4,5,3], $ret);
    }

    /**
     * @group passed
     */
    public function testRPopLPush()
    {
        $this->case->run(function (Client $client) {
            $client->lPush('test_src',1,2,3);
            $client->lPush('test_tgt',4,5,6);
            $client->rPopLPush('test_src', 'test_tgt')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            })->then(function ($_) use ($client){
                $client->lRange('test_tgt')->then(function ($resp) {
                    global $ret2;
                    $ret2 = $resp;
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret,$ret2;
        $this->assertEquals(1, $ret);
        $this->assertEquals([1,6,5,4], $ret2);
    }

    /**
     * @group passed
     */
    public function testLInsert()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list',1);
            $client->rPush('test_list',2);
            $client->lInsert('test_list','AFTER', 2, 3)->then(function ($_) use ($client) {
                $client->lRange('test_list')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
            })->then(function ($_) use ($client) {
                $client->flushAll();
            });
        });
        global $ret;
        $this->assertEquals([1,2,3], $ret);
    }

    /**
     * @group passed
     */
    public function testLTrim()
    {
        $this->case->run(function (Client $client) {
            $client->rPush('test_list', 'one');
            $client->rPush('test_list', 'two');
            $client->rPush('test_list', 'three');
            $client->lTrim('test_list', 1, -1)->then(function ($_) use ($client) {
                $client->lRange('test_list')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                })->then(function ($_) use ($client) {
                    $client->flushAll();
                });
            });
        });
        global $ret;
        $this->assertEquals(['two','three'], $ret);
    }

    // \/--Hashes--\/

    /**
     * @group passed
     */
    public function testHdel()
    {
        $this->case->run(function (Client $client) {
            $client->hSet('k','f','v');
            $client->hDel('k','f')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
            });
            $client->flushDb();
        });

        global $ret;
        $this->assertEquals(1, $ret);
    }

    /**
     * @group passed
     */
    public function testHExists()
    {
        $this->case->run(function (Client $client) {
            $client->hSet('k','f','v')->then(function ($resp) use ($client){
                $client->hExists('k')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                }); 
                $client->flushDb();
            });
        });

        global $ret;
        $this->assertEquals(1, $ret);
    }

    /**
     * @group passed
     */
    public function testHget()
    {
        $this->case->run(function (Client $client) {
            $client->hSet('k','f','v')->then(function ($_) use ($client) {
               
                $client->hGet('k','f')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals('v', $ret);
    }

    /**
     * @group passed
     */
    public function testHGetAll()
    {
        $this->case->run(function (Client $client) {
            $client->hMSet('k', ['f1'=>'v1','f2'=>'v2','f3'=>'v3'])->then(function ($_) use ($client) {
                $client->hGetAll('k')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(['f1'=>'v1','f2'=>'v2','f3'=>'v3'], $ret);
    }

    /**
     * @group passed
     */
    public function testHIncrBy()
    {
         $this->case->run(function (Client $client) {
            $client->hMSet('k', ['f1'=>1,'f2'=>2,'f3'=>3])->then(function ($_) use ($client) {
                $client->hIncrBy('k', 'f1', 1)->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(2, $ret);
    }

    /**
     * @group passed
     */
    public function testIncrByFloat()
    {
         $this->case->run(function (Client $client) {
            $client->hMSet('k', ['f1'=>1,'f2'=>2,'f3'=>3])->then(function ($_) use ($client) {
                $client->hIncrByFloat('k', 'f1', 1.5)->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(2.5, $ret);
    }

    /**
     * @group passed
     */
    public function testHKeys()
    {
        $this->case->run(function (Client $client) {
            $client->hMSet('k', ['f1'=>'v1','f2'=>'v2','f3'=>'v3'])->then(function ($_) use ($client) {
                $client->hKeys('k')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(['f1','f2','f3'], $ret);
    }
    
    /**
     * @group passed
     */
    public function testHLen()
    {
        $this->case->run(function (Client $client) {
            $client->hMSet('k', ['f1'=>'v1','f2'=>'v2','f3'=>'v3'])->then(function ($_) use ($client) {
                $client->hLen('k')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(3, $ret);
    }

     /**
     * @group passed
     */
    public function testHMGet()
    {
        $this->case->run(function (Client $client) {
            $client->hMSet('k', ['f1'=>'v1','f2'=>'v2','f3'=>'v3'])->then(function ($_) use ($client) {
                $client->hMGet('k','f1','f2','f3')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(['v1','v2','v3'], $ret);
    }

    /**
     * @group passed
     */
    public function testHMSet()
    {
        $this->case->run(function (Client $client) {
            $fv = [
                'f1' => 'v1',
                'f2' => 'v2',
                'f3' => 'v3',
            ];
            $client->hMSet('k', $fv)->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushDb();
        });
        global $ret;
        $this->assertEquals('OK', $ret);
    }

    /**
     * @group passed
     */
    public function testHSet()
    {
        $this->case->run(function (Client $client) {
            $client->hSet('k','f','v')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushDb();
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }

    /**
     * @group testing
     */
    public function testHSetNx()
    {
        $this->case->run(function (Client $client) {
            $client->hSetNx('k','f','v')->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
            $client->flushDb();
        });
        global $ret;
        $this->assertEquals(1, $ret);
    }

    /**
     * @group testing
     */
    public function testHStrlen()
    {
        $this->case->run(function (Client $client) {
            $client->hSet('k','f','hello')->then(function ($_) use ($client) {
                $client->hStrLen('k', 'f')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(strlen('hello'), $ret);
    }

    /**
     * @group testing
     */
    public function testHVals()
    {
        $this->case->run(function (Client $client) {
            $fv = [
                'f1' => 'v1',
                'f2' => 'v2',
                'f3' => 'v3',
            ];
            $client->hMSet('k', $fv)->then(function ($_) use ($client) {
                $client->hVals('k')->then(function ($resp) {
                    global $ret;
                    $ret = $resp;
                });
                $client->flushDb();
            });
        });
        global $ret;
        $this->assertEquals(['v1','v2','v3'], $ret);
    }

    /**
     * @group testing
     */
    public function testScan()
    {

    }

    /**
     * @group passed
     */
    public function testFlushDb()
    {
        $this->case->run(function (Client $client) {
            $client->flushAll()->then(function ($resp) {
                global $ret;
                $ret = $resp;
            }) ;
        });
        global $ret;
        $this->assertEquals('OK', $ret);
    }

    /**
     * @group passed
     */
    public function testQuit()
    {
        $this->case->run(function (Client $client) {
            $client->quit()->then(function ($resp) {
                global $ret;
                $ret = $resp;
            });
        });
        global $ret;
        $this->assertEquals('OK', $ret);
    }

    private function createClient()
    {
        //set your local redis server url
        $host = "192.168.99.100:36379";
        $loop = new Loop(new SelectLoop());

        return new Client($host , $loop);
    }
}
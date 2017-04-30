<?php

namespace Kraken\_Module\SSH;

use Kraken\SSH\Auth\SSH2Password;
use Kraken\SSH\SSH2;
use Kraken\SSH\SSH2Config;
use Kraken\SSH\SSH2DriverInterface;
use Kraken\SSH\SSH2Interface;
use Kraken\SSH\SSH2ResourceInterface;
use Kraken\Test\Simulation\Simulation;
use Kraken\Test\Simulation\SimulationInterface;
use Kraken\Test\TModule;

class SSH2Test extends TModule
{
    /**
     *
     */
    public function testSSH2_ConnectsAndDisconnectsProperly()
    {
        if (!extension_loaded('ssh2'))
        {
            $this->markTestSkipped('Test has been skipped because of lacking SSH2 extension!');
        }

        $sim = $this;
        $sim = $sim->simulate(function(SimulationInterface $sim) {
            $loop   = $sim->getLoop();
            $auth   = new SSH2Password(TEST_USER, TEST_PASSWORD);
            $config = new SSH2Config();
            $ssh2   = new SSH2($auth, $config, $loop);

            $ssh2->on('connect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('connect');
                $ssh->disconnect();
            });

            $ssh2->on('disconnect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('disconnect');
                $sim->done();
            });

            $ssh2->on('error', function(SSH2Interface $ssh, $ex) use($sim) {
                $sim->fail($ex->getMessage());
            });

            $sim->onStart(function() use($ssh2) {
                $ssh2->connect();
            });
            $sim->onStop(function() use($ssh2) {
                $ssh2->disconnect();
            });

        });
        $sim = $sim->expect([
            [ 'connect', [] ],
            [ 'disconnect', [] ]
        ]);
    }

    /**
     *
     */
    public function testSSH2_ShellDriver_IsAbleToExecute_SingleLineCommand()
    {
        if (!extension_loaded('ssh2'))
        {
            $this->markTestSkipped('Test has been skipped because of lacking SSH2 extension!');
        }

        $sim = $this;
        $sim = $sim->simulate(function(SimulationInterface $sim) {
            $loop   = $sim->getLoop();
            $auth   = new SSH2Password(TEST_USER, TEST_PASSWORD);
            $config = new SSH2Config();
            $ssh2   = new SSH2($auth, $config, $loop);

            $ssh2->on('connect:shell', function(SSH2DriverInterface $shell) use($sim) {
                $sim->expect('connect:shell');
                $buffer = '';

                $command = $shell->open();
                $command->write('echo "test"');
                $command->on('data', function($command, $data) use(&$buffer) {
                    $buffer .= $data;
                });
                $command->on('end', function() use(&$buffer, $shell, $sim) {
                    $sim->expect('buffer', [ $buffer ]);
                    $shell->disconnect();
                });
            });

            $ssh2->on('disconnect:shell', function(SSH2DriverInterface $shell) use($sim, $ssh2) {
                $sim->expect('disconnect:shell');
                $ssh2->disconnect();
            });

            $ssh2->on('connect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('connect');
                $ssh->createDriver(SSH2::DRIVER_SHELL)
                    ->connect();
            });

            $ssh2->on('disconnect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('disconnect');
                $sim->done();
            });

            $ssh2->on('error', function(SSH2Interface $ssh, $ex) use($sim) {
                $sim->fail($ex->getMessage());
            });

            $sim->onStart(function() use($ssh2) {
                $ssh2->connect();
            });
            $sim->onStop(function() use($ssh2) {
                $ssh2->disconnect();
            });
        });
        $sim = $sim->expect([
            [ 'connect', [] ],
            [ 'connect:shell', [] ],
            [ 'buffer', [ "test\r\n" ] ],
            [ 'disconnect:shell', [] ],
            [ 'disconnect', [] ]
        ]);
    }

    /**
     *
     */
    public function testSSH2_ShellDriver_IsAbleToExecute_MultiLineCommand()
    {
        if (!extension_loaded('ssh2'))
        {
            $this->markTestSkipped('Test has been skipped because of lacking SSH2 extension!');
        }

        $sim = $this;
        $sim = $sim->simulate(function(SimulationInterface $sim) {
            $loop   = $sim->getLoop();
            $auth   = new SSH2Password(TEST_USER, TEST_PASSWORD);
            $config = new SSH2Config();
            $ssh2   = new SSH2($auth, $config, $loop);

            $ssh2->on('connect:shell', function(SSH2DriverInterface $shell) use($sim) {
                $sim->expect('connect:shell');

                $buffer = '';
                $command = $shell->open();
                $command->write('printf "A\nB\nC\n"');
                $command->on('data', function($command, $data) use(&$buffer) {
                    $buffer .= $data;
                });
                $command->on('end', function() use(&$buffer, $shell, $sim) {
                    $sim->expect('buffer', [ $buffer ]);
                    $shell->disconnect();
                });
            });

            $ssh2->on('disconnect:shell', function(SSH2DriverInterface $shell) use($sim, $ssh2) {
                $sim->expect('disconnect:shell');
                $ssh2->disconnect();
            });

            $ssh2->on('connect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('connect');
                $ssh->createDriver(SSH2::DRIVER_SHELL)
                    ->connect();
            });

            $ssh2->on('disconnect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('disconnect');
                $sim->done();
            });

            $ssh2->on('error', function(SSH2Interface $ssh, $ex) use($sim) {
                $sim->fail($ex->getMessage());
            });

            $sim->onStart(function() use($ssh2) {
                $ssh2->connect();
            });
            $sim->onStop(function() use($ssh2) {
                $ssh2->disconnect();
            });
        });
        $sim = $sim->expect([
            [ 'connect', [] ],
            [ 'connect:shell', [] ],
            [ 'buffer', [ "A\r\nB\r\nC\r\n" ] ],
            [ 'disconnect:shell', [] ],
            [ 'disconnect', [] ]
        ]);
    }

    /**
     *
     */
    public function testSSH2_SftpDriver_IsAbleToWriteFiles()
    {
        $this->markTestSkipped('It seems there are problems with setting this automation on Travis.');

        $sim = $this;
        $sim = $sim->simulate(function(SimulationInterface $sim) {
            $loop   = $sim->getLoop();
            $auth   = new SSH2Password(TEST_USER, TEST_PASSWORD);
            $config = new SSH2Config();
            $ssh2   = new SSH2($auth, $config, $loop);

            $ssh2->on('connect:sftp', function(SSH2DriverInterface $sftp) use($sim) {
                $sim->expect('connect:sftp');

                $lines = [ "KRAKEN\n", "IS\n", "AWESOME!\n" ];
                $linesPointer = 0;

                $file = $sftp->open(__DIR__ . '/_Data/file_write.txt', 'w+');
                $file->write();
                $file->on('drain', function(SSH2ResourceInterface $file) use(&$lines, &$linesPointer) {
                    if ($linesPointer < count($lines)) {
                        $file->write($lines[$linesPointer++]);
                    }
                });
                $file->on('finish', function(SSH2ResourceInterface $file) {
                    $file->close();
                });
                $file->on('close', function(SSH2ResourceInterface $file) use($sftp) {
                    $sftp->disconnect();
                });
            });

            $ssh2->on('disconnect:sftp', function(SSH2DriverInterface $sftp) use($sim, $ssh2) {
                $sim->expect('disconnect:sftp');
                $ssh2->disconnect();
            });

            $ssh2->on('connect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('connect');
                $ssh->createDriver(SSH2::DRIVER_SFTP)
                    ->connect();
            });

            $ssh2->on('disconnect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('disconnect');
                $sim->done();
            });

            $ssh2->on('error', function(SSH2Interface $ssh, $ex) use($sim) {
                $sim->fail($ex->getMessage());
            });

            $sim->onStart(function() use($ssh2) {
                $ssh2->connect();
            });
            $sim->onStop(function() use($ssh2) {
                $ssh2->disconnect();
            });
        });
        $sim = $sim->expect([
            [ 'connect', [] ],
            [ 'connect:sftp', [] ],
            [ 'disconnect:sftp', [] ],
            [ 'disconnect', [] ]
        ]);
    }

    /**
     *
     */
    public function testSSH2_SftpDriver_IsAbleToReadFiles()
    {
        $this->markTestSkipped('It seems there are problems with setting this automation on Travis.');

        $sim = $this;
        $sim = $sim->simulate(function(SimulationInterface $sim) {
            $loop   = $sim->getLoop();
            $auth   = new SSH2Password(TEST_USER, TEST_PASSWORD);
            $config = new SSH2Config();
            $ssh2   = new SSH2($auth, $config, $loop);

            $ssh2->on('connect:sftp', function(SSH2DriverInterface $sftp) use($sim) {
                $sim->expect('connect:sftp');

                $buffer = '';
                $file = $sftp->open(__DIR__ . '/_Data/file_read.txt', 'r+');
                $file->read();
                $file->on('data', function(SSH2ResourceInterface $file, $data) use(&$buffer) {
                    $buffer .= $data;
                });
                $file->on('end', function(SSH2ResourceInterface $file) use(&$buffer, $sim) {
                    $sim->expect('buffer', [ $buffer ]);
                    $file->close();
                });
                $file->on('close', function(SSH2ResourceInterface $file) use($sftp) {
                    $sftp->disconnect();
                });
            });

            $ssh2->on('disconnect:sftp', function(SSH2DriverInterface $sftp) use($sim, $ssh2) {
                $sim->expect('disconnect:sftp');
                $ssh2->disconnect();
            });

            $ssh2->on('connect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('connect');
                $ssh->createDriver(SSH2::DRIVER_SFTP)
                    ->connect();
            });

            $ssh2->on('disconnect', function(SSH2Interface $ssh) use($sim) {
                $sim->expect('disconnect');
                $sim->done();
            });

            $ssh2->on('error', function(SSH2Interface $ssh, $ex) use($sim) {
                $sim->fail($ex->getMessage());
            });

            $sim->onStart(function() use($ssh2) {
                $ssh2->connect();
            });
            $sim->onStop(function() use($ssh2) {
                $ssh2->disconnect();
            });
        });
        $sim = $sim->expect([
            [ 'connect', [] ],
            [ 'connect:sftp', [] ],
            [ 'buffer', [ "KRAKEN\r\nIS\r\nAWESOME\r\n" ] ],
            [ 'disconnect:sftp', [] ],
            [ 'disconnect', [] ]
        ]);
    }
}

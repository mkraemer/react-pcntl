<?php

namespace MKraemer\ReactPCNTL;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;

class PCNTL extends EventEmitter
{
    /**
     * Constructor. Registers a periodicTimer to call
     * the installed signal handlers
     *
     * @param \React\EventLoop\LoopInterface $loop     Event Loop
     * @param float                          $interval Interval in which new signals should be read
     */
    public function __construct(LoopInterface $loop, $interval = 0.1)
    {
        $loop->addPeriodicTimer($interval, $this);
    }

    /**
     * Registers a new signal handler
     *
     * @param int      $signo    The signal number
     * @param callable $listener The listener
     */
    public function on($signo, callable $listener)
    {
        pcntl_signal($signo, array($this, 'emit'));
        parent::on($signo, $listener);
    }

    /**
     * Call signal handlers for pending signals
     */
    public function __invoke()
    {
        pcntl_signal_dispatch();
    }
}

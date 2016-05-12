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
     * Removes a signal handler
     *
     * @param int      $signo    The signal number
     * @param callable $listener The listener
     */
    public function removeListener($signo, callable $listener) {
        // call the parent's code
        parent::removeListener($signo, $listener);

        // if last listener removed, uninstall PCNTL signal handler
        if (empty($this->listeners[$signo])) {
            pcntl_signal($signo, SIG_DFL);
        }
    }

    /**
     * Removes all signal handlers
     *
     * @param int|null $signo    The signal number
     */
    public function removeAllListeners($signo = null) {
        // prepare a list of signal numbers to deal with
        $signoList = [];
        if (!is_null($signo)) {
            $signoList = [$signo];
        } elseif (is_array($this->listeners)) {
            $signoList = array_keys($this->listeners);
        }

        // call the parent's code
        parent::removeAllListeners($signo);

        // uninstall PCNTL signal handlers
        foreach ($signoList as $realSigno) {
            pcntl_signal($realSigno, SIG_DFL);
        }
    }

    /**
     * Call signal handlers for pending signals
     */
    public function __invoke()
    {
        pcntl_signal_dispatch();
    }
}

<?php

namespace MKraemer\ReactPCNTL;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

class PCNTL extends EventEmitter
{
    const DEFAULT_INTERVAL = 0.1;

    /**
     * @var TimerInterface|null
     */
    private $timer;
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * Constructor. Registers a periodicTimer to call
     * the installed signal handlers
     *
     * @param LoopInterface $loop     Event Loop
     * @param float         $interval Interval in which new signals should be read
     */
    public function __construct(LoopInterface $loop, $interval = self::DEFAULT_INTERVAL)
    {
        $this->loop = $loop;
        $this->start($interval);
    }

    /**
     * Adds timer to Loop queue
     *
     * @param float $interval
     * @return TimerInterface
     */
    public function start($interval = self::DEFAULT_INTERVAL)
    {
        if ($this->timer) {
            $this->stop();
        }

        return $this->timer = $this->loop->addPeriodicTimer($interval, $this);
    }

    /**
     * Cancels timer
     */
    public function stop()
    {
        if ($this->timer && $this->timer->isActive()) {
            $this->timer->cancel();
        }

        $this->timer = null;
    }

    /**
     * Registers a new signal handler
     *
     * @param int      $signo    The signal number
     * @param callable $listener The listener
     */
    public function on($signo, callable $listener)
    {
        /*
         * The associative array given when pcntl_signal calls the handler function
         * can not be passed on to EventEmitterTrait::emit, as evenement 3
         * does not support associative arrays. Drop it by using an intermediate
         * callback function:
         */
        pcntl_signal($signo, function () use ($signo) {$this->emit($signo);});
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

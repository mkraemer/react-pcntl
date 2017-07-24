<?php
namespace MKraemer\ReactPCNTL;

use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

function pcntl_signal($signo, $callback) {
    PCNTLTest::$pcntl_signal_args[$signo] = $callback;
}
function pcntl_signal_dispatch() {
    PCNTLTest::$pcntl_signal_dispatch = true;
}

class PCNTLTest extends \PHPUnit\Framework\TestCase
{
    public static $pcntl_signal_args;
    public static $pcntl_signal_dispatch;

    /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loop;

    protected function setUp()
    {
        self::$pcntl_signal_args = array();
        self::$pcntl_signal_dispatch = false;
        $this->loop = $this->createMock('React\EventLoop\LoopInterface');
    }

    public function testLoopStartStop()
    {
        $timer = $this->createMock('React\EventLoop\Timer\TimerInterface');
        $this->loop->expects($this->exactly(2))
            ->method('addPeriodicTimer')
            ->with(0.1, $this->isType('callable'))
            ->willReturn($timer)
        ;
        $timer->expects($this->once())
            ->method('isActive')
            ->willReturn(true)
        ;
        $timer->expects($this->once())
            ->method('cancel')
        ;

        $pcntl = new PCNTL($this->loop);
        $timer = $pcntl->start();
        $this->assertInstanceOf('\React\EventLoop\Timer\TimerInterface', $timer);
    }

    public function testRegisterSignalHandler()
    {
        $pcntl = new PCNTL($this->loop);
        $pcntl->on(SIGTERM, function(){ });

        $listeners = $pcntl->listeners(SIGTERM);
        $this->assertEquals(1, count($listeners));
        $this->assertEquals(1, count(self::$pcntl_signal_args));
        $this->assertArrayHasKey(SIGTERM, self::$pcntl_signal_args);
        $this->assertSame(array($pcntl, 'emit'), self::$pcntl_signal_args[SIGTERM]);
    }

    public function testInvokeCallsDispatch()
    {
        $this->assertFalse(self::$pcntl_signal_dispatch);
        $pcntl = new PCNTL($this->loop);
        $pcntl();
        $this->assertTrue(self::$pcntl_signal_dispatch);
    }

    public function testRemoveLastListener()
    {
        $pcntl = new PCNTL($this->loop);

        $listener = function () { };
        $pcntl->on(SIGTERM, $listener);
        $pcntl->removeListener(SIGTERM, $listener);

        $this->assertEmpty($pcntl->listeners(SIGTERM));
        $this->assertEquals(SIG_DFL, self::$pcntl_signal_args[SIGTERM]);
    }

    public function testRemoveNotLastListener()
    {
        $pcntl = new PCNTL($this->loop);

        $pcntl->on(SIGTERM, function () {});

        $secondListener = function () { };
        $pcntl->on(SIGTERM, $secondListener);
        $pcntl->removeListener(SIGTERM, $secondListener);

        $this->assertNotEquals(SIG_DFL, self::$pcntl_signal_args[SIGTERM]);
    }

    public function testRemoveAllListenersOfOneSignal()
    {
        $pcntl = new PCNTL($this->loop);

        $pcntl->on(SIGTERM, function () {});
        $pcntl->on(SIGHUP, function () {});

        $pcntl->removeAllListeners(SIGTERM);

        $this->assertEmpty($pcntl->listeners(SIGTERM));
        $this->assertNotEmpty($pcntl->listeners(SIGHUP));
        $this->assertEquals(SIG_DFL, self::$pcntl_signal_args[SIGTERM]);
        $this->assertSame(array($pcntl, 'emit'), self::$pcntl_signal_args[SIGHUP]);
    }

    public function testRemoveAllListenersOfAllSignals()
    {
        $pcntl = new PCNTL($this->loop);

        $pcntl->on(SIGTERM, function () {});
        $pcntl->on(SIGHUP, function () {});

        $pcntl->removeAllListeners();

        $this->assertEmpty($pcntl->listeners(SIGTERM));
        $this->assertEquals(SIG_DFL, self::$pcntl_signal_args[SIGTERM]);
        $this->assertEmpty($pcntl->listeners(SIGHUP));
        $this->assertEquals(SIG_DFL, self::$pcntl_signal_args[SIGHUP]);
    }
}

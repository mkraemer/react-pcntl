<?php
namespace MKraemer\ReactPCNTL;

use React\EventLoop\LoopInterface;

function pcntl_signal($signo, $callback) {
    PCNTLTest::$pcntl_signal_args = array($signo, $callback);
}
function pcntl_signal_dispatch() {
    PCNTLTest::$pcntl_signal_dispatch = true;
}

class PCNTLTest extends \PHPUnit_Framework_TestCase
{
    public static $pcntl_signal_args;
    public static $pcntl_signal_dispatch;

    /** @var LoopInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $loop;

    protected function setUp()
    {
        self::$pcntl_signal_args = null;
        self::$pcntl_signal_dispatch = false;
        $this->loop = $this->getMock('React\EventLoop\LoopInterface');
    }


    public function testConstruct_addsPeriodicTimer()
    {
        $this->loop->expects($this->once())
            ->method('addPeriodicTimer')
            ->with(0.1, $this->isType('callable'));

        new PCNTL($this->loop);
    }

    public function testRegisterSignalHandler()
    {
        $pcntl = new PCNTL($this->loop);
        $pcntl->on(SIGTERM, function(){ });

        $listeners = $pcntl->listeners(SIGTERM);
        $this->assertEquals(1, count($listeners));
        $this->assertInternalType('array', self::$pcntl_signal_args);
        $this->assertEquals(SIGTERM, self::$pcntl_signal_args[0]);
        $this->assertSame(array($pcntl, 'emit'), self::$pcntl_signal_args[1]);
    }

    public function testInvokeCallsDispatch()
    {
        $this->assertFalse(self::$pcntl_signal_dispatch);
        $pcntl = new PCNTL($this->loop);
        $pcntl();
        $this->assertTrue(self::$pcntl_signal_dispatch);
    }
}

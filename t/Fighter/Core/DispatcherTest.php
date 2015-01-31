<?hh //partial
namespace t\Fighter\Core;

use Fighter\Core\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {

    public function testAddEvents() {
        $dis = new Dispatcher();
        $dis->addEvent('testing', () ==> { 'pass'; });
        $this->assertTrue($dis->hasEvent('testing'));
        $this->assertFalse($dis->hasEvent('not existing'));
        $this->assertNotNull($dis->getEvent('testing'));
        $dis->clearEvent('testing');
        $this->assertFalse($dis->hasEvent('testing'));
    }

    public function testGetEvent() {
        $dis = new Dispatcher();
        $handler = () ==> { 'pass'; };
        $dis->addEvent('testing', $handler);
        $this->assertSame($handler, $dis->getEvent('testing'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetEventFailsOnNotRegisteredEvent() {
        $dis = new Dispatcher();
        $dis->getEvent('not registered');
    }

    public function testDispatch() {
        $dis = new Dispatcher();
        $called = Vector {};
        $dis->addEvent('testing', ($val) ==> { $called[] = $val; });
        $dis->dispatch('testing', Vector {103});
        $dis->dispatch('testing', Vector {'hello'});
        $this->assertEquals(Vector {103, 'hello'}, $called);
    }

    /**
     * @depends testDispatch
     */
    public function testAddHooks() {
        $dis = new Dispatcher();
        $called = Vector {};
        $dis->addEvent('testing', () ==> { $called[] = 'event'; });
        $dis->addHookBeforeEvent('testing', () ==> { $called[] = 'before'; });
        $dis->addHookAfterEvent('testing', () ==> { $called[] = 'after'; });
        $dis->dispatch('testing');
        $this->assertEquals(Vector {'before', 'event', 'after'}, $called);
    }

    public function testResetClearsEvents() {
        $dis = new Dispatcher();
        $dis->addEvent('testing', () ==> { 'pass'; });
        $dis->addEvent('testing2', () ==> { 'pass'; });
        $dis->reset();
        $this->assertFalse($dis->hasEvent('testing'));
        $this->assertFalse($dis->hasEvent('testing2'));
    }
}

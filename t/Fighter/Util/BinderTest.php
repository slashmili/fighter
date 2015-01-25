<?hh //partial

class Test_Fighter_Util_BinderObject {
    use Fighter\Util\Binder;
}

class Test_Fighter_Util_BinderHello {
    public function sayHi() {
        return 'hi';
    }

    public function sayBye() {
        return 'bye';
    }
}

class BinderTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->obj = new Test_Fighter_Util_BinderObject();
    }

    public function testClosureBinding() {
        $this->obj->bind('bind1', () ==> {
            return 'foo';
        });

        $this->assertEquals('foo', $this->obj->bind1());
    }

    public function testClassMethodBinding() {
        $h = new Test_Fighter_Util_BinderHello();
        $this->obj->bind('bind2', array($h, 'sayHi'));

        $this->assertEquals('hi', $this->obj->bind2());
    }


    public function testClassStaticBinding() {
        $this->obj->bind('bind3', array('Test_Fighter_Util_BinderHello', 'sayBye'));

        $this->assertEquals('bye', $this->obj->bind3());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Fatal error: Call to undefined method Test_Fighter_Util_BinderObject::non_exist_bind4
     */
    public function testBinderDoesNotExist() {
        set_error_handler(($errno, $errstr, $errfile, $errline) ==> {
            throw new \Exception($errstr);
        });
        $this->obj->non_exist_bind4();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage bind5 is not callable
     */
    public function testPassNotCallableBinding() {
        $this->obj->bind('bind5', 'what_where');
    }


    public function testClosureBindingWithParam() {
        $this->obj->bind('bind6', ($r) ==> {
            return 'rand: ' . $r;
        });

        $r = mt_rand();
        $this->assertEquals('rand: ' . $r, $this->obj->bind6($r));
    }

}


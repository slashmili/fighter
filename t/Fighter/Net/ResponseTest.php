<?hh //partial

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponseString() {
        $res = new \Fighter\Net\Response('ok');
        $this->assertEquals(
            'text/html',
            $res->type
        );
    }

    public function testResponseJson() {
        $res = new \Fighter\Net\Response(Map {'name' => 'test'});
        $this->assertEquals(
            'application/json',
            $res->type
        );
    }

    public function testResponseForceStringToJson() {
        $_SERVER['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml,application/json;q=0.9,image/webp,*/*;q=0.8";
        $res = new \Fighter\Net\Response('ok');
        $this->assertEquals(
            'application/json',
            $res->type
        );
    }

    public function testResponseForceArrayToJson() {
        $_SERVER['HTTP_ACCEPT'] = "text/html,application/xhtml+xml,application/xml,application/json;q=0.9,image/webp,*/*;q=0.8";
        $res = new \Fighter\Net\Response(['name'=> 'Json Array']);
        $this->assertEquals(
            'application/json',
            $res->type
        );
    }
}

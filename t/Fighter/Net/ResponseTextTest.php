<?hh //partial

class ResponseTextTest extends PHPUnit_Framework_TestCase
{
    public function testResponseString() {
        $res = new \Fighter\Net\Response\Text('ok');
        $this->assertEquals(
            'ok',
            (string)$res
        );
    }
}

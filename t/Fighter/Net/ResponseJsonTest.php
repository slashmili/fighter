<?hh //partial

class Fighter_Net_Response_Test_Person { public $full_name = "Sam Rast"; }
class ResponseJsonTest extends PHPUnit_Framework_TestCase
{
    public function testResponseSimpleString() {
        $res = new \Fighter\Net\Response\Json('ok');
        $this->assertEquals(
            '"ok"',
            (string)$res
        );
    }

    public function testResponseArray() {
        $res = new \Fighter\Net\Response\Json(['full_name' => 'Sam Rast']);
        $this->assertEquals(
            '{"full_name":"Sam Rast"}',
            (string)$res
        );
    }


    public function testResponseArrayOfArray() {
        $res = new \Fighter\Net\Response\Json([['full_name' => 'Sam Rast'], ['full_name' => 'Mas Tasr']]);
        $this->assertEquals(
            '[{"full_name":"Sam Rast"},{"full_name":"Mas Tasr"}]',
            (string)$res
        );
    }

    public function testResponseMap() {
        $res = new \Fighter\Net\Response\Json(Map {'full_name' => 'Sam Rast'});
        $this->assertEquals(
            '{"full_name":"Sam Rast"}',
            (string)$res
        );
    }

    public function testResponseVectorOfMap() {
        $res = new \Fighter\Net\Response\Json(Vector { Map {'full_name' => 'Sam Rast'}, Map {'full_name' => 'Mas Tsar'}});
        $this->assertEquals(
            '[{"full_name":"Sam Rast"},{"full_name":"Mas Tsar"}]',
            (string)$res
        );
    }

    public function testResponseObject() {
        $res = new \Fighter\Net\Response\Json(new Fighter_Net_Response_Test_Person());
        $this->assertEquals(
            '{"full_name":"Sam Rast"}',
            (string)$res
        );
    }
}

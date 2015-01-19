<?hh //partial

class RequestTest extends PHPUnit_Framework_TestCase
{

    public function setUp() {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '32.32.32.32';

        $this->request = new \Fighter\Net\Request();
    }

    public function testDefaults() {
        $this->assertEquals('/', $this->request->url);
        $this->assertEquals('GET', $this->request->method);
        $this->assertEquals('/', $this->request->base);
        $this->assertEquals('', $this->request->referrer);
        $this->assertEquals(true, $this->request->ajax);
        $this->assertEquals('HTTP/1.1', $this->request->scheme);
        $this->assertEquals('', $this->request->type);
        $this->assertEquals(0, $this->request->length);
        $this->assertEquals(true, $this->request->secure);
        $this->assertEquals('', $this->request->accept);
    }

    public function testIpAddress() {
        $this->assertEquals('8.8.8.8', $this->request->ip);
        $this->assertEquals('32.32.32.32', $this->request->proxy_ip);
    }

    public function testSubdirectory() {
        $_SERVER['SCRIPT_NAME'] = '/subdir/index.php';

        $req = new \Fighter\Net\Request();

        $this->assertEquals('/subdir', $req->base);
    }

    public function testQueryParameters() {
        $_SERVER['REQUEST_URI'] = '/page?id=1&name=bob';

        $req = new \Fighter\Net\Request();


        $this->assertEquals('/page?id=1&name=bob', $req->url);
        $this->assertEquals(1, $req->get->get('id'));
        $this->assertEquals('bob', $req->get->get('name'));
    }

    public function testCollections() {
        $_SERVER['REQUEST_URI'] = '/page?id=0';

        $_GET['q'] = 1;
        $_POST['q'] = 2;
        $_COOKIE['q'] = 3;
        $_FILES['q'] = 4;

        $req = new \Fighter\Net\Request();

        $this->assertEquals(0, $req->get->get('id'));
        $this->assertEquals(1, $req->get->get('q'));
        $this->assertEquals(2, $req->post->get('q'));
        $this->assertEquals(3, $req->cookies->get('q'));
        $this->assertEquals(4, $req->files->get('q'));
    }

    public function testMethodOverrideWithHeader() {
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';

        $req = new \Fighter\Net\Request();

        $this->assertEquals('PUT', $req->method);
    }

    public function testMethodOverrideWithPost() {
        $_REQUEST['_method'] = 'PUT';

        $req = new \Fighter\Net\Request();

        $this->assertEquals('PUT', $req->method);
    }
}

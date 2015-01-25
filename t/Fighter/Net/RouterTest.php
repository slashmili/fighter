<?hh //partial

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function setUp(): void {
        $this->router = new Fighter\Net\Router();
        $this->request = new Fighter\Net\Request();
    }

    public function testDefaultRoute() {
        $this->router->map('/', [$this, 'ok']);
        $this->request->url = '/';

        $this->check('OK');
    }

    // Simple output
    public function ok(): string {
        return 'OK';
    }

    // Checks if a route was matched with a given output
    public function check($str = '') {
        $route = $this->router->route($this->request);
        $params = array_values($route->params);
        $this->assertTrue(is_callable($route->callback));
        $response = call_user_func_array($route->callback, $params);
        $this->assertEquals($response, $str);
    }

    public function testRemoveRouteURLWhitespaces(): void {
        $this->router->map(' /path ', array($this, 'ok'));
        $this->request->url = '/path';
        $this->check('OK');
    }

    public function testRemoveRouteWhitespaces(): void {
        $this->router->map(' POST|GET /path ', array($this, 'ok'));
        $this->request->url = '/path';
        $this->check('OK');
    }

    // Simple path
    public function testPathRoute(): void {
        $this->router->map('/path', array($this, 'ok'));
        $this->request->url = '/path';
        $this->check('OK');
    }

    // POST route
    public function testPostRoute(): void {
        $this->router->map('POST /', array($this, 'ok'));
        $this->request->url = '/';
        $this->request->method = 'POST';
        $this->check('OK');
    }

    // Either GET or POST route
    public function testGetPostRoute():void {
        $this->router->map('GET|POST /', array($this, 'ok'));
        $this->request->url = '/';
        $this->request->method = 'GET';
        $this->check('OK');
    }

    // Test regular expression matching
    public function testRegEx() {
        $this->router->map('/num/[0-9]+', array($this, 'ok'));
        $this->request->url = '/num/1234';
        $this->check('OK');
    }

    // Passing URL parameters
    public function testUrlParameters(): void {
        $this->router->map('/user/@id', ($id) ==> $id);
        $this->request->url = '/user/123';
        $this->check('123');
    }

    // Passing URL parameters matched with regular expression
    public function testRegExParameters() {
        $this->router->map('/test/@name:[a-z]+', ($name) ==> $name);
        $this->request->url = '/test/abc';
        $this->check('abc');
    }

    // Optional parameters
    public function testOptionalParameters() {
        $this->router->map('/blog(/@year(/@month(/@day)))',
            ($year, $month, $day) ==> "$year,$month,$day"
        );
        $this->request->url = '/blog/2000';
        $this->check('2000,,');
    }

    // Regex in optional parameters
    public function testRegexOptionalParameters() {
        $this->router->map(
            '/@controller/@method(/@id:[0-9]+)',
            ($controller, $method, $id) ==> "$controller,$method,$id"
        );

        $this->request->url = '/user/delete/123';
        $this->check('user,delete,123');
    }

    // Regex in optional parameters
    public function testRegexEmptyOptionalParameters() {
        $this->router->map(
            '/@controller/@method(/@id:[0-9]+)', 
            ($controller, $method, $id) ==> "$controller,$method,$id"
        );

        $this->request->url = '/user/delete/';
        $this->check('user,delete,');
    }

    // Wildcard matching
    public function testWildcard() {
        $this->router->map('/account/*', array($this, 'ok'));
        $this->request->url = '/account/123/abc/xyz';
        $this->check('OK');
    }

    // Check if route object was passed
    public function testRouteObjectPassing() {
        $this->markTestSkipped(
            'Not sure if we need this!'
        );
        $this->router->map('/yes_route', function($route){
            $this->assertTrue(is_object($route));
        },
        true);
        $this->request->url = '/yes_route';
        $this->check();
        $this->router->map('/no_route', function($route = null){
            $this->assertTrue(is_null($route));
        },
        false);
        $this->request->url = '/no_route';
        $this->check();
    }

    // Test splat
    public function testSplatWildcard() {
        $this->markTestSkipped(
            'Not sure if we need this!'
        );

        $this->router->map('/account/*', function($route){
            echo $route->splat;
        },
        true);

        $this->request->url = '/account/456/def/xyz';
        $this->check('456/def/xyz');
    }

    // Test splat without trailing slash
    public function testSplatWildcardTrailingSlash() {
        $this->markTestSkipped(
            'Not sure if we need this!'
        );

        $this->router->map('/account/*', function($route) {
                echo $route->splat;
            },
            true);

        $this->request->url = '/account';
        $this->check();
    }

    // Test splat with named parameters
    public function testSplatNamedPlusWildcard() {
        $this->markTestSkipped(
            'Not sure if we need this!'
        );

        $this->router->map('/account/@name/*', function($name, $route) {
                echo $route->splat;
                $this->assertEquals('abc', $name);
            },
            true);

        $this->request->url = '/account/abc/456/def/xyz';
        $this->check('456/def/xyz');
    }
}

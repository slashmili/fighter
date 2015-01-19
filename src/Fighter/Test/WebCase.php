<?hh //partial

namespace Fighter\Test;

class WebCase extends \PHPUnit_Framework_TestCase {

    public function __construct(?string $name = null, array $data = array(), string $dataName = '') {
        parent::__construct($name, $data, $dataName);
        putenv('FIGHTER_MUTE=1');
    }

    public function __destruct() {
        putenv('FIGHTER_MUTE');
    }

    public function createClient(\Fighter\Application $app, Map<string, string> $server = Map {}): Client {
        return new Client($app, $server);
    }

    public function hasRoute(string $route, \Fighter\Application $app, string $message = '', Map<string, string> $server = Map {}): void {
        $request = new \Fighter\Net\Request();
        list($request->method, $request->url) = $this->getRequestAndMethod($route);
        $selected_route = $app->router->route($request);
        $found_route = false;
        if ($selected_route) {
            $found_route = true;
        }
        $this->assertTrue($found_route, $message);
    }

    private function getRequestAndMethod(string $route): array<string> {
        $match = [];
        if (preg_match("/(GET|POST|PUT|DELETE) (.+)/", $route, $match)) {
            return [$match[1], $match[2]];

        }
        return ['GET' ,$route];
    }
}

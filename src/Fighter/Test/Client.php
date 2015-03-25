<?hh //strict
namespace Fighter\Test;

class Client {

    public function __construct(private \Fighter\Application $app, private Map<string, string> $server) {
    }

    public function request(
        string $route,
        Map<string, mixed> $parameters = Map {},
        Map<string, string> $files = Map {},
        Map<string, string> $server = Map {},
        ?string $content = null,
        bool $changeHistory = true
    ): void {
        list($method, $url) = $this->getRequestAndMethod($route);
        $get = $method == 'GET' ? $parameters : Map {};
        $post = $method == 'POST' ? $parameters : Map {};
        $request = new \Fighter\Net\Request(
            shape('method' => $method, 'url' => $url),
            Map {},
            $get,
            $post
        );
        $this->app->run($request);
    }

    public function getResponse(): \Fighter\Net\Response {
        return $this->app->getResponse();
    }

    private function getRequestAndMethod(string $route): Vector<string> {
        $match = [];
        if (preg_match("/(GET|POST|PUT|DELETE|OPTIONS|HEAD) (.+)/", $route, $match)) {
            return Vector {$match[1], $match[2]};
        }
        return Vector {'GET', $route};
    }

    public function routeExists(string $route): bool {
        $request = new \Fighter\Net\Request();
        list($request->method, $request->url) = $this->getRequestAndMethod($route);
        $selected_route = $this->app->router->route($request);
        return (bool) $selected_route;
    }
}

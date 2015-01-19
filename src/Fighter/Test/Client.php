<?hh //strict
namespace Fighter\Test;

class Client {

    public function __construct(private \Fighter\Application $app, private Map<string, string> $server) {
    }

    public function request (
        string $route,
        Map<string, mixed> $parameters = Map {},
        Map<string, string> $files = Map{},
        Map<string, string> $server = Map{},
        ?string $content = null,
        bool $changeHistory = true
    ): void {
        $request = new \Fighter\Net\Request();
        list($request->method, $request->url) = $this->getRequestAndMethod($route);
        $this->app->run($request);
    }

    public function getResponse(): \Fighter\Net\Response {
        return $this->app->getResponse();
    }

    private function getRequestAndMethod(string $route): array<string> {
        $match = [];
        if (preg_match("/(GET|POST|PUT|DELETE) (.+)/", $route, $match)) {
            return [$match[1], $match[2]];

        }
        return ['GET' ,$route];
    }

    public function isRouteExists(string $route): bool {
        $request = new \Fighter\Net\Request();
        list($request->method, $request->url) = $this->getRequestAndMethod($route);
        $selected_route = $this->app->router->route($request);
        if ($selected_route) {
            return true;
        }
        return false;
    }

}

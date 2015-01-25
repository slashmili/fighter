<?hh //strict

namespace Fighter\Net;

class Router {

    private Vector<Route> $routes = Vector{};
    private int $index = 0;

    public function map(string $pattern, mixed $callback, bool $pass_route = false): void {
        $pattern = trim($pattern);
        $url = $pattern;
        $methods = Vector{'*'};

        if (strpos($pattern, ' ') !== false) {
            list($method, $url) = explode(' ', $pattern, 2);

            $methods = new Vector(explode('|', $method));
        }

        $this->routes[] = new Route(trim($url), $callback, $methods, $pass_route);
    }

    public function route(Request $request): ?Route {
        while ($route = $this->current()) {
            if ($route->matchMethod($request->method) && $route->matchUrl($request->url)) {
                return $route;
            }
            $this->next();
        }

        return null;
    }

    public function current(): ?Route {
        return $this->routes->get($this->index) ? $this->routes[$this->index] : null;
    }

    private function next(): void {
        $this->index++;
    }

}

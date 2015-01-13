<?hh //strict
namespace Fighter;

class Application {
    public Net\Router $router;
    public ?Net\Response $response;

    public function __construct() {
        $this->router = new Net\Router();
    }

    public function route(string $path, mixed $func): void {
        $this->router->map($path, $func);
    }

    public function run(?Net\Request $request = null) : void {
        if (is_null($request)) {
            $request = new Net\Request();
        }
        $route = $this->router->route($request);
        if ($route) {
            $params = array_values($route->params);
            try {
                $this->response = new Net\Response(call_user_func_array($route->callback, $params));
            } catch (\Exception $e) {
                $res = new Net\Response("Internal Server");
                $res->setStatus(500);
                $res->setBody((string) $e);
                $this->response = $res;
            }
        }
        if (is_null($this->response)) {
            $this->response = $this->notFound();
        }
        $this->flush();
    }

    public function getResponse(): Net\Response {
        if ($this->response) {
            return $this->response;
        }

        return $this->notFound();
    }

    public function flush(): void {
        if (getenv('FIGHTER_ENV') === 'test') {
            return;
        }
        if (is_null($this->response)) {
            $this->notFound()->flush();
            return;
        }
        $this->response->flush();
    }

    public function notFound(): Net\Response {
        $response = new Net\Response("Not Found");
        $response->setStatus(404);
        return $response;
    }
}

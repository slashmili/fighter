<?hh //strict
namespace Fighter;

class Application {
    use Util\Binder;

    public Net\Router $router;
    public ?Net\Response $response;
    public Net\Request $request;
    public bool $mute = false;
    public Map<string, mixed> $var = Map {};

    public function __construct() {
        $this->router = new Net\Router();
        $this->request = new Net\Request();
        $this->mute = (bool) getenv('FIGHTER_MUTE');
    }

    public function route(string $path, mixed $func): void {
        $this->router->map($path, $func);
    }

    public function run(?Net\Request $request = null) : void {
        $this->request = is_null($request)? new Net\Request() :  $request;
        $route = $this->router->route($request);
        if ($route) {
            $params = array_values($route->params);
            $params[] = $this;
            try {
                $this->response = new Net\Response(call_user_func_array($route->callback, $params));
            } catch (\Exception $e) {
                $res = new Net\Response("Internal Server");
                $res->setStatus(500);
                $res->setBody((string) $e);
                $this->response = $res;
            }
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
        if ($this->mute) return;
        $this->getResponse()->flush();
    }

    public function notFound(): Net\Response {
        $response = new Net\Response("Not Found");
        $response->setStatus(404);
        return $response;
    }
}

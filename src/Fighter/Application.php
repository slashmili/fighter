<?hh //strict
namespace Fighter;

use Fighter\Net\Request;
use Fighter\Net\Response;
use Fighter\Core\Dispatcher;

class Application {
    use Util\Binder;

    public Net\Router $router;
    public ?Net\Response $response;
    public ?Net\Request $request;
    public bool $mute = false;
    public Map<string, mixed> $var = Map {};
    protected Core\Dispatcher $dispatcher;

    public function __construct() {
        $this->router = new Net\Router();
        $this->dispatcher = new Dispatcher();
        $this->mute = (bool) getenv('FIGHTER_MUTE');
        $this->initEvents();
    }

    private function initEvents()
    {
        $this->dispatcher->reset();
        $events = ['start', 'route', 'stop', 'error', 'notFound', 'shutdown'];
        foreach ($events as $event) {
            $this->dispatcher->addEvent($event, [$this, 'on' . ucfirst($event) . 'Handler']);
        }
    }

    public function route(string $path, mixed $func): void {
        $this->router->map($path, $func);
    }

    public function run(?Request $request = null) : void {
        $this->request = $request ? : new Request();
        $this->dispatcher->dispatch('start', Vector { $request });
        $this->dispatcher->dispatch('shutdown');
    }

    public function getResponse(): Net\Response {
        return $this->response ? : $this->getNotFoundResponse();
    }

    private function getNotFoundResponse(int $code = 404, string $message = 'Not Found'): Response {
        $response = new Response($message);
        $response->setStatus($code);
        return $response;
    }

    private function getErrorResponse(int $code = 500, string $message = 'Internal Server'): Response {
        $response = new Response($message);
        $response->setStatus($code);
        return $response;
    }

    protected function flushResponse(Response $response) : bool {
        if ($this->mute) return false;
        $response->flush();
        return true;
    }

    public function onStartHandler(Request $request) : void {
        $this->request = $request ? : new Net\Request();
        $this->dispatcher->dispatch('route', Vector { $request });
    }

    public function onRouteHandler(Request $request): void {
        $route = $this->router->route($request);
        if (!$route) {
            $this->response = $this->getNotFoundResponse();
            $this->dispatcher->dispatch('notFound', Vector { $request });
            return;
        }

        $params = $route->params->values();
        $params[] = $this;
        try {
            $this->response = new Response(call_user_func_array($route->callback, $params));
        } catch (\Exception $exp) {
            $this->response = $this->getErrorResponse();
            $this->dispatcher->dispatch('error', Vector { $exp });
        }
        $this->dispatcher->dispatch('stop', Vector { $this->response });
    }

    public function onStopHandler(Response $response) : void {
        $this->flushResponse($response);
    }

    public function onErrorHandler(\Exception $exp): void {
        $response = $this->response ? : $this->getErrorResponse();
        $response->setBody((string) $exp);
        $this->flushResponse($response);
    }

    public function onNotFoundHandler(Request $request) : void {
        $response = $this->response ? : $this->getNotFoundResponse();
        $this->flushResponse($response);
    }

    public function onShutdownHandler() : void {
    }
}

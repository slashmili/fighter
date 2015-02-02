<?hh //strict
namespace Fighter;

use Fighter\Net\Request;
use Fighter\Net\Response;
use Fighter\Net\Router;
use Fighter\Core\Dispatcher;

class Application {
    use Util\Binder;

    public Router $router;
    public ?Response $response;
    public ?Request $request;
    public bool $mute = false;
    public Map<string, mixed> $var = Map {};
    protected Core\Dispatcher $dispatcher;
    private Vector<string> $events = Vector {'start', 'route', 'stop', 'error', 'notFound', 'shutdown'};

    public function __construct() {
        $this->router = new Router();
        $this->dispatcher = new Dispatcher();
        $this->mute = (bool) getenv('FIGHTER_MUTE');
        $this->initEventHandlers();
        $this->initEvents();
    }

    private function getEventHandlerName(string $event) : string {
        return $event . 'Handler';
    }

    public function initEventHandlers() : this {
        foreach ($this->events as $event) {
            $handlerName = $this->getEventHandlerName($event);
            $this->bind($handlerName, [$this, 'default' . ucfirst($handlerName)]);
        }
        return $this;
    }

    private function initEvents() : this {
        $this->dispatcher->reset();
        foreach ($this->events as $event) {
            $this->dispatcher->addEvent($event, [$this, $this->getEventHandlerName($event)]);
        }
        return $this;
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

    protected function getNotFoundResponse(int $code = 404, string $message = 'Not Found'): Response {
        $response = new Response($message);
        $response->setStatus($code);
        return $response;
    }

    protected function getErrorResponse(int $code = 500, string $message = 'Internal Server'): Response {
        $response = new Response($message);
        $response->setStatus($code);
        return $response;
    }

    protected function flushResponse(Response $response) : bool {
        if ($this->mute) return false;
        $response->flush();
        return true;
    }

    public function defaultStartHandler(Request $request) : void {
        $this->request = $request ? : new Net\Request();
        $this->dispatcher->dispatch('route', Vector { $request });
    }

    public function defaultRouteHandler(Request $request): void {
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

    public function defaultStopHandler(Response $response) : void {
        $this->flushResponse($response);
    }

    public function defaultErrorHandler(\Exception $exp): void {
        $response = $this->response ? : $this->getErrorResponse();
        $response->setBody((string) $exp);
        $this->flushResponse($response);
    }

    public function defaultNotFoundHandler(Request $request) : void {
        $response = $this->response ? : $this->getNotFoundResponse();
        $this->flushResponse($response);
    }

    public function defaultShutdownHandler() : void {
    }

    public function bindErrorHandler((function(\Exception) : void) $func) : this {
        $this->bind($this->getEventHandlerName('error'), $func);
        return $this;
    }

    public function bindNotFoundHandler((function(Request) : void) $func) : this {
        $this->bind($this->getEventHandlerName('notFound'), $func);
        return $this;
    }

    public function bindShutdownHandler((function() : void) $func) : this {
        $this->bind($this->getEventHandlerName('shutdown'), $func);
        return $this;
    }

    public function hookBeforeStart((function(Request): void) $func) : this {
        $this->dispatcher->addHookBeforeEvent('start', $func);
        return $this;
    }

    public function hookAfterStart((function(Request): void) $func) : this {
        $this->dispatcher->addHookAfterEvent('start', $func);
        return $this;
    }

    public function hookBeforeRoute((function(Request): void) $func) : this {
        $this->dispatcher->addHookBeforeEvent('route', $func);
        return $this;
    }

    public function hookAfterRoute((function(Request): void) $func) : this {
        $this->dispatcher->addHookAfterEvent('route', $func);
        return $this;
    }

    public function hookBeforeStop((function(Response): void) $func) : this {
        $this->dispatcher->addHookBeforeEvent('stop', $func);
        return $this;
    }

    public function hookAfterStop((function(Response): void) $func) : this {
        $this->dispatcher->addHookAfterEvent('stop', $func);
        return $this;
    }

    public function hookBeforeNotFound((function(Request): void) $func) : this {
        $this->dispatcher->addHookBeforeEvent('notFound', $func);
        return $this;
    }

    public function hookAfterNotFound((function(Request): void) $func) : this {
        $this->dispatcher->addHookAfterEvent('notFound', $func);
        return $this;
    }

    public function hookBeforeError((function(\Exception): void) $func) : this {
        $this->dispatcher->addHookBeforeEvent('error', $func);
        return $this;
    }

    public function hookAfterError((function(\Exception): void) $func) : this {
        $this->dispatcher->addHookAfterEvent('error', $func);
        return $this;
    }
}

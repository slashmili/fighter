<?hh //strict
namespace Fighter\Test;

class Client {

    public function __construct(private \Fighter\Application $app, private Map<string, string> $server) {
    }


    public function request (
        string $method,
        string $uri,
        Map<string, mixed> $parameters = Map {},
        Map<string, string> $files = Map{},
        Map<string, string> $server = Map{},
        ?string $content = null,
        bool $changeHistory = true
    ): void {
        $request = new \Fighter\Net\Request();
        $request->url = $uri;
        $this->app->run();
    }

    public function getResponse(): \Fighter\Net\Response {
        return $this->app->getResponse();
    }
}

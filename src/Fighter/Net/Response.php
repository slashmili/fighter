<?hh //strict

namespace Fighter\Net;

class Response {
    use Http;

    public string $type = '';
    protected int $status = 200;
    public Map<string, string> $headers = Map{};
    public string $body = '';



    private ResponseInterface<mixed> $response;

    public function __construct(private mixed $raw) {
        $this->detectResponse($raw);
    }

    private function detectResponse(mixed $raw): void {
        $this->response = new Response\Text($raw);
        if ($this->doesAcceptJson($this->getServerParams())) {
            $this->response = new Response\Json($raw);
        } elseif (is_string($raw)) {
            $this->response = new Response\Text($raw);
        } else {
            $this->response = new Response\Json($raw);
        }

        $this->type = $this->response->type;
    }

}

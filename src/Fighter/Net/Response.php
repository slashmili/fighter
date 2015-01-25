<?hh //strict

namespace Fighter\Net;

class Response {
    use Http;

    public string $type = '';
    protected int $status = 200;
    public Map<string, string> $headers = Map{};
    protected string $body = '';


    private ResponseInterface<mixed> $response;

    public function __construct(private mixed $raw) {
        $this->detectResponse($raw);
    }

    private function detectResponse(mixed $raw): void {
        $this->response = new Response\Text($raw);

        #TODO: improve $raw data detection
        if (is_null($raw)) {
            $this->response = new Response\Text('');
        } elseif ($raw instanceof Response\Json) {
            $this->response = $raw;
        } elseif ($raw instanceof Response\Text) {
            $this->response = $raw;
        } elseif ($this->doesAcceptJson($this->getServerParams())) {
            $this->response = new Response\Json($raw);
        } elseif (is_string($raw)) {
            $this->response = new Response\Text($raw);
        } else {
            $this->response = new Response\Json($raw);
        }

        $this->type = $this->response->type;
    }

    public function setStatus(?int $code = null): void {
        if (is_null($code)) {
            $code = 200;
            return;
        }
        $this->status = $code;
    }

    public function getStatus(): int {
        return $this->status;
    }

    public function addHeader(string $name, string $value): void {
        $this->headers[$name] = $value;
    }

    public function getHeaders(): Map<string, string> {
        return $this->headers;
    }

    public function __toString(): string {
        return (string) $this->response;
    }

    public function flush(): void {
        if(!headers_sent()) {
            $this->sendHeaders();
        }
        echo $this;
    }

    public function sendHeaders(): void {
        $h =sprintf("%s %d %s", $this->getHttpProtocol(), $this->status, Response\Status::$codes->get($this->status));
        header($h, true, $this->status);

        foreach ($this->headers as $field => $value) {
            header($field.': '.$value);
        }
    }

    public function setBody(string $body): void {
        $this->body = $body;
    }
}

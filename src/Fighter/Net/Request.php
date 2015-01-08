<?hh //strict

namespace Fighter\Net;

type RequestArg = shape(
    'url' => ?string,
    'base' => ?string,
    'method' => ?string,
    'referrer' => ?string,
    'ip' => ?string,
    'ajax' => ?string,
    'scheme' => ?string,
    'user_agent' => ?string,
    'type' => ?string,
    'length' => ?string,
    'query' => ?Map<string, string>,
    'data' => ?Map<string, string>,
    'cookies' => ?Map<string, string>,
    'files' => ?Map<string, string>,
    'secure' => ?bool,
    'accept' => ?string,
    'proxy_ip' => ?string
);

class Request {
    use Http;
    use \Fighter\Util\DynamicAssignment;

    public Map<string, mixed> $request = Map{};

    public string $url = "";
    public string $method = "";
    public string $base = "";
    public string $referrer = "";
    public string $scheme = "";
    public string $type = "";
    public string $accept = "";
    public string $ip = "";
    public string $proxy_ip = "";

    public int $length = 0;

    public bool $ajax = false;
    public bool $secure = false;


    #TODO: fix the first arg type
    public function __construct(
        private mixed $config = shape(),
        public Map<string, string> $server = Map{},
        public Map<string, mixed> $get = Map{},
        public Map<string, mixed> $post = Map{},
        public Map<string, mixed> $cookies = Map{},
        public Map<string, mixed> $files = Map{},
    ) {
        $this->setDefaultValues();
    }


    private function setDefaultValues(): void {
        if ($this->server->isEmpty()) {
            $this->server = $this->getServerParams();
        }

        if ($this->get->isEmpty()) {
            $this->get = $this->getGetParams($this->server);
        }

        if ($this->post->isEmpty()) {
            $this->post = $this->getPostParams();
        }

        if ($this->cookies->isEmpty()) {
            $this->cookies = $this->getCookiesParams();
        }

        if ($this->files->isEmpty()) {
            $this->files = $this->getFilesParams();
        }

        if ($this->request->isEmpty()) {
            $this->request = $this->getRequestParams();
        }

        $mc = array_merge(
            [
                'url' => $this->getHttpUri($this->server),
                'method' => $this->getHttpMethod($this->server, $this->request),
                'base' => $this->getHttpBase($this->server),
                'referrer' => $this->getHttpReferer($this->server),
                'ajax' => $this->getHttpIsAjax($this->server),
                'scheme' => $this->getHttpScheme($this->server),
                'length' => $this->getHttpHeaderLength($this->server),
                'secure' => $this->getHttpIsHttps($this->server),
                'accept' => $this->getHttpAccept($this->server),
                'ip' => $this->getHttpIp($this->server),
                'proxy_ip' => $this->getHttpProxyIp($this->server),
                'query' => $this->get,
                'data' => $this->post,
            ],
            $this->config
        );

        foreach($mc as $key => $value) {
            $this->setWithDynamicAssignment($key, $value);
        }
    }

}

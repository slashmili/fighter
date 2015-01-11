<?hh //partial

namespace Fighter\Net;

trait Http {
    public function getServerParams(): Map<string, string> {
        return Map::fromArray($_SERVER);
    }

    public function getGetParams(Map<string, string> $server = Map{}): Map<string, mixed> {
        $url = $this->getHttpUri($server);
        $extra = [];
        $args = Map::fromArray(parse_url($url));
        if ($query = $args->get('query')) {
            parse_str($query, $extra);
        }
        return Map::fromArray(array_merge($extra, $_GET));
    }

    public function getPostParams(): Map<string, mixed> {
        return Map::fromArray($_POST);
    }

    public function getCookiesParams(): Map<string, mixed> {
        return Map::fromArray($_COOKIE);
    }

    public function getFilesParams(): Map<string, mixed> {
        return Map::fromArray($_FILES);
    }

    public function getRequestParams(): Map<string, mixed> {
        return Map::fromArray($_REQUEST);
    }

    public function getHttpMethod(Map<string, string> $server = Map{}, Map<string, mixed> $request = Map{}): string {
        if ($server->get('HTTP_X_HTTP_METHOD_OVERRIDE')) {
            return (string)$server->get('HTTP_X_HTTP_METHOD_OVERRIDE');
        } elseif ($request->get('_method')) {
            return (string)$request->get('_method');
        }

        return $server->get('REQUEST_METHOD') ? : 'GET';
    }

    public function getHttpBase(Map<string, string> $server = Map {}): string {
        $script_name = $server->get('SCRIPT_NAME') ? : '/';
        return str_replace(array('\\',' '), array('/','%20'), dirname($script_name));
    }

    public function getHttpUri(Map<string, string> $server = Map{}): string {
        return $server->get('REQUEST_URI')? : '/';
    }

    public function getHttpReferer(Map<string, string> $server = Map{}): string {
        return (string)$server->get('HTTP_REFERER');
    }

    public function getHttpAccept(Map<string, string> $server = Map{}): string {
        return (string)$server->get('HTTP_ACCEPT');
    }

    public function getHttpIsAjax(Map<string, string> $server = Map {}): bool {
        if ($result = $server->get('HTTP_X_REQUESTED_WITH')) {
            return $result === 'XMLHttpRequest';
        }
        return false;
    }

    public function getHttpIsHttps(Map<string, string> $server = Map {}): bool {
        if ($result = $server->get('HTTPS')) {
            return $result === 'on';
        }
        return false;
    }

    public function getHttpScheme(Map<string, string> $server = Map{}): string {
        return $server->get('SERVER_PROTOCOL')? : 'HTTP/1.1';
    }

    public function getHttpIp(Map<string, string> $server = Map{}): string {
        return (string)$server->get('REMOTE_ADDR');
    }

    public function getHttpProxyIp(Map<string, string> $server = Map{}): string {
        #TODO: improve the proxy ip detection
        $forwarded = Vector{
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        };

        foreach ($forwarded as $k) {
            if ($result = $server->get($k)) {
                return $result;
            }
        }
        return '';
    }


    public function getHttpHeaderLength(Map<string, string> $server = Map{}): int {
        return (int)$server->get('CONTENT_LENGTH');
    }

    public function doesAcceptJson(Map<string, string> $server = Map{}): bool {
        return (bool) preg_match("#application/json#", $server->get('HTTP_ACCEPT'));
    }
}

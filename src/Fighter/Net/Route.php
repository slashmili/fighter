<?hh //strict

namespace Fighter\Net;

class Route {

    public Map<mixed, mixed> $params = Map{};
    public string $splat = '';
    public string $regex = '';
    private array<mixed, mixed> $ids = [];

    public function __construct(
        private string $pattern, public mixed $callback,
        private Vector<string>$methods, private bool $pass)
    {}

    public function matchMethod(string $method): bool {
        return count(array_intersect(array($method, '*'), $this->methods)) > 0;
    }

    public function matchUrl(string $url): bool {
        if ($this->pattern === '*' || $this->pattern === $url) {
            if ($this->pass) {
                //$this->params[] = $this;
                throw new \Exception("Nooop no pass route");
            }
            return true;
        }

        $this->ids = array();
        $last_char = substr($this->pattern, -1);
        if ($last_char === '*') {
            $n = 0;
            $len = strlen($url);
            $count = substr_count($this->pattern, '/');
            for ($i = 0; $i < $len; $i++) {
                if ($url[$i] == '/') $n++;
                if ($n == $count) break;
            }

            $this->splat = (string)substr($url, $i+1);
        }

        $regex = str_replace(array(')','/*'), array(')?','(/?|/.*?)'), $this->pattern);

        $regex = preg_replace_callback(
            '#@([\w]+)(:([^/\(\)]*))?#',
            ($matches) ==> {
                $matches = new Vector($matches);
                #FIXME: $ids should be here with refrence!
                $this->ids[$matches[1]] = null;
                if ($matches->get(3)) {
                    return '(?P<'.$matches[1].'>'.$matches[3].')';
                }
                return '(?P<'.$matches[1].'>[^/\?]+)';
            },
            $regex
        );

        // Fix trailing slash
        if ($last_char === '/') {
            $regex .= '?';
        }
        // Allow trailing slash
        else {
            $regex .= '/?';
        }

        $matches = [];
        // Attempt to match route and named parameters
        if (preg_match('#^'.$regex.'(?:\?.*)?$#i', $url, $matches)) {
            foreach ($this->ids as $k => $v) {
                 $this->params[$k] = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
            }

            if ($this->pass) {
                //$this->params[] = $this;
                throw new \Exception("Nooop no pass route");
            }

            $this->regex = $regex;
            return true;
        }
        return false;
    }
}

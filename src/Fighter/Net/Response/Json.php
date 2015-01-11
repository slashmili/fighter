<?hh

namespace Fighter\Net\Response;

class Json<T> implements \Fighter\Net\ResponseInterface<T> {
    public string $type ='application/json';

    public function __construct(private T $raw) {
    }

    public function __toString(): string {
        return json_encode($this->raw);
    }
}

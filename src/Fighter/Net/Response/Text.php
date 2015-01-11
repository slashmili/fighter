<?hh

namespace Fighter\Net\Response;

class Text<T> implements \Fighter\Net\ResponseInterface<T> {
    public string $type ='text/html';

    public function __construct(private T $raw) {
    }

    public function __toString(): string {
        return (string) $this->raw;
    }
}

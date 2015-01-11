<?hh
namespace Fighter\Net;

interface ResponseInterface<T> {
    public function __construct(T $raw);

    public function __toString(): string;
}

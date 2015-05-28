<?hh // strict

namespace Fighter\Util;

class Bucket
{
    protected Map<string, mixed> $data = Map {};
    protected Map<string, mixed> $provider = Map {};

    public function __construct(?KeyedTraversable<string, mixed> $it=null)
    {
        $this->data = Map::fromArray($it);
    }

    public function set(string $key, mixed $value) : this
    {
        $this->data->set($key, $value);
        return $this;
    }

    /**
     * @throws \InvalidArgumentException on not callable provider
     * @throws \LogicException on registring with a key that is already set
     */
    public function registerProvider(string $key, mixed $callable) : this
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Can not register provider for '$key'. Provider is not callable");
        }
        if ($this->data->contains($key)) {
            throw new \LogicException("A value for key '$key' has already been set. Provider will not be used");
        }
        $this->provider[$key] = $callable;
        return $this;
    }

    public function contains(string $key) : bool
    {
        return $this->data->contains($key) || $this->provider->contains($key);
    }

    public function keys() : Vector<string>
    {
        return $this->data->keys()->addAll($this->provider->keys());
    }

    public function get(string $key) : mixed
    {
        if (!$this->data->contains($key) && $this->provider->contains($key)) {
            $provider = $this->provider[$key];
            if (!is_callable($provider)) {
                throw new \UnexpectedValueException("Provider is not a callable for key '$key'");
            }
            // UNSAFE
            $this->data[$key] = call_user_func($provider);
        }
        return $this->data->get($key);
    }

    public function remove(string $key) : this
    {
        $this->data->remove($key);
        return $this;
    }

    public function clear() : this
    {
        $this->data->clear();
        return $this;
    }

    public function toMap() : Map<string, mixed>
    {
        return $this->data->toMap();
    }
}

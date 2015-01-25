<?hh //strict

namespace Fighter\Util;

trait Binder {
    protected Map<string, mixed> $core_binds = Map {};

    /**
     * @throws \InvalidArgumentException on not callable bind
     */
    public function bind(string $name, mixed $bind): this {
        if (!is_callable($bind)) {
            throw new \InvalidArgumentException("$name is not callable");
        }
        $this->core_binds[$name] = $bind;
        return $this;
    }

    public function __call(string $name, array<mixed> $params): mixed {
        $callback = $this->core_binds->get($name);

        if($callback === null ) {
            $last_call = current(debug_backtrace());
            trigger_error(
                sprintf(
                    "Fatal error: Call to undefined method %s::%s() in %s on line %s",
                    get_class($this),
                    $name,
                    $last_call['file'],
                    $last_call['line']
                ),
                E_USER_ERROR
            );
        }
        return call_user_func_array($callback, $params);
    }
}

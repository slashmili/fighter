<?hh // partial

namespace Fighter\Core;

class Dispatcher {

    protected Map<string, (function() : void)> $events = Map {};
    protected Map<string, Map<string, Vector<(function() : void)>>> $hooks = Map {};

    /**
     * @throws \LogicException if no event has been registered
     */
    public function dispatch(string $event, Vector<mixed> $params = Vector {}) : mixed {

        if ($this->hooks[$event]->contains('before')) {
            $this->applyEventHooks('before', $event, $params);
        }

        $output = call_user_func_array($this->getEvent($event), $params);

        if ($this->hooks[$event]->contains('after')) {
            $this->applyEventHooks('after', $event, $params);
        }

        return $output;
    }

    public function addEvent(string $event, (function () : void) $handler) : this {
        $this->events[$event] = $handler;
        $this->hooks[$event] = Map {
            'before' => Vector {},
            'after' => Vector {}
        };
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getEvent(string $event) : (function() : void) {
        if (!$this->events->contains($event)) {
            throw new \InvalidArgumentException("No event is registered for '$event'");
        }
        return $this->events[$event];
    }

    public function hasEvent(string $event) : bool {
        return $this->events->contains($event);
    }

    public function clearEvent(string $event) : this {
        $this->events->remove($event);
        $this->hooks->remove($event);
        return $this;
    }

    public function addHookBeforeEvent(string $event, (function() : void) $callback) : this {
        $this->hooks[$event]['before'][] = $callback;
        return $this;
    }

    public function addHookAfterEvent(string $event, (function() : void) $callback) : this {
        $this->hooks[$event]['after'][] = $callback;
        return $this;
    }

    public function clearEventHooks(string $event) : this {
        $this->hooks[$event] = Map {
            'before' => Vector {},
            'after' => Vector {}
        };
        return $this;
    }

    public function reset() : this {
        $this->events = Map {};
        $this->hooks = Map {};
        return $this;
    }

    protected function addHook(string $event, string $type, (function () : void) $callback) : this {
        if (!$this->hooks[$event]->contains($type)) {
            $this->hooks[$event][$type] = Vector {};
        }
        $this->hooks[$event][$type][] = $callback;
        return $this;
    }

    /**
     * @throws \LogicException
     */
    protected function applyEventHooks(string $type, string $event, Vector<mixed> $params) : this {
        if (!$this->hooks->contains($event)) {
            throw new \LogicException("Failed to find hooks for event '$event'");
        }
        if (!$this->hooks[$event]->contains($type)) {
            throw new \LogicException("No hook '$type' is registered for event '$event'");
        }
        $hooks = $this->hooks[$event][$type];
        foreach ($hooks as $func) {
            call_user_func_array($func, $params);
        }
        return $this;
    }
}

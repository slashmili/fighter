<?hh // partial

namespace Fighter\Core;

class Dispatcher {

    protected Map<string, mixed> $events = Map {};
    protected Map<string, Map<string, Vector<mixed>>> $hooks = Map {};

    /**
     * @throws \LogicException if no event has been registered
     */
    public function dispatch(string $event, Vector<mixed> $params = Vector {}) : mixed {

        if (!$this->events->contains($event)) {
            throw new \LogicException("No event is registered for '$event'");
        }

        if ($this->hooks[$event]->contains('before')) {
            $this->applyEventHooks('before', $event, $params);
        }

        $output = call_user_func_array($this->getEvent($event), $params);

        if ($this->hooks[$event]->contains('after')) {
            $this->applyEventHooks('after', $event, $params);
        }

        return $output;
    }

    public function addEvent(string $event, $handler) : this {
        $this->events[$event] = $handler;
        $this->hooks[$event] = Map {
            'before' => Vector {},
            'after' => Vector {}
        };
        return $this;
    }

    /**
     * @throws \InvalidArgumentException on not registered event
     */
    public function getEvent(string $event) : mixed {
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

    /**
     * @throws \LogicException on not registered event
     * @throws \InvalidArgumentException on not callable hook
     */
    public function addHookBeforeEvent(string $event, $callback) : this {
        if (!$this->events->contains($event)) {
            throw new \LogicException("Can not add hooks for a not registered event '$event'");
        }
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Event hook should be callable");
        }
        $this->hooks[$event]['before'][] = $callback;
        return $this;
    }

    /**
     * @throws \LogicException on not registered event
     * @throws \InvalidArgumentException on not callable hook
     */
    public function addHookAfterEvent(string $event, $callback) : this {
        if (!$this->events->contains($event)) {
            throw new \LogicException("Can not add hooks for a not registered event '$event'");
        }
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Event hook should be callable");
        }
        $this->hooks[$event]['after'][] = $callback;
        return $this;
    }

    /**
     * @throws \InvalidArgumentException not registered event
     */
    public function getEventHooks(string $event) : Pair<Vector<mixed>, Vector<mixed>> {
        if (!$this->events->contains($event)) {
            throw new \InvalidArgumentException("No event is registered for '$event'");
        }
        $hooks = $this->hooks[$event];
        return Pair { $hooks['before'], $hooks['after'] };
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

<?hh //partial

namespace Fighter\Util;

trait DynamicAssignment {
    public function setWithDynamicAssignment(string $key, mixed $value): void {
        $this->$key = $value;
    }
}

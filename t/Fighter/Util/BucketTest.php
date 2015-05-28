<?hh //partial

use Fighter\Util\Bucket;

class BucketTest extends \PHPUnit_Framework_TestCase
{
    public function testSetContainsGet(): void
    {
        $bucket = new Bucket();
        $this->assertFalse($bucket->contains('name'));
        $bucket->set('name', 'test');
        $this->assertTrue($bucket->contains('name'));
        $this->assertEquals('test', $bucket->get('name'));
        $this->assertNull($bucket->get('something that is not there'));
    }

    public function testRemove(): void
    {
        $bucket = new Bucket();
        $this->assertTrue($bucket->set('drink', 'coffee')->contains('drink'));
        $this->assertFalse($bucket->remove('drink')->contains('drink'));
    }

    public function testToMap() : void
    {
        $bucket = new Bucket();
        $bucket->set('read', 'books')->set('walk in', 'nature');
        $this->assertEquals(Map {'read' => 'books', 'walk in' => 'nature'}, $bucket->toMap());
    }

    /**
     * @depends testSetContainsGet
     */
    public function testKeys() : void
    {
        $bucket = new Bucket();
        $bucket->set('love', 'family')->set('also', 'friends');
        $this->assertEquals(Vector {'love', 'also'}, $bucket->keys());
    }

    /**
     * @depends testKeys
     */
    public function testClear(): void
    {
        $bucket = new Bucket();
        $bucket->set('drink', 'tea')->set('eat', 'healthy');
        $this->assertEmpty($bucket->clear()->keys());
    }

    /**
     * @depends testKeys
     */
    public function testRegisterProvider(): void
    {
        $bucket = new Bucket();
        $bucket->set('a key', 'a value');
        $called = Vector {};
        $bucket->registerProvider(
            'fetch',
            () ==> {
                $called->add(true);
                return 'fetched!';
            }
        );
        $this->assertTrue($bucket->contains('fetch'));
        $this->assertEquals(Vector {'a key', 'fetch'}, $bucket->keys());
        $this->assertEquals('fetched!', $bucket->get('fetch'));
        $this->assertCount(1, $called);
    }
}

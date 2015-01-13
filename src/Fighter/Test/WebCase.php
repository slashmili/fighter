<?hh //partial
namespace Fighter\Test;

class WebCase extends \PHPUnit_Framework_TestCase {

    public function __construct(?string $name = null, array $data = array(), string $dataName = '') {
        parent::__construct($name, $data, $dataName);
        putenv('FIGHTER_MUTE=1');
    }

    public function __destruct() {
        putenv('FIGHTER_MUTE');
    }

    public function createClient(\Fighter\Application $app, Map<string, string> $server = Map {}): Client {
        return new Client($app, $server);
    }
}

<?hh //partial

require_once('AppProvider.php');

class ApplicationTest extends \Fighter\Test\WebCase {

    public function testExternalFileRouting() {
        $app = AppProvider::singleDefaultRoute();
        $client = $this->createClient($app);

        $client->request('GET', '/');

        $this->assertEquals(
            'Hello World with one route',
            $client->getResponse()
        );

        $client->request('GET', '/foo');

        $this->assertEquals(
            'bar',
            $client->getResponse()
        );

    }

    public function testNotFound404() {
        $app = new Fighter\Application();

        $client = $this->createClient($app);
        $client->request('GET', '/');

        $this->assertEquals(
            404,
            $client->getResponse()->getStatus()
        );
    }


    public function testInternalServr500() {
        $app = new Fighter\Application();
        $app->route('/', () ==> {throw new \Exception("Errorrrrrr");});

        $client = $this->createClient($app);
        $client->request('GET', '/');

        $this->assertEquals(
            500,
            $client->getResponse()->getStatus()
        );
    }


    public function testAppWithFlush() {
        $app = new Fighter\Application();
        $app->mute = false;
        $client = $this->createClient($app);

        ob_start();
        $client->request('GET', '/');
        ob_end_clean();

        $response = $client->getResponse();
    }
}

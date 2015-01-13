<?hh //partial

class ApplicationTest extends \Fighter\Test\WebCase {

    public function testExternalFileRouting() {
        $app = require __DIR__ . '/ApplicationTest/app_with_default_route.php';
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
        putenv('FIGHTER_ENV');
        $app = new Fighter\Application();

        $client = $this->createClient($app);
        ob_start();
        $client->request('GET', '/');
        ob_end_clean();

        $this->assertEquals(
            404,
            $client->getResponse()->getStatus()
        );
    }

    public function testWithCallingRunDirectly() {
        putenv('FIGHTER_ENV');
        $app = new Fighter\Application();
        $app->route('/', () ==> {throw new \Exception("Errorrrrrr");});

        ob_start();
        $app->run();
        ob_end_clean();

        $this->assertEquals(
            500,
            $app->getResponse()->getStatus()
        );
    }
}

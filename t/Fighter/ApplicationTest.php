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

    public function testAppWithVariable() {
        $app = new Fighter\Application();

        $app->var->set('id', 1);
        $this->assertEquals(1, $app->var->get('id'));

        $this->assertTrue($app->var->contains('id'));

        $app->var->clear();
        $this->assertFalse($app->var->contains('id'));

    }

    public function testAppWithRouteParamAndAppAsLastParam() {
        $app = new Fighter\Application();
        $app->route('GET /user/@id', ($id, $app) ==> {
            $this->assertInstanceOf('\Fighter\Application', $app);
            return $id;
        });
        $client = $this->createClient($app);

        $client->request('GET', '/user/10');

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
    }

    public function testAppWithRouteParamWithoutApps() {
        $app = new Fighter\Application();
        $app->route('GET /user/@id', ($id) ==> $id);
        $client = $this->createClient($app);

        $client->request('GET', '/user/10');

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
    }
}

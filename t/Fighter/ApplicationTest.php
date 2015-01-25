<?hh //partial

require_once('AppProvider.php');

class ApplicationTest extends \Fighter\Test\WebCase {

    public function testHasRoutes() {
        $app = AppProvider::singleDefaultRoute();
        $this->hasRoute('/', $app, 'Should have / route');
        $this->hasRoute('/foo', $app, 'Should have /foo route');
        $this->hasRoute('GET /foo', $app, 'Should have GET /foo route');
    }


    /**
     *  This route doesn't exist
     *  @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testDoesntHaveRouteSimple() {
        $app = AppProvider::singleDefaultRoute();
        $this->hasRoute('/bar', $app);
    }

    /**
     *  This route doesn't exist
     *  @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testDoesntHaveRouteWithMethod() {
        $app = AppProvider::singleDefaultRoute();
        $this->hasRoute('POST /bar', $app);
    }


    public function testExternalFileRouting() {
        $app = AppProvider::singleDefaultRoute();
        $client = $this->createClient($app);

        $client->request('/');

        $this->assertEquals(
            'Hello World with one route',
            $client->getResponse()
        );

        $client->request('/foo');

        $this->assertEquals(
            'bar',
            $client->getResponse()
        );

    }

    public function testNotFound404() {
        $app = new Fighter\Application();

        $client = $this->createClient($app);
        $client->request('GET /');

        $this->assertEquals(
            404,
            $client->getResponse()->getStatus()
        );
    }


    public function testInternalServr500() {
        $app = new Fighter\Application();
        $app->route('/', () ==> {throw new \Exception("Errorrrrrr");});

        $client = $this->createClient($app);
        $client->request('GET /');

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
        $client->request('GET /');
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

    public function testAppWithMethodBinding() {
        $app = new Fighter\Application();
        $app->bind('return_foo', () ==> 'foo');
        $app->route('/foo', () ==> $app->return_foo());

        $client = $this->createClient($app);

        $client->request('GET /foo');

        $this->assertEquals(
            'foo',
            $client->getResponse()
        );
    }

}

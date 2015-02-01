<?hh //partial

require_once('AppProvider.php');

use Fighter\Net\Request;

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

    public function testAppWithRouteParamAndAppAsLastParam() {
        $app = new Fighter\Application();
        $app->route('GET /user/@id', ($id, $app) ==> {
            $this->assertInstanceOf('\Fighter\Application', $app);
            return $id;
        });
        $client = $this->createClient($app);

        $client->request('GET /user/10');

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
    }

    public function testAppWithRouteParamWithoutApps() {
        $app = new Fighter\Application();
        $app->route('GET /user/@id', ($id) ==> $id);
        $client = $this->createClient($app);

        $client->request('GET /user/10');

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
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

    public function testAppWithGetParam() {
        $app = new Fighter\Application();
        $app->route('GET /user', ($app) ==> $app->request->get->get('id'));
        $client = $this->createClient($app);

        $client->request('GET /user', Map {'id' => 10});

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
    }

    public function testAppWithPostParam() {
        $app = new Fighter\Application();
        $app->route('POST /user', ($app) ==> $app->request->post->get('id'));
        $client = $this->createClient($app);

        $client->request('POST /user', Map {'id' => 11});

        $this->assertEquals(
            '11',
            $client->getResponse()
        );
    }

    public function testAppWithNoReturn() {
        $app = new Fighter\Application();
        $app->route('POST /user', () ==> {});
        $client = $this->createClient($app);

        $client->request('POST /user');

        $this->assertEquals(
            '',
            $client->getResponse()
        );
    }

    public function testBindErrorHandler() {
        $app = new Fighter\Application();
        $errors = Vector {};
        $app->route('/', () ==> {throw new \Exception("I want error");});
        $app->route('/other', () ==> {throw new \Exception("I always want error");});
        $app->bindErrorHandler((\Exception $e) ==> { $errors[] = $e; });
        $client = $this->createClient($app);
        $client->request('GET /');
        $client->request('GET /other');
        $this->assertEquals(
            Vector {
                new \Exception("I want error"),
                new \Exception("I always want error")
            },
            $errors
        );
    }

    public function testBindNotFoundHandler() {
        $routed = Vector {};
        $notFound = Vector {};
        $app = new Fighter\Application();
        $app->bindNotFoundHandler((Request $req) ==> { $notFound[] = $req; });
        $app->route('/', () ==> {$routed[] = '/';});
        $client = $this->createClient($app);
        $client->request('GET /');
        $this->assertEmpty($notFound);
        $this->assertCount(1, $routed);

        $routedBeforeOther = $routed->toVector();
        $client->request('GET /other');
        $this->assertEquals($routedBeforeOther, $routed);
        $this->assertCount(1, $notFound);
        $this->assertInstanceOf('Fighter\Net\Request', $notFound[0]);
    }

    public function testBindShutdownHandler() {
        $shutdowns = Vector {};
        $routed = Vector {};

        $app = new Fighter\Application();
        $app->bindShutdownHandler(() ==> { $shutdowns[] = true; });
        $app->route('/', () ==> {$routed[] = '/';});

        $client = $this->createClient($app);
        $client->request('GET /');
        $client->request('GET /other');

        $this->assertEquals(Vector {'/'}, $routed);
        $this->assertEquals(
            404,
            $client->getResponse()->getStatus()
        );
        $this->assertEquals(Vector {true, true}, $shutdowns);
    }

    public function testHookForStart() {
        $app = new Fighter\Application();
        $beforeCalled = Vector {};
        $afterCalled = Vector {};
        $app->hookBeforeStart((Request $req) ==> { $beforeCalled[] = true; });
        $app->hookBeforeStart((Request $req) ==> { $beforeCalled[] = 'yep'; });
        $app->hookAfterStart((Request $req) ==> { $afterCalled[] = 1; });
        $app->hookAfterStart((Request $req) ==> { $afterCalled[] = 'yes yes'; });
        $client = $this->createClient($app);
        $client->request('/not_existing');
        $this->assertEquals(Vector {true, 'yep'}, $beforeCalled);
        $this->assertEquals(Vector {1, 'yes yes'}, $afterCalled);
    }

    public function testHookForRoute() : void {
        $app = new Fighter\Application();
        $beforeCalled = Vector {};
        $afterCalled = Vector {};
        $app->hookBeforeRoute((Request $req) ==> { $beforeCalled[] = true; });
        $app->hookBeforeRoute((Request $req) ==> { $beforeCalled[] = 'yep'; });
        $app->hookAfterRoute((Request $req) ==> { $afterCalled[] = 1; });
        $app->hookAfterRoute((Request $req) ==> { $afterCalled[] = 'yes yes'; });
        $client = $this->createClient($app);
        $client->request('/not_existing');
        $this->assertEquals(Vector {true, 'yep'}, $beforeCalled);
        $this->assertEquals(Vector {1, 'yes yes'}, $afterCalled);
    }
}

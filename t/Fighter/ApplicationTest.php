<?hh //partial

require_once('AppProvider.php');

use Fighter\Net\Request;
use Fighter\Net\Response;

class ApplicationTest extends \Fighter\Test\WebCase {

    public function testHasRoutes() : void {
        $app = AppProvider::singleDefaultRoute();
        $this->hasRoute('/', $app, 'Should have / route');
        $this->hasRoute('/foo', $app, 'Should have /foo route');
        $this->hasRoute('GET /foo', $app, 'Should have GET /foo route');
    }

    /**
     *  This route doesn't exist
     *  @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testDoesntHaveRouteSimple() : void {
        $app = AppProvider::singleDefaultRoute();
        $this->hasRoute('/bar', $app);
    }

    /**
     *  This route doesn't exist
     *  @expectedException PHPUnit_Framework_ExpectationFailedException
     */
    public function testDoesntHaveRouteWithMethod() : void {
        $app = AppProvider::singleDefaultRoute();
        $this->hasRoute('POST /bar', $app);
    }


    public function testExternalFileRouting() : void {
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

    public function testNotFound404() : void {
        $app = new Fighter\Application();

        $client = $this->createClient($app);
        $client->request('GET /');

        $this->assertEquals(
            404,
            $client->getResponse()->getStatus()
        );
    }


    public function testInternalServr500() : void {
        $app = new Fighter\Application();
        $app->route('/', () ==> {throw new \Exception("Errorrrrrr");});

        $client = $this->createClient($app);
        $client->request('GET /');

        $this->assertEquals(
            500,
            $client->getResponse()->getStatus()
        );
    }

    public function testAppWithFlush() : void {
        $app = new Fighter\Application();
        $app->mute = false;
        $client = $this->createClient($app);

        ob_start();
        $client->request('GET /');
        ob_end_clean();

        $response = $client->getResponse();
    }

    public function testAppWithVariable() : void {
        $app = new Fighter\Application();

        $app->var->set('id', 1);
        $this->assertEquals(1, $app->var->get('id'));

        $this->assertTrue($app->var->contains('id'));

        $app->var->clear();
        $this->assertFalse($app->var->contains('id'));
    }

    public function testAppWithRouteParamAndAppAsLastParam() : void {
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

    public function testAppWithRouteParamWithoutApps() : void {
        $app = new Fighter\Application();
        $app->route('GET /user/@id', ($id) ==> $id);
        $client = $this->createClient($app);

        $client->request('GET /user/10');

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
    }

    public function testAppWithMethodBinding() : void {
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

    public function testAppWithGetParam() : void {
        $app = new Fighter\Application();
        $app->route('GET /user', ($app) ==> $app->request->get->get('id'));
        $client = $this->createClient($app);

        $client->request('GET /user', Map {'id' => 10});

        $this->assertEquals(
            '10',
            $client->getResponse()
        );
    }

    public function testAppWithPostParam() : void {
        $app = new Fighter\Application();
        $app->route('POST /user', ($app) ==> $app->request->post->get('id'));
        $client = $this->createClient($app);

        $client->request('POST /user', Map {'id' => 11});

        $this->assertEquals(
            '11',
            $client->getResponse()
        );
    }

    public function testAppWithNoReturn() : void {
        $app = new Fighter\Application();
        $app->route('POST /user', () ==> {});
        $client = $this->createClient($app);

        $client->request('POST /user');

        $this->assertEquals(
            '',
            $client->getResponse()
        );
    }

    public function testBindErrorHandler() : void {
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

    public function testBindNotFoundHandler() : void {
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

    public function testBindShutdownHandler() : void {
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

    public function testHookForStart() : void {
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

    public function testHookForStop() : void {
        $responses = Vector {};
        $app = AppProvider::singleDefaultRoute();
        $app->hookBeforeStop((Response $rsp) ==> { $rsp->setStatus(600); });
        $app->hookAfterStop((Response $rsp) ==> { $responses[] = $rsp; });

        $client = $this->createClient($app);
        $client->request('/');

        $this->assertEquals(600, $client->getResponse()->getStatus());
        $this->assertCount(1, $responses);
        $this->assertInstanceOf('Fighter\Net\Response', $responses[0]);

        $client->request('/foo');
        $this->assertEquals(600, $client->getResponse()->getStatus());
        $this->assertCount(2, $responses);
        $this->assertInstanceOf('Fighter\Net\Response', $responses[1]);
    }

    public function testHookForNotFound() : void {
        $app = new Fighter\Application();
        $app->route('/', () ==> {'pass';});
        $beforeCalled = Vector {};
        $afterCalled = Vector {};
        $app->hookBeforeNotFound((Request $req) ==> { $beforeCalled[] = true; });
        $app->hookBeforeNotFound((Request $req) ==> { $beforeCalled[] = 'yep'; });
        $app->hookAfterNotFound((Request $req) ==> { $afterCalled[] = 1; });
        $app->hookAfterNotFound((Request $req) ==> { $afterCalled[] = 'yes yes'; });
        $client = $this->createClient($app);
        $client->request('/');
        $client->request('/not_existing');
        $this->assertEquals(Vector {true, 'yep'}, $beforeCalled);
        $this->assertEquals(Vector {1, 'yes yes'}, $afterCalled);
    }

    public function testHookForError() : void {
        $app = new Fighter\Application();
        $app->route('/', () ==> {'pass';});
        $app->route('/problem', () ==> { throw new \RuntimeException('failed', 300); });
        $beforeCalled = Vector {};
        $afterCalled = Vector {};
        $app->hookBeforeError((\Exception $e) ==> { $beforeCalled[] = $e->getCode(); });
        $app->hookBeforeError((\Exception $e) ==> { $beforeCalled[] = $e->getMessage(); });
        $app->hookAfterError((\Exception $e) ==> { $afterCalled[] = $e->getMessage(); });
        $app->hookAfterError((\Exception $e) ==> { $afterCalled[] = $e->getCode(); });
        $client = $this->createClient($app);
        $client->request('/');
        $client->request('/problem');
        $this->assertEquals(Vector {300, 'failed'}, $beforeCalled);
        $this->assertEquals(Vector {'failed', 300}, $afterCalled);
    }
}

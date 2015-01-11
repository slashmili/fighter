<?hh
$app = new Fighter\Application();

$app->route('/', () ==> 'Hello World with one route');

return $app;

<?hh
$app = new Fighter\Application();

$app->route('/', () ==> 'Hello World with one route');
$app->route('/foo', () ==> 'bar');

return $app;

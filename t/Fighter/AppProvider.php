<?hh //strict

class AppProvider {
    public static bool $muteByDefault = true;

    public static function singleDefaultRoute() : Fighter\Application {
        $app = new Fighter\Application();
        $app->route('/', () ==> 'Hello World with one route');
        $app->route('/foo', () ==> 'bar');
        if (self::$muteByDefault) $app->mute = true;
        return $app;
    }
}

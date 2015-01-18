---
title: Fighter - Document
header: Docs
layout: page
---


# Base
The base appliaction should be like this :

{% highlight php %}
<?hh
require __DIR__ . '/vendor/autoload.php';

$app = new Fighter\Application();

$app->run();

{% endhighlight %}


# Routing
Routing in Fighter is done by matching a URL pattern with a callback function.

{% highlight php %}
<?hh
$app->route('/', () ==> 'Hello World!');
{% endhighlight %}

The callback can be any object that is callable. So you can use a regular function:
{% highlight php %}
<?hh
function hello() {
    return 'Hello World!';
}
$app->route('/', () ==> 'hello');
{% endhighlight %}

Or a class method:
{% highlight php %}
<?hh
class Greeting {
    public static function hello() {
        return 'hello world!';
    }
}
$app->route('/', () ==> ['Greeting','hello']);
{% endhighlight %}


## Method Routing

By default, route patterns are matched against all request methods. You can respond to specific methods by placing an identifier before the URL.

{% highlight php %}
<?hh
$app->route('GET /', () ==> 'I received a GET request.');
$app->route('POST /', () ==> 'I received a POST request.');
{% endhighlight %}


## Regular Expressions
You can use regular expressions in your routes:

{% highlight php %}
<?hh
$app->route('/user/[0-9]+', () ==> 'This will match /user/1234');
{% endhighlight %}


## Named Parameters
You can specify named parameters in your routes which will be passed along to your callback function.

{% highlight php %}
<?hh
$app->route('/@name/@id', ($name, $id) ==> "hello, $name ($id)!");
{% endhighlight %}


You can also include regular expressions with your named parameters by using the : delimiter:

{% highlight php %}
<?hh
$app->route('/@name/@id:[0-9]{3}', ($name, $id) ==> {
    'This will match /bob/123, But will not match /bob/12345'
});

{% endhighlight %}


## Optional Parameters
You can specify named parameters that are optional for matching by wrapping segments in parentheses.

{% highlight php %}
<?hh
$app->route('/blog(/@year(/@month(/@day)))', ($year, $month, $day) ==> {
    // This will match the following URLS:
    // /blog/2012/12/10
    // /blog/2012/12
    // /blog/2012
    // /blog
});
{% endhighlight %}

Any optional parameters that are not matched will be passed in as NULL.


## Wildcards
Matching is only done on individual URL segments. If you want to match multiple segments you can use the * wildcard.

{% highlight php %}
<?hh
$app->route('/blog/*', () ==> {
    // This will match /blog/2000/02/01
});
{% endhighlight %}

To route all requests to a single callback, you can do:

{% highlight php %}
<?hh
$app->route('*', () ==> {
    // Do something
});
{% endhighlight %}


# Testing

# Extending


<script>
$('#main_content').toc();
</script>

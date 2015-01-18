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
$app->route('GET /', () ==> return 'I received a GET request.');
$app->route('POST /', () ==> return 'I received a POST request.');
{% endhighlight %}




## Regular Expressions

## Named Parameters

## Optional Parameters

## Wildcards

# Testing

# Extending


<script>
$('#main_content').toc();
</script>

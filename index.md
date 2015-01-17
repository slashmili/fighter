---
title: Fighter - An extensible micro-framework for HackLang
layout: page
---

## What is Fighter
Fighter is a fast, simple and testable framework for HackLang. Fighter enables you to quickly and easily build RESTful web applications.


{% highlight php %}
<?hh //partial
require __DIR__ . '/vendor/autoload.php';

$app = new Fighter\Application();
$app->route('/', () ==> 'Hello World');

$app->run();

{% endhighlight %}

Fighter is clone of [Flight](http://flightphp.com)

### Support or Contact
Having trouble with Fighter? Check out [Fighter repo](https://github.com/slashmili/fighter).


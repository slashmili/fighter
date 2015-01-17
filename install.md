---
title: Fighter - Install
layout: page
---

## Install

### Composer
Add Fighter requirement to your compose.json

{% highlight json %}
{
    "require": {
        "fighter/fighter": "dev-master"
    }
}
{% endhighlight %}

Create your app.hh
{% highlight php %}
<?hh
require_once __DIR__ .'/vendor/autoload.php';

$app = new Fighter\Application();
$app->route('/', () ==> 'Hello World');

$app->run();
{% endhighlight %}


###Nginx
{% highlight Nginx %}
server {
    listen 80 default_server;
    listen [::]:80 default_server ipv6only=on;

    root /var/www/html/yourapp/web

    server_name localhost;

    location / {
        try_files $uri @rewriteapp;
    }

    location @rewriteapp {
        rewrite ^(.*)$ /app.hh/$1 last;
    }

    location ~ ^/(app|app_dev)\.hh(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.hh)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS off;
    }
}
{% endhighlight %}

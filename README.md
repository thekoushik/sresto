# SRESTO
A very simple lightweight PHP REST Framework

### Install

composer.json
``` json
{
    "repositories": [
        {
            "type":"vcs",
            "url":"https://github.com/thekoushik/sresto.git"
        }
    ],
    "require": {
        "thekoushik/sresto": "dev-master"
    },
    "minimum-stability": "dev"
}
```
Then run in your terminal
``` bash
$ composer install
```

### Basic Usage

``` php
require __DIR__ . '/vendor/autoload.php';

use SRESTO\Router;

$router=new Router();

$router->get("/hello",function($req,$res,$s){
    $res->message("Hello World");
});
$router->execute();
```

Test it

``` bash
$ curl -GET "http://localhost/helloworld/?/hello"
```
will print

```
{"message":"Hello World"}
```
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

$router->get("a=0",function($req,$res,$services){
    echo $req['b'];
});
$router->execute();
```

Test it

``` bash
$ curl -GET "http://localhost/helloworld/index.php?a=0&b=Hello%20World"
```
will print

```
Hello World
```
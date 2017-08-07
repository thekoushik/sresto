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

### URL parameters

``` php
$router->get("/:id",function($req,$res,$s){
    $res->message("Your id is ".$req->param['id']);
},['id'=>'digits']);
```
where
``` bash
$ curl -GET "http://localhost/helloworld/?/2323"
```
will print

```
{"message":"Your id is 2323"}
```

Supported Param Types

* digits
* alphabets
* alphanumerics


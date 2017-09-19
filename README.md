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

use SRESTO\Application;

$router=Application::createRouter();
$router->get("/hello",function($req,$res){
    $res->message("Hello World");
});
Application::execute();
```

##### With URL rewrite (.htaccess)

``` apacheconf
<IfModule mod_rewrite.c>
    # Tell PHP that the mod_rewrite module is ENABLED.
    SetEnv HTTP_MOD_REWRITE On

    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule . index.php [L]
</IfModule>
```

Test it (in **helloworld** directory)

``` bash
$ curl -GET "http://localhost/helloworld/hello"
```
will print

```
{"message":"Hello World"}
```

##### Without URL rewrite

``` bash
$ curl -GET "http://localhost/helloworld/?/hello"
```

### URL parameters

``` php
$router->get("/:id",function($req,$res){
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


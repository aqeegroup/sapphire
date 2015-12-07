Sapphire
==============================

[![Total Downloads](https://poser.pugx.org/lornewang/sapphire/downloads)](https://packagist.org/packages/lornewang/sapphire)
[![Latest Stable Version](https://poser.pugx.org/lornewang/sapphire/v/stable)](https://packagist.org/packages/lornewang/sapphire)
[![Latest Unstable Version](https://poser.pugx.org/lornewang/sapphire/v/unstable)](https://packagist.org/packages/lornewang/sapphire)
[![License](https://poser.pugx.org/lornewang/sapphire/license)](https://packagist.org/packages/lornewang/sapphire)

Sapphire is a super lightweight development framework - for people who build application using PHP 5.4+.

Sapphire goal is to enable you to develop projects much faster than you could if you were writing underlying code from scratch.
Sapphire does not contain any class libraries and tools, but it can be very good combined any package manager,
such as Composer etc., you can make almost any projects based on this system.

Sapphire is right for you if :

- You want a framework with a small footprint.
- You need exceptional performance.
- You want a framework that requires nearly zero configuration.
- You need a framework has only routing module and underlying common modules.
- You need free to add any components or libraries.
- You eschew complexity, favoring simple solutions.

Configuration
------------------
### Pretty URLs

####  Apache

If your Apache server has mod_rewrite enabled, you can easily remove this file by using a .htaccess file with some simple rules.
Here is an example of such a file, using the "negative" method in which everything is redirected except the specified items:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

#### Nginx

On Nginx, the following directive in your site configuration will allow "pretty" URLs:

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Architecture
------------------
### Introduction
Lighter is the most simplified underlying framework, no redundant modules, structure is free, customizable, 
determine how to structure pattern by the developer.

Itself execution speed is very fast, combined with third-party libraries and extension will play its strong 
ability to work.

### Request Lifecycle
When using any tool in the "real world", you feel more confident if you understand how that tool works. 
Application development is no different. When you understand how your development tools function, you 
feel more comfortable and confident using them.

The goal of this document is to give you a good, high-level overview of how the Lighter framework
"works". By getting to know the overall framework better, everything feels less "magical" and you 
will be more confident building your applications.

If you don't understand all of the terms right away, don't lose heart! Just try to get a basic grasp
of what is going on, and your knowledge will grow as you explore other sections of the documentation.

1. The entry point for all requests to a Lighter application is the **index.php** file. 
All requests are directed to this file by your web server (Apache / Nginx) configuration. 
The index.php file doesn't contain bussiness logic code. Rather, it is simply a starting
point for loading the rest of the framework.

2. Include the Loader, registered the automatic loading mechanism, at the same time, the default 
setting **system/package** and **system/facades** to automatically load directory.
3. Perform custom initialization file, you can be defined in the application starting before you 
need to do in **app/initialize.php** file.

4. The Router examines the HTTP or Cli request to determine what should be done with it.
5. To do what you should do, until finished the request.


### Application Structure
The default Lighter application structure is intended to provide a great starting point for both
large and small applications. Of course, you are free to organize your application however you like.
Lighter imposes almost no restrictions on where any given class is located - as long as Composer 
or Lighter can autoload the class.

You can modify the **index.php** and **app/loader.php** to customize your personalized directory and load path,
If you beginning ability, even can modify the **system** directory content depth to customize your system structure.

The root directory of a fresh Lighter installation contains a variety of folders:

- **app** (*Contains the core of the application code*)
 - **main** (*The main logic code, has been use autoload*)
 - **config** (*Place configuration files, support different environment mapping*)
 - **temp** (*Place cache, runtime, debris etc. temporary files*)
 - **loader.php** (*Setting automatic loading mechanism*)
 - **routes.php** (*Setting custom routes*)
- **index.php** (*Project single index file*)


Loader
------------------
### Paths Cache
After open cached, all loading paths will be cached.The next request, will save path search time, improve
the performance of load
```php
Loader::setCacheEnabled(TRUE);
```
### Add Paths
You can add new path to autoload one by one in the **app/loader.php** file
```php
Loader::addPath(APP_PATH . 'libraries');
```
### Import Files
You can add new initialization file before the route started in the **app/loader.php** file, such as "composer" autoload.php, 
and can also be used in any where instead of `require_once`

```php
Loader::import('/path/to/vendor/autoload.php');
```

Route
------------------
### Basic Usage
You will define most of the routes for your application in the **app/routes.php** file, 
the most basic Lighter routes simply accept a URI and a `Closure`
```php
Route::get('/', function()
{
    echo 'Hello World';
});
```

Accept more methods, such as: `post` , `put` , `delete` or  `all` match all methods
```php
Route::post('foo/bar', function()
{
    echo 'Hello World';
});
```

Support regular expression matching

```php
Route::put('foo/bar/(\d+)', function($uri, $id)
{
    echo "ID is {$id}";
});
```

Also can directly call an object's method, but it must be autoload

```php
Route::delete('bar/baz', 'Foo\Bar::baz');
```

### Auto Routing
If you want to use the convention way to access URL routing

```
example.com/foo/bar/baz/qux
```
Mapped to a file and instantiation "Baz" class and invoke "qux" method
```
app/main/Controller/Foo/Bar/Baz.php
```
You can use the below sample code to the **app/routes.php** file

```php
Route::auto('main/Controller', 'Controller');
```

License
-------

Sapphire is licensed under the MIT License - see the `LICENSE` file for details
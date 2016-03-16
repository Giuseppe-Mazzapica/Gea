Gea
====

> Environment variables management in PHP

-----

[![travis-ci status](https://img.shields.io/travis/Giuseppe-Mazzapica/Gea.svg?style=flat-square)](https://travis-ci.org/Giuseppe-Mazzapica/Gea)
[![codecov.io](https://img.shields.io/codecov/c/github/Giuseppe-Mazzapica/Gea.svg?style=flat-square)](http://codecov.io/github/Giuseppe-Mazzapica/Gea?branch=master)
[![release](https://img.shields.io/github/release/Giuseppe-Mazzapica/Gea.svg?style=flat-square)](https://github.com/Giuseppe-Mazzapica/Gea/releases/latest)

-----

From a fork of [PHP Dotenv](https://github.com/vlucas/phpdotenv) by [Vance Lucas](http://www.vancelucas.com/).

-----

# What's Gea

Gea is a PHP library that manages app configuration via environment variables.

# What's environment variables

Environment variables are application configuration that may change according to application environment (production, development, staging...).

A lot of times, these are *sensitive* configuration, such as database credentials, API keys, and so on.

This kind of configurations, should **never** be placed in code, for security reasons, but also to be able to easily change them according to environment.

# How to store environment variables

Environment variables can be set on the server that runs the application. These can be done via command line, via automated deployment tools (like Capistrano), or configured from an interface provided by the cloud hosting service (like  Heroku).

This is very fine, however, during development this is not really handy. Ther's another approach: the *.env* file.

# The `.env` file

`.env` file is a text file that contains a lists of environment variables, one per line. The way it is written is in bash syntax, something like:

```bash
FOO=Bar
BAR=Baz
```

### Nested variables

You can even use variables to define other variables:

```bash
BASE_PATH=/var/www/app
PUBLIC_PATH=${BASE_PATH}/public
```

### String with spaces

Strings containing spaces, need to be surrounded by quotes:

```bash
TITLE="Hello World"
```

### Comments

Any string prepended by `#` is a comment

```bash
# Following line is super secret
PASSWORD=mypassword # Cool, isn't it?
```

### Export

It is also possible to prepend the bash command `export` to variables:

```bash
export PASSWORD=mypassword
```

# Environment variables and PHP

PHP has two functions to read and write environment variables: [`getenv()`](http://php.net/manual/en/function.getenv.php) and [`putenv()`](http://php.net/manual/en/function.putenv.php).

However, with these functions is not possible to set more variables at once, so you would need to parse the `.env` file, read it line by line, deal with spaces, quotes, comments....

... no worries, here it comes **Gea**.

# Load `.env` file with Gea

To load environmet variables from a file with Gea is very easy.

First we need to obtain an instance of `Gea\Gea` class with the static `instance()` method.  

```php
$gea = Gea\Gea::instance(__DIR__);
```

Only required parameter is the folder path in which the file is saved. The file name is set as `.env` by default, but can be customized to anything passing the name as second argument.

After that, we can call `load()` on the obtained instance:

```php
$gea->load();
```

That's it. All the environment variables in the file are now loaded.

# Read environment variables

Surely it is possible to read environment variables using `getenv()` PHP function, but Gea comes with a `read()` method, and also with `ArrayAccess` support.

Following lines do same thing:

```php
$apiKey = $gea->read('APY_KEY');
$apiKey = $gea['APY_KEY'];
$apiKey = getenv('APY_KEY');
```

# Write environment variables

As you can guess, Gea also comes with a `write()` method, and you can write variables with `ArrayAccess` syntax as well:

```php
$gea->write('APY_KEY', 'mysupersecretkey');
$gea['APY_KEY'] = 'mysupersecretkey';
putenv('APY_KEY=mysupersecretkey');
```

# Immutability

Environment variables, by themself, are not immutable. You can change them anytime you want. This may appear fine to many, but it actually add complexity to the application.

For this reason, **Gea is committed to immutability**.

For example it is not possible to call `load()` more than once.

```php
$gea->load(); # ok
$gea->load(); # throw an exception
```

It also means that an environment variables that are already written (or loaded from `.env` file) can't be overwritten.

```php
$gea->write('A_VAR', 'A value'); # ok
$gea->write('A_VAR', 'Another value'); # throw an exception
```

*Note that Gea does not control `putenv()`, so calling that function it will be possible to actually overwrite variables, so immutable behavior only applies when variables are accessed via Gea methods.*

However, if you really have to, there are two ways to change a value of variables already set:

 - Discarding it
 - Hard-flushing it


# Discarding variables

It is possible to discard a variable with `discard()` Gea method, or simply using `unset()` with `ArrayAccess` syntax:

```php
$gea->discard('FOO');
unset($gea['FOO']);
```

After a variable have beed flushed, it can be set again with a different value:

```php
$gea['FOO'] = 'First Value';

echo $gea['FOO']; # print 'First Value'

unset($gea['FOO']); # Without this an exception had been thrown on next line

$gea['FOO'] = 'Second Value';

echo $gea['FOO']; # Print 'Second Value'
```

# Flushing variables

Gea has two different kinds of variables flushing: *hard* flush and *soft* flush.

## Soft Flush

Soft flush allows to do more consecutive calls to `load()`. However, if the variables loaded on a second call are the same loaded first time, an exception is thrown, because of immutability behavior.

```php
$gea->flush(Gea\Gea::FLUSH_SOFT);
```

For these reason, soft flush is only useful if all the variables loaded has been discarded, or if the `.env` file has changed during app execution... nothing very common.

## Hard Flush

Hard flushing can be sees as a sort of *bulk discarding*, in fact, it can be used to discard more variables at once:

```php
$gea->flush(Gea\Gea::FLUSH_HARD, ['FOO', 'BAR', 'BAZ']);
```

As you can guess, the second argument of `flush()` is an array of the variables that needs to be discarded.

These means that if we may want to know which variables have been set. 

# Get names of variables set

When variables are loaded from `.env` file, the `load()` method return an array of the names of all the variables that have been loaded (not the values).

By default this array is not stored anywhere by Gea. But it is possible to instrct Gea to do that by passing the flag `Gea\Gea::VAR_NAMES_HOLD` as third argument to `instance()` method:

```php
$gea = Gea\Gea::instance(__DIR__, '.env', Gea\Gea::VAR_NAMES_HOLD);
$gea->load();
```

After Gea is instructed to hold variables names, the method `varNames()` returns it:

```php
var_export( $gea->varNames() ); // array( 'FOO', 'BAR', 'BAZ' )
```

The array is kept updated, it means that any variables wrote is added, and any variables discarded is removed.

It also means that thies method in comination with hard flushing can discard all variables loaded:

```php
$gea = Gea\Gea::instance(__DIR__, '.env', Gea\Gea::VAR_NAMES_HOLD);
$gea->load();

$gea->flush(Gea\Gea::FLUSH_HARD, $gea->varNames());
```

Actually, the second argument can be omitted: hard flush defaults to discard all variables if nothing is provided and `Gea\Gea::VAR_NAMES_HOLD` flag is used to instantiate Gea.

# Read-only behavior

Gea provides a way to disable all write, flush and dicard operation. After variables have been loaded, they can't be changed at all.

It can be done by passing  the flag `Gea\Gea::READ_ONLY` as third argument to `instance()` method:

```php
$gea = Gea\Gea::instance(__DIR__, '.env', Gea\Gea::READ_ONLY);
$gea->load();
```

After that, any call to `write()`, `discard()`,  or `flush()` with throw an exception.

Note that `instance()` third argument accepts a bitmask of flags, so it is possible to combine them:

```php
$flags = Gea\Gea::READ_ONLY|Gea\Gea::VAR_NAMES_HOLD;
$gea = Gea\Gea::instance(__DIR__, '.env', $flags);
```

# Filtering and validating variables

A powerful feature of Gea is filtering and validation of variables.

Environent variables are strings. However, configuration values are not always strings... you may want integers, booleans... and so on.

Moreover, some configuration values are required, and it is fine to "fail early" if those required configuration are not there. 

Gea variable filters allow to do all of this.

Filters are added using `addFilter()` method of Gea instance. The filters shipped with Gea are:

- required
- enum
- choices
- int
- float
- bool
- array
- object
- callback

and it is possible to write custom filters.

## What filters do

When variables are accessed using Gea methods, the returned value can be changed by filters.

Most filters are lazy, it means they are evaluated (only once) when (and if) the value is first accessed. 

Filters that do *validation* like "required" or "enum" are evaluated immediately after variables have been loaded, to ensure an early fail if a mandatory value is missing.


## Required variables

To make an environment variable mandatory, it is possible to use the "required" filter, like so:

```php
$gea = Gea\Gea::instance(__DIR__);

$gea->addFilter('API_KEY', 'required');
```

Now when variables are loaded, if the `API_KEY` var is not set, an excetion is thrown.

This behavior is not the same for all filters: in fact, only `'required'`, `enum`, and `choices` are evaluated on load, all other filters are *lazy*, they are evaluated when (and if) the variable is first accessed.

## Constrain variables to some values

Both  `'enum'` and `'choices'` filters do pretty the same thing: force the variable to a set of possible values. Only difference is that `enum` do a strict comparison (`===`) while `'choices'` do a weak comparison (`==`).

```php
$gea->addFilter('MY_SWITCH', ['enum' => ['on', 'off']]);
```

This time I passed the filters as a single item array, where the key is the filter name, and the value is an an array to configure the filter. This is the syntax to use for filters that actually need configuration.

Worth noting that this filter can be used to make a variable required, for example the code above will trigger an exception if the var `'MY_SWITCH'` is not set. By adding `null` to the list of values, the variable becomes optional.

## Cast to numerical values

The two filters `'int'` and `'float'` are used to ensure the variable they are applied to, is casted to, respectively, an integer and a floating comma number. These filters, just like al the following, are lazy, they are evaluated when and if the variable they are associated is first accessed.

```php
$gea->addFilter('MY_NUMBER', 'int');
```

If the value is not numerical, the filter trigger an exception.


## Cast to booleans

The `'bool'` filter is used to cast variabls to booleans. Nothe that this filters uses `filter_var()` in combination with `FILTER_VALIDATE_BOOLEAN` constant, it means that `'1'`, `'true'`, `'yes'`, `'on'` strings are all casted to `true`.

```php
$gea->addFilter('AWESOMENESS_ALLOWED', 'bool');
```

## Make array from variables

The `'array'` filter is very flexible, and is used to convert a variable to an array. In its simplest form just *explode* the string by commas:

```php
$gea->addFilter('MY_LIST', 'array');
```
Configuration for this filter acceps three arguments: the first allows to set a different separator, the second can bne used to turn on or off the *trim* of items after the explode, finally the third argument can be set as a callback that will me used to *map* all items.

For instance, let's assume the var `USER` is set in `.env` file like this: 

```bash
USER=" john | doe"
```

Using the filter:

```php
$gea->addFilter('USER', ['array' => ['|', ArrayFilter::DO_TRIM, 'ucfirst']]);
$gea->load();
```

Then 

```php
var_export($gea['USER']); // array( 'John', 'Doe' )
```

## Instantiate objects from variables

The `'object'` filter can be used to instantiate an object, which class is given when ading filter, passing the current value of the variable to class constructor.

```php
$gea->addFilter('USER_EMAIL', ['object' => [My\App\EmailObject::class]);
```

Useful to instantiante value objects straight from env variables.

## Transformate variables with callback

Possibly the most flexible filter shipped with Gea is `callback`, it allows to set a callback that will be called passing as argument the current value of the variable. Whatever the callback returns, will be used as variable value.

```php
$gea->addFilter('USER_ID', ['callback' => [function($userId) {
   return My\App\UserFactory::fromID($userId);
}]);
```

## Combine filters

In Gea it is possible to combine variables filters. When more filters are set to same variable, they are called in order using *pipeline* pattern: the next filter is called with the result of previous as argument.

To add more filters to a variable, you can:

- call more and more times `addFilter()` using the same variable name
- pass an array of filters as `addFilter()` second argument
- both the previous

Some examples:

```php
$gea->addFilter('USER_ID', ['required', 'int']);
```

```php
$gea->addFilter('MY_LIST', ['array', ['object' => [\ArrayIterator::class]]);
```

```php
$gea->addFilter('USER_EMAIL', ['required', ['callback' => function($email) {
   return filter_var($email, FILTER_SANITIZE_EMAIL);
}]);
```

# Gea for production environments

Loading variables from `.env` files works very well for development, however it should be avoided on production, to skip the overhead of loading and parsing the `.env` file.

Nice thing about Gea is that you **don't** have to load variables with it to use it: you can use Gea to access (and optionally to validate and filter) environment variables without ever calling `load()`.

Gea will just look for variables, no matter how they are set.

This behavour, combined with the `ArrayAccess` interface implemented by Gea, allows to use it as a configuran *bucket*, something very easy to mock or replace on tests, decoupling the application code from any environment-related operation.

## No-loader instance

The easiest way to use Gea as a configuration "container" without load nothing, is to use the the `noLoaderInstance()` named constructor, as easy as:

```php
$gea = Gea\Gea::noLoaderInstance();
```

Not having to load any file, there's no need to pass folder path or file name. Gea flags can passed as first argument, if necessary.

An instance obtained in this way, can do anything that a *normal" Gea instance can do, you can even call `load()` on it, and it will load nothing, butany non-lazy filter is immediately evaluated.

## No-loader & read-only instance

Another named constructor available in Gea is `readOnlyInstance()`. An instance obtained with this method, not only does not load anything, but is also in read-only mode.

```php
$gea = Gea\Gea::readOnlyInstance();
```

is equivalent to:

```php
$gea = Gea\Gea::noLoaderInstance(Gea\Gea::READ_ONLY);
```

# Integration Example

Let's imagine a pseudo class like this:

```php
namespace MyApp;

class App {
  
    public function run(\ArrayAccess $configs) {
       // bootstrap the application here
    }
}
```

and an *index* file like this:

```php
$gea = getenv('APP_ENV') === 'production'
   ? Gea\Gea::noLoaderInstance() # in production we load nothing
   : Gea\Gea::instance(__DIR__); # in development we load .env file

$gea->addFilter(['DB_NAME', 'DB_USER', 'DB_PASS'] 'required');
// maybe more filters here...

// This will load nothing on production, but ensures filter are validated
$gea->load(); 

$myApp = new MyApp\App();
$myApp->run($gea);
```

The `App` class code is completely decoupled from environment functions, global variables and even from Gea specific methods. It means that it will be very easy to mock the configuration in tests, or maybe switch to another kind of configuration, like JSON, Yaml or just PHP files.


# Why Gea?

Gea was born as a fork of [PHP dotenv](https://github.com/vlucas/phpdotenv) by Vance Lucas.

That library is widely used and is considered by PHP community as a very affordable piece of code.

So, *why this*?

Everything started because for an app I was working on, I could not allow environment variables to be stored in `$_SERVER` array (that was exposed), that is the default behavior of PHP Dotenv.

I realized that customize this behavior was not as simple as I thought, and decided to fork the library and add an interface that had allowed an easier replacement for the PHP Dotenv `Loader` class.

The original idea was to contribute to PHP Dotenv, but after having forked and added the interface, I could not stop myself refactoring... ending up in a complete rewrite with a completely different architecture.

Something that surely can't be merged in a pull request.

Gea architecture is more complex than PHP Dotenv, and that's bad, but it is also more flexible and has more "sugars" that aren't there in PHP Dotenv.

Of course, one can use Gea to just load  a `.env` file like Dotenv does, without using any other feature or customization; in that case differences with PHP Dotenv are close to zero.

Depending on your use case, Gea may fit or just not.

## Some differences between PHP Dotenv and Gea

- Gea has filtering for advanced variable casting and filtering (PHP Dotenv has "required" and "allowedValues" for validation)
- Gea implements `ArrayAccess`, `read()`,`write()`,`discard()`, and `flush()` methods that make it usable as a configuration "bucket".
  This makes Gea more a configuration "bucket" than just a loaded for environment variables
  This feature are completely decoupled from loader that might not be used at all.
- Gea ensures **immutability** by default, when  values are accessed with its methods
- In Gea is easy to have a clue of which variables have been set / loaded
- Gea is completely OOP, it means that implementing its interfaces it's easy to customize its behavior
- Minimum required PHP version: 5.3.9 for PHP Dotenv, 5.5 for Gea
- License is BSD-3-clause for PHP Dotenv, MIT for Gea. 

## Incorporated parts from PHP Dotenv

This package contains parts of code from PHP Dotenv. More specifically, the parser "engine" is taken pretty much "as is" from there.

Tests fixtures and some test sources comes from PHP Dotenv, to ensure Gea loads files just like Dotenv does.

All the files that incorporates code from PHP Dotenv contain license notice on top.


# Minimum Requirements

- PHP 5.5+


# Installation

With Composer require `gmazzap/gea`.


# License

Gea is released under MIT license https://opensource.org/licenses/MIT


# Contributing

See `CONTRIBUTING.md`.

**Don't** use issue tracker (nor send any pull request) if you find a **security** issue.
They are public, so please send an email to the address on my [Github profile](https://github.com/Giuseppe-Mazzapica). Thanks.
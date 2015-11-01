Gea
====

> Loads environment variables from `.env` to `getenv()`, `$_ENV` and `$_SERVER`.

-----
**This is a fork (and refactoring) of [PHP dotenv by Vance Lucas](https://github.com/vlucas/phpdotenv)**.
-----

-----

# Table of Contents

- [The `.env` File](#the-env-file)
  - Why?
  - Write Variables in the `.env` file
  - Source Control
  - The `.env.example` file
  - Comments
  - Nesting Variables
- [Gea Usage](#ayika-usage)
  - Loading Variables
  - Accessing Variables
  - Immutability
    - Single Load Point
    - Load More Files
    - Flushing
    - No Overwrite
    - Discarding
    - Hard Flushing
  - Access Names of Set Variables
    - Make Gea Hold Variables Names
  - Filtering
    - Required Variables
    - Validating Variables
    - Casting Variables
    - Custom Filters
- [Usage Notes](#usage-notes)
  - Development VS Production Environments
  - Command Line Scripts
- [Why this fork?](#why-this-fork)
  - Differences between PHP dotenv and Gea
  - What stayed the same?
- [Minimum Requirements](#minimum-requirements)
- [Installation](#installation)
- [License](#license)
- [Contributing](#contributing)


-----


# The `.env` File

## Why?

**You should never store sensitive credentials in your code**.

Storing [configuration in the environment](http://www.12factor.net/config) is one of the tenets of a
[twelve-factor app](http://www.12factor.net/). Anything that is likely to change between deployment environments 
– such as database credentials or credentials for 3rd party services – should be extracted from the
code into environment variables.

Basically, a `.env` file is an easy way to load custom configuration variables that your application needs without having
to modify `.htaccess` files or Apache/nginx virtual hosts.
This means you won't have to edit any files outside the project, and all the environment variables are
always set no matter how you run your project - Apache, Nginx, CLI, and even PHP 5.4's built-in webserver.

It's **way** easier than all the other ways you know of to set environment variables, and you're going to love it.

- **No** editing virtual hosts in Apache or Nginx
- **No** adding `php_value` flags to `.htaccess` files
- **Easy** portability and sharing of required ENV values
- **Compatible** with PHP's built-in web server and CLI runner


## Write Variables in the `.env` file

The variables have to wrote one per line, in the form: `id=value`, e.g.

```shell
S3_BUCKET="my_bucket"
SECRET_KEY="my_password"
```

## Source Control

Add your application configuration to a `.env` file in the root of your
project. **Make sure the `.env` file is added to your `.gitignore` so it is not
checked-in the code**


## The `.env.example` file

Because the `.env` file is kept out of version control it's a good idea provide a separate `.env.example` file
with all the required environment variables defined, except for the sensitive ones, which are either user-supplied for
their own development environments or are communicated elsewhere to project collaborators.

This file can be kept under source control because it contains no sensitive information.

The project collaborators then independently copy the `.env.example` file to a local `.env` and ensure
all the settings are correct for their local environment, filling in the secret
keys or providing their own values when necessary.

The idea behind `.env.example` is to let people know what variables are required, but not give them the sensitive
production values. 


## Comments

You can comment your `.env` file using the `#` character. E.g.

```shell
# this is a comment
VAR="value" # comment
VAR=value # comment
```

## Nesting Variables

It's possible to nest an environment variable within another, useful to cut
down on repetition.

This is done by wrapping an existing environment variable in `${…}` e.g.

```shell
BASE_DIR="/var/webroot/project-root"
CACHE_DIR="${BASE_DIR}/cache"
TMP_DIR="${BASE_DIR}/tmp"
```
With Gea is possible to use more than one variable into another. e.g.

```shell
FOO="foo"
BAR="bar"
BAZ="baz"
FOO_BAR_BAZ=${FOO}/${BAR}/${BAZ}
```

-----

# Gea Usage

## Loading Variables

Before to access variables, you need to *load* them using Gea. The required code is something like this:

```php
Gea\Gea::instance(__DIR__)->load();
```

Where `__DIR__` represent the folder where the `.env` is located. It is possible to use a different id for the file,
just pass it as second argument to `instance()` method.

## Accessing Variables

After all variables have been loaded, it's possible to access them

- with the `getenv` function
- as part of the `$_ENV` super-global
- as part of the `$_SERVER` super-global
- using Gea object `read()` method

```php
$ayika = Gea\Gea::instance(__DIR__);
$ayika->load();

$s3_bucket = getenv('S3_BUCKET');
$s3_bucket = $_ENV['S3_BUCKET'];
$s3_bucket = $_SERVER['S3_BUCKET'];
$s3_bucket = $ayika->read('S3_BUCKET');
```

## Immutability

Gea is committed to immutability. PHP does **not** handle environment variable as immutable, so you can change their
value with consecutive calls of `putenv`.

Changing configuration *on the fly* during app execution, means to increase complexity, and Gea try to prevent
overwrite by mistake.

Gea does that:

- preventing more than one `load()` call on same Gea instance
- preventing overwrite of already set variables

### Single Load Point

If you try to call `load()` more than once, an exception is thrown:

```php
$ayika = Gea\Gea::instance(__DIR__);
$ayika->load();
$ayika->load(); // Exception!
```

### Load More Files

Normally an application has just one `.env` file, but if in tests, or for whichever reason, you need to load environment
variables from several files, just use a different instance of Gea:

```php
Gea\Gea::instance(__DIR__, '.env-1')->load();
Gea\Gea::instance(__DIR__, '.env-2')->load();
```

### Flushing

A way to allow consecutive calls to `load()` is to *flush* the Gea instance, that may be useful if the `.env` file may
be changed during app execution:

```php
$ayika = Gea\Gea::instance(__DIR__);
$ayika->load();

// let's assume .env file somewhat change here...

$ayika->flush();
$ayika->load();
```

### No Overwrite 

However, neither using more files or flushing Gea instance is possible to overwrite an already set variable:
if another instance of Gea or the same instance after flushing try to overwrite an already set variable,
an exception is thrown.


### Discarding

If for some reason you need to overwrite environment variables, you can do that by *discarding* the old value and writing
the new one. E.g. assuming an `.env` file like this:

```shell
FOO="I am the old FOO value"
BAR="I am the old BAR value"
```

It is possible to:

```php
$ayika = Gea\Gea::instance(__DIR__);
$ayika->load();

echo getenv("FOO"); // echo "I am the old FOO value"

$discard = $ayika->discard("FOO");

$ayika->write("FOO", "I am the NEW FOO value");

echo getenv("FOO"); // echo "I am the NEW FOO value"
```

`$discard` in code above is equal to `I am the old FOO value` because `discard()` returns the old value that have been
 just discarded. It will be equal to `null` if there was no value set.

### Hard Flushing

If you need to discard more variables, is possible to do an *hard flush*:

$ayika = Gea\Gea::instance(__DIR__);
$ayika->load();

$ayika->flush(true, ['FOO', 'BAR']);

$ayika->write("FOO", "I am the new FOO value");
$ayika->write("BAR", "I am the new BAR value");
```

After the hard flush is even possible to load an `.env` file using the same Gea instance, assuming that all the old
variables have been flushed (so their names have been passed as second argument to `flush()`).

For these reason may be necessary to know the id of all variables that have been set.


## Access Names of Set Variables

Gea `load()` method returns the array of all the variables that have been set:

```php
$ayika = Gea\Gea::instance(__DIR__);
$names = $ayika->load();

var_dump( $names ); // array( 'FOO', 'BAR' )
```

Moreover, `write()` method returns the id of the variables just set:

```php
$id = $ayika->write(" FOO = BAR ");

var_dump( $id ); // "FOO"
```

### Make Gea Hold Variables Names

By default, Gea instances does not store the names of the variables that have been set, but it is possible to do that,
by setting passing `true` as third argument to `Gea::instance()` method. After that, names can be accessed using
`varNames()` method:

```php
$ayika = Gea\Gea::instance(__DIR__, '.env', true);
$ayika->load();

$names = $ayika->varNames();

var_dump( $names ); // array( 'FOO', 'BAR' )
```

Note that if `varNames()` is called without instructing Gea to hold var names, an exception is thrown.

When Gea is set to hold var names, any call to `write()` updated the array of names:
 
```php
$names = $ayika->varNames();

var_dump( $names ); // array( 'FOO', 'BAR' )

$ayika->write("BAZ", "I am the BAZ value");

$names = $ayika->varNames();

var_dump( $names ); // array( 'FOO', 'BAR', 'BAZ' )
```

## Filtering

Gea allows filtering of environment variables. Filtering is the way that Gea uses for:

 - ensure that required environment variables are set
 - ensure that environment meets some (evn custom) requirements (validation)
 - post-process environment variables, e.g to cast them to a different type (by default env vars are always strings)
 
All the filtering are applied using `addFilter()` method.

Every variable can have attached more filters, in that case Gea uses the *pipeline pattern*: the next filter is
applied on the result of previous filters.
 
### Required Variables

Required filter can be used to ensure some variables are set.

```php
$ayika = Gea\Gea::instance(__DIR__);
$ayika->load();

// if DB_USER or DB_PASSWORD are not set an exception is thrown

$ayika->addFilter('DB_USER', 'required');    
$ayika->addFilter('DB_PASSWORD', 'required');
```

### Validating Variables

Sometimes is required that variables meets some requirements. This is done in Gea using filters. At the moment, the only
validation filter is `'enum'` that ensures the variable ois in a set of predefined values.

```php
$allowed = ['ready', 'in-progress', 'draft'];
$ayika->addFilter('APP_STATUS', ['enum' => [$allowed]);    
```

Using code above we ensures that `APP_STATUS` env var is equal to one of the three strings passed in the `$allowed` array.

This is how, in Gea, you set filters that needs arguments: using an array where the key is the filter name, and the value
 is the array of arguments.
 
Note that using `'enum'` there's no need to also use `'required'` filter: when not set a var is considered `null` by
Gea, and is possible to make the var not-required simply adding `null` to allowed values.


### Casting Variables

By default environment variables set via `.env` file are strings. But more than often configuration require other types,
e.g. integers or boolean.

Gea filters can be used to ensures this types.

**Please note**:

- post-process filters only apply when vars are accessed using `$ayika->read()` method, they do nothing when
  vars are accessed
- post-process filters are applied *lazily*: they run when the related variable is first accessed, in this way a never
  used bad variable does not hurt the app.
  
Gea ships with a set of post-process filters, they are:

 - `'array'` to cast a variable to an array. Strings are *exploded* by `,` by default, but separator can be customised
 - `'int'` to cast a variable to an integer
 - `'float'` to cast a variable to a float
 - `'object'` to instantiate a class (whose class name is provided) injecting the variable as argument
 
This post-process filters does not ensures the variable is set, they fallback to their empty version when the var is not set.
E.g. `'int'` return `0`, `'array'` an empty array and `'object'` instantiate the class passing no argument to constructor.

However, is possible to combine these filters with `'required'` filter to ensure var is set.

```php
$ayika->addFilter('DEBUG_ENABLED', 'bool');   
$ayika->addFilter('MAX_USERS', ['required', 'int']);
$ayika->addFilter('ADMIN_EMAIL', ['object' => [MyApp\EmailValueObject::class]);
$ayika->addFilter('USER_DATA', ['required', 'array', 'object' => ['ArrayObject']);
```

In code above:

- `DEBUG_ENABLED` is casted to bool, and if not set will return `FALSE`
- `MAX_USERS` is casted to int, and if not set an exception is thrown because it is required
- `ADMIN_EMAIL` when accessed will return an instance of the `MyApp\EmailValueObject` class, where the env var was passed to constructor.
- `USER_DATA` when accessed will return an instance of the `ArrayObject` that had received in constructor an array
  obtained exploding the env var by comma. This is because filters are applied in *waterfall*.
  Moreover, an exception is thrown when the env var is not set because it is required
  
Again: remember that what said above only applies if var are accessed via `$ayika->read()`.


### Custom Filters

Gea allows to write custom filters (both lazy and non-lazy) extending `Gea\Filter\FilterInterface`.


-----


# Usage Notes

## Development VS Production Environments

Load and parse environment variables from `.env` files, is something that fits better development environments and generally
should not be used in production.
In production, the actual environment variables should be set so that there is no overhead of loading the `.env` file on each request.
This can be achieved via an automated deployment process with tools like Vagrant, chef, or Puppet, or can be set
manually with cloud hosts like Pagodabox and Heroku.

However, is possible to leverage Gea features like filtering, even in those production scenario, when there's no `.env`
files at all: without calling `load()` Gea just assumes the variables are set in *some* way.


## Command Line Scripts

If you need to use environment variables that you have set in your `.env` file
in a command info script that doesn't use the Dotenv library, you can `source`
it into your local shell session:

```shell
source .env
```

-----


# Why this fork?

Gea born from a fork (and a refactoring) of [PHP dotenv](https://github.com/vlucas/phpdotenv) by Vance Lucas.

That library is used in millions of apps and is considered by PHP community as a very affordable piece of code.

So, *why this fork*?

The original idea was to contribute to PHP dotenv and try to introduce there an interface for their `Loader` class that would
allow me to customize the way it works by default.

But after having forked and added the interface, I could not stop myself refactoring... ending up in a completely different
architecture, something that can't be merged in a pull request.

## Differences between PHP dotenv and Gea

- Minimum required PHP version: 5.3.9 for PHP dotenv, 5.5 for Gea.
- Different architecture. Gea architecture is more "structured" that, yes, means more complex but also much more flexible.
  Thanks to its OOP architecture allows to customize every aspect of how Gea works.
- Introduce filtering (PHP dotenv has "required" and "allowedValues" feature, OOP nature of Gea filters make them easier configurable)
- Env variables can contain more than one *nested* var
- License that is BSD-3-clause for PHP dotenv, MIT for Gea. 

## What stayed the same?

- The idea
- The code that parse `.env` files, that has been proved to be very effective, has not be touched at all: thanks Vance :)
- Part of this readme
- Part of the tests and test fixtures


-----


# Minimum Requirements

- PHP 5.5+

# Installation

With Composer require `gmazzap/ayika`.


# License

Gea is released under MIT license https://opensource.org/licenses/MIT


---


# Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Make your changes
4. Run the tests, adding new ones for your own code if necessary (`phpunit`)
5. Commit your changes (`git commit -am 'Added some feature'`)
6. Push to the branch (`git push origin my-new-feature`)
7. Create new Pull Request
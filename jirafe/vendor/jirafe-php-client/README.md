# Jirafe_Client

PHP 5.2 client for [Jirafe](http://jirafe.com/) web analytics.

## Installation

You can either clone this repository which includes tests with:

    git clone https://github.com/jirafe/php-client

Then, init/update all project submodules (from project root - `cd php-client`):

    git submodule update --init

Or alternatively only clone the src folder with:

    git clone https://github.com/jirafe/php-client-src

## Usage

``` php
<?php

require_once 'path/to/client/autoload.php';

// init API client
$client = new Jirafe_Client(YOUR_API_TOKEN);

// get visits report from site #12 in application #23
$reportHash = $client->applications(23)->sites(12)->visits()->fetch();

// get 'Jack' user information
$userHash = $client->users('Jack')->fetchValues();
```

## Users resource

### Create new user

To create new user, you don't need a token. Also, user will return new token
in it's hash after creation:

``` php
<?php

$client   = new Jirafe_Client();
$userHash = $client->users()->create('vjousse', 'vjousse@knplabs.com');
```

In this case, `$userHash` will be equal to:

```
Array
(
    [email] => vjousse@knplabs.com
    [username] => vjousse
    [token] => TOKEN
)
```

### Operate on specific user

To get user with username = jack, you need a client, initialized with token,
that gives rights to read that user's info:

``` php
<?php

$client  = new Jirafe_Client(API_TOKEN);
$userRes = $client->users('jack');
// or $userRes = $client->users()->get('jack');
```

`$userRes` is `Jirafe_Api_Resource_User` instance, with which you can
operate on original user resource.

Available resource operations are:

1. Update values:

    ``` php
    <?php

    $userHash = $userRes->update(array('email' => 'jack@knplabs.com'));
    ```

    `$userHash` after that will hold:

    ```
    Array
    (
        [email] => jack@knplabs.com
        [username] => jack
    )
    ```

2. Delete resource:

    ``` php
    <?php

    $userRes->delete();
    ```

## Applications resource

### Create new application

To create new application, you don't need a token. Also, application will
return new token in it's hash after creation:

``` php
<?php

$client  = new Jirafe_Client();
$appHash = $client->applications()->create('new app', 'http://newapp.com');
```

In this case, `$appHash` will be equal to:

```
Array
(
    [app_id] => 123
    [name] => new app
    [token] => TOKEN
    [url] => http://newapp.com
)
```

### Operate on specific application

To get application with ID = 23, you need a client, initialized with token,
that gives rights to read that application:

``` php
<?php

$client = new Jirafe_Client(API_TOKEN);
$appRes = $client->applications(23);
// or $appRes = $client->applications()->get(23);
```

`$appRes` is `Jirafe_Api_Resource_Application` instance, with which you can
operate on original application resource.

Available resource operations are:

1. Fetch current values:

    ``` php
    <?php
    
    $appHash = $appRes->fetch();
    ```

    `$appHash` after that will hold:

    ```
    Array
    (
        [app_id] => 23
        [name] => my application
        [url] => http://everzet.com
    )
    ```

2. Update values:

    ``` php
    <?php

    $appHash = $appRes->update(array('url' => 'http://newurl.com'));
    ```

    `$appHash` after that will hold:

    ```
    Array
    (
        [app_id] => 23
        [name] => my application
        [url] => http://newurl.com
    )
    ```

3. Delete resource:

    ``` php
    <?php

    $appRes->delete();
    ```

## Sites resource

### List all application sites

To get list of all sites, added to application with ID = 23, you need a client,
initialized with token, that give rights to read that application:

``` php
<?php

$client    = new Jirafe_Client(API_TOKEN);
$sitesHash = $client->applications(23)->sites()->fetchAll();
// or $sitesHash = $client->applications()->get(23)->sites()->fetchAll();
```

`$sitesHash` after that will hold:

```
Array
(
    [0] => Array
        (
            [site_id] => 12
            [app_id] => 23
            [description] => Jirafe API
            [url] => http://api.jirafe.com
            [timezone] => Europe/Paris
            [currency] => EUR
        )

    [1] => Array
        (
            [site_id] => 13
            [app_id] => 23
            [description] => Jirafe Site
            [url] => http://jirafe.com
            [timezone] => America/New_York
            [currency] => USD
        )

)
```

### Operate on specific site

To get site with ID = 324 in application with ID = 23, you need a client,
initialized with token, that gives rights to read that application and site:

``` php
<?php

$client  = new Jirafe_Client(API_TOKEN);
$siteRes = $client->applications(23)->sites(324);
// or $siteRes = $client->applications()->get(23)->sites()->get(324);
```

`$siteRes` is `Jirafe_Api_Resource_Site` instance, with which you can operate
on original site resource.

Available resource operations are:

1. Fetch current values:

    ``` php
    <?php

    $siteHash = $siteRes->fetch();
    ```

    `$siteHash` after that will hold:

    ```
    Array
    (
        [site_id] => 324
        [app_id] => 23
        [description] => Jirafe API
        [url] => http://api.jirafe.com
        [timezone] => Europe/Paris
        [currency] => EUR
    )
    ```

2. Update values:

    ``` php
    <?php

    $siteHash = $siteRes->update(array('description' => 'Jirafe API WebSite'));
    ```

    `$siteHash` after that will hold:

    ```
    Array
    (
        [site_id] => 324
        [app_id] => 23
        [description] => Jirafe API WebSite
        [url] => http://api.jirafe.com
        [timezone] => Europe/Paris
        [currency] => EUR
    )
    ```

3. Delete resource:

    ``` php
    <?php

    $siteRes->delete();
    ```

## Site reports

To get access to site reports, you'll need a client, initialized with token,
that gives you access to this specific site.

First, you'll need to get site resource:

``` php
<?php

$client  = new Jirafe_Client(API_TOKEN);
$siteRes = $client->applications(23)->sites(324);
// or $siteRes = $client->applications()->get(23)->sites()->get(324);
```

Then, you must choose from one of 8 report types:

``` php
<?php

$specificSiteReports = $siteRes->visits();
$specificSiteReports = $siteRes->visitors();
$specificSiteReports = $siteRes->bounces();
$specificSiteReports = $siteRes->average();
$specificSiteReports = $siteRes->revenues();
$specificSiteReports = $siteRes->keywords();
$specificSiteReports = $siteRes->referers();
$specificSiteReports = $siteRes->exits();
```

And then, you can get specific report with `fetch(...)` or `fetch...(...)` methods.
For example, to get visits per hour on specific day, you should call:

``` php
<?php

$repHash = $siteRes->visits()->fetch('yesterday', array('hour'));
```

First argument is a date, second is array of breakdown parameters (`hour`, `day`).

`$repHash` in that case will hold something like:

```
Array
(
    [0] => Array
        (
            [hour] => 0
            [visits] => 40
        )

    [1] => Array
        (
            [hour] => 1
            [visits] => 32
        )

    [2] => Array
        (
            [hour] => 2
            [visits] => 0
        )
    ...
)
```

## Sync API

To be able to run sync process, you'll need and application admin token and
client instance, initializes with it.

``` php
<?php

$client     = new Jirafe_Client(ADMIN_TOKEN);
$syncedHash = $client->applications(23)->resources()->sync(
    array(
        array('description' => 'site1', 'url' => 'http://site1'),
        array('description' => 'site1', 'url' => 'http://site2')
    ),
    array(
        array('email' => 'everzet@knplabs.com', 'username' => 'everzet'),
        array('email' => 'vjousse@knplabs.com', 'username' => 'vjousse')
    )
);
```

First argument is array of sites and their values, second is users array.
Jirafe will check availability of that resources in it and will create them
if needed. The return value of `sync()` method is hash of sites and users,
synced with specified application, their id's and other info.

Copyright
---------

JirafeClient Copyright (c) 2011 Jirafe <http://www.jirafe.com>.

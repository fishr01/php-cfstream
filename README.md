# PHP - Cloudflare Stream

PHP CFStream is a PHP TUS client that makes it easy to send video files to Cloudflare Stream. 

- Simple interface that supports:
  - Upload videos
  - Get status of uploaded videos
  - Get embed code for videos
  - Set allowedOrigins for each video
  - Delete videos
- Implemented in pure PHP and CURL with the help of GuzzleHttp Client
- Tightly integrated with Cloudflare Stream
- Does not support:
  - Resume uploads

## Installation

Install the package via Composer as usual. Use the `dev-master` branch.

```
composer require jianjye/php-cfstream dev-master
```

#### Laravel 5.0+ 

For Laravel 5.0 and newer projects, you can do `vendor:publish` to enable basic integrations:

```
php artisan vendor:publish 
```

Then select `Provider: Fishr01\CFStream\Laravel\ServiceProvider`. `cfstream.php` will be copied to your `config` folder.

After that, add these into your `.env` file:

```
CLOUDFLARE_KEY=
CLOUDFLARE_ZONE=
CLOUDFLARE_EMAIL=
```

## Usage

#### Generic PHP Projects

If you are on composer-enabled projects, the following instructions should work for you. Otherwise try requiring `src/CFStream.php` directly in your project instead.

```
use Fishr01\CFStream\CFStream;

$cfstream = new CFStream($key, $zone, $email);

$resourceUrl = $cfstream->upload($filepath);
$cfstream->status($resourceUrl);
$cfstream->code($resourceUrl);
$cfstream->allow($resourceUrl, 'example.com, *.example.net');
$cfstream->delete($resourceUrl);
```

#### Laravel Projects

If you have done the `vendor:publish` step, then `CFStream` can grab your credentials from the config file. You can use the `CFStreamLaravel` client instead.

```
use Fishr01\CFStream\CFStreamLaravel;

$cfstream = new CFStreamLaravel();

$resourceUrl = $cfstream->upload($filepath);
$cfstream->status($resourceUrl);
$cfstream->code($resourceUrl);
$cfstream->allow($resourceUrl, 'example.com, *.example.net');
$cfstream->delete($resourceUrl);
```

## Changelog

### 2019-04-02 - Namespace Changed

The namespace of this project has been updated from `JJSee` to `Fishr01`. Please update your projects accordingly. 

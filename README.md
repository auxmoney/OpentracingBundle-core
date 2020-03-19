# auxmoney OpentracingBundle

![release](https://github.com/auxmoney/OpentracingBundle-core/workflows/release/badge.svg)
![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/auxmoney/OpentracingBundle-core)
![Travis (.org)](https://img.shields.io/travis/auxmoney/OpentracingBundle-core)
![Coveralls github](https://img.shields.io/coveralls/github/auxmoney/OpentracingBundle-core)
![Codacy Badge](https://api.codacy.com/project/badge/Grade/fc044c88d4e046ab8813be04032a29a4)
![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/auxmoney/OpentracingBundle-core)
![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/auxmoney/OpentracingBundle-core)
![GitHub](https://img.shields.io/github/license/auxmoney/OpentracingBundle-core)

This collection of symfony bundles provides everything needed for a symfony application to enable distributed tracing.

It utilizes the [PHP implementation](https://github.com/opentracing/opentracing-php) of the [opentracing specification](https://opentracing.io/specification/) 
and wraps it in an opinionated fashion. It also aims to never disrupt your application, by not throwing exceptions and sending tracing data
to the agent as late as possible in the Symfony lifecycle.

The core contains:
* kernel/console event subscribers to automatically instrument the application, adding useful tags to root spans
* convenience functions to create tracing spans manually, including logging messages and tagging spans
* a convenience function for passing the tracing headers to PSR-7 requests manually

Additional bundles contain:
* [a monolog processor](https://github.com/auxmoney/OpentracingBundle-Monolog) to enrich log contexts with the current span context
* [Guzzle client](https://github.com/auxmoney/OpentracingBundle-Guzzle) automatic header injection

## Installation

### Choose tracer implementation

The core itself is only a library and should not be installed directly. You need to choose from different tracer implementation bundles, 
which will then use this library.

#### Jaeger

* require the dependencies (unfortunately, neither `opentracing/opentracing` nor `jukylin/jaeger-php` are released in a stable version right now):

```bash
    composer req auxmoney/opentracing-bundle-jaeger:^0.3 opentracing/opentracing:1.0.0-beta5@beta jukylin/jaeger-php:2.1.1-beta@beta
```

#### Zipkin

* require the dependencies (unfortunately, `opentracing/opentracing` is not released in a stable version right now):

```bash
    composer req auxmoney/opentracing-bundle-zipkin:^0.3 opentracing/opentracing:1.0.0-beta5@beta
```

### Enable the bundle

If you are using [Symfony Flex](https://github.com/symfony/flex), you are all set!

If you are not using it, you need to manually enable the bundle:

* add bundle to your application:

```php
    # Symfony 3: AppKernel.php
    $bundles[] = new Auxmoney\OpentracingBundle\OpentracingBundle();
```

```php
    # Symfony 4+: bundles.php
    Auxmoney\OpentracingBundle\OpentracingBundle::class => ['all' => true],
```

## Configuration

You can optionally configure environment variables, however, the default configuration will run fine out of the box for a tracing agent on localhost.
If you cannot change environment variables in your project, you can alternatively overwrite the container parameters directly.

| environment variable | container parameter | type | default | description |
|---|---|---|---|---|
| AUXMONEY_OPENTRACING_AGENT_HOST | auxmoney_opentracing.agent.host | `string` | `localhost` | hostname or IP of the agent |
| AUXMONEY_OPENTRACING_AGENT_PORT | auxmoney_opentracing.agent.port | `string` | (depends on the chosen tracer) | port of the agent |
| AUXMONEY_OPENTRACING_PROJECT_NAME | auxmoney_opentracing.project.name | `string` | `basename(kernel.project_dir)` |  passed to the tracer as tracer name / service name |

## Usage

### Propagation of tracing headers

For Guzzle clients, the [Guzzle bundle](https://github.com/auxmoney/OpentracingBundle-Guzzle) provides automatic tracing header injection.

If you do not use Guzzle, you need to inject the trace headers into every outgoing PSR-7 compatible request. To do so, simply use

```php
    Auxmoney\OpentracingBundle\Service\Tracing::injectTracingHeaders(Psr\Http\Message\RequestInterface $request): Psr\Http\Message\RequestInterface
```

on the request and use the resulting request with your favorite request client.

If you are using a request that is not PSR-7 compatible, you can inject the headers directly into an array using

```php
    Auxmoney\OpentracingBundle\Service\Tracing::injectTracingHeadersIntoCarrier(array $carrier): array
```

passing the array representing the headers of your request.

### Automatic tracing

Out of the box, the bundle will trace some spans automatically:
* span of the kernel lifecycle (from `kernel.request` to `kernel.finish_request`)
* span of controller lifecycles (from each `kernel.controller` to each `kernel.response`, including `kernel.exception`)
* span of the command lifecycle (from `console.command` to `console.terminate`, including `console.error`)

In case of exceptions thrown, it will additionally log exception types and messages to a controller/command span.

### Manual tracing

You can inject the tracing service automatically (via autowiring) or use the provided service alias `@auxmoney_opentracing`.

#### Manual spanning

You can define spans manually, by using

```php
    Auxmoney\OpentracingBundle\Service\Tracing::startActiveSpan(string $operationName, array $options = null): void
```

and 

```php
    Auxmoney\OpentracingBundle\Service\Tracing::finishActiveSpan(): void
```

respectively.

`$operationName` ist the displayed name of the trace operation, `$options` is an associative array of tracing options; the main usage is 
`$options['tags']`, which is an associative array of user defined tags (key value pairs). See the
[documentation for starting spans](https://github.com/opentracing/opentracing-php#using-startspanoptions) for more information.

#### Tagging spans

You can set tags (key value pairs) to the currently active span with

```php
    Auxmoney\OpentracingBundle\Service\Tracing::setTagOfActiveSpan(string $key, string|bool|int|float $value): void
```

You should respect the [span conventions of the opentracing project](https://github.com/opentracing/specification/blob/master/semantic_conventions.md#span-tags-table)
when setting tags to spans.

#### Logging in spans

You can always attach logs (key value pairs) to the currently active span with

```php
    Auxmoney\OpentracingBundle\Service\Tracing::logInActiveSpan(array $fields): void
```

You should respect the [log conventions of the opentracing project](https://github.com/opentracing/specification/blob/master/semantic_conventions.md#log-fields-table)
when logging fields.

## Development

Be sure to run

```bash
    composer run-script quality
```

every time before you push code changes. The tools run by this script are also run in the CI pipeline.

## Doc

Various informations regarding the bundle and its usage is available in the [doc section](./doc/README.md).

<?php

declare(strict_types=1);

namespace Auxmoney\OpentracingBundle\Service;

use OpenTracing\Exceptions\UnsupportedFormat;
use Psr\Http\Message\RequestInterface;

interface Tracing
{
    /**
     * Injects necessary tracing headers into an array.
     * @param array<mixed> $carrier
     * @return array<mixed>
     *
     * @throws UnsupportedFormat when the format is not recognized by the tracer
     */
    public function injectTracingHeadersIntoCarrier(array $carrier): array;

    /**
     * Injects necessary tracing headers into a RequestInterface.
     *
     * This is done automatically for:
     * - requests created with Guzzle
     */
    public function injectTracingHeaders(RequestInterface $request): RequestInterface;

    /**
     * Starts a new Span representing a unit of work.
     *
     * @param array<string,mixed>|null $options A set of optional parameters:
     *   - Zero or more references to related SpanContexts, including a shorthand for ChildOf and
     *     FollowsFrom reference types if possible.
     *   - Zero or more tags
     *   - FinishSpanOnClose option
     */
    public function startActiveSpan(string $operationName, array $options = null): void;

    /**
     * Adds a log record to the span in key => value format, key must be a string and tag must be either
     * a string, a boolean value, or a numeric type.
     *
     * @param array<string,string|bool|int|float> $fields
     */
    public function logInActiveSpan(array $fields): void;

    /**
     * Adds a tag to the span.
     *
     * If there is a pre-existing tag set for key, it is overwritten.
     *
     * @param string|bool|int|float $value
     */
    public function setTagOfActiveSpan(string $key, $value): void;

    /**
     * Finishes the currently active span.
     *
     * The user is responsible for finishing *exactly* as many Spans as he started.
     */
    public function finishActiveSpan(): void;

    /**
     * Sets a baggage item (key value pair of strings) to the active span.
     *
     * If there is a pre-existing tag set for key, it is overwritten.
     *
     * Use this feature thoughtfully and with care. Every key and value is copied into every local and remote child of
     * the associated Span, and that can add up to a lot of network and cpu overhead.
     */
    public function setBaggageItem(string $key, string $value): void;

    /**
     * Returns the value of a baggage item based on its key.
     *
     * If there is no value with such key it will return null.
     */
    public function getBaggageItem(string $key): ?string;
}

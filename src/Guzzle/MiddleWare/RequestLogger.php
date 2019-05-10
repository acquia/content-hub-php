<?php

namespace Acquia\ContentHubClient\Guzzle\Middleware;

use Closure;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RequestLogger.
 */
class RequestLogger
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Decoded response body.
     *
     * @var mixed
     */
    protected $decodedResponseBody;

    /**
     * RequestLogger constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs any response/request.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler): Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {

            $promise = function (ResponseInterface $response) use ($request) {
                try {
                    $this->withResponseAndRequest($response, $request)->log();
                } catch (Exception $exception) {
                    $message = sprintf('Failed to make log entry. Reason: %s', $exception->getMessage());
                    $this->logger->critical($message);
                }

                return $response;
            };

            return $handler($request, $options)->then($promise);
        };
    }

    /**
     * Logs response.
     */
    public function log(): void
    {
        if (!$this->isTrackable()) {
            return;
        }

        $entry = $this->buildLogEntry();

        $this->logEntry($entry, $this->response->getStatusCode());
    }

    /**
     * Initializes response and request properties.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *   Response instance.
     * @param \Psr\Http\Message\RequestInterface $request
     *   Request instance.
     *
     * @return $this
     */
    protected function withResponseAndRequest(ResponseInterface $response, RequestInterface $request)
    {
        $this->request = $request;
        $this->response = $response;
        $this->decodedResponseBody = json_decode($response->getBody(), true);

        return $this;
    }

    /**
     * Checks if a response can be tracked.
     *
     * @return bool
     */
    protected function isTrackable(): bool
    {
        if (empty($this->response) || empty($this->request)) {
            return false;
        }

        // Dont't track requests without ID.
        if (empty($this->decodedResponseBody['request_id'])) {
            return false;
        }

        // Dont't track requests with sensitive data.
        if (isset($this->decodedResponseBody['data']['data']['metadata']['settings'])) {
            return false;
        }

        return true;
    }

    /**
     * Builds log entry.
     *
     * @return string
     *   Log message.
     */
    protected function buildLogEntry(): string
    {
        return sprintf(
            'Request ID: %s. Method: %s. Path: %s. Status code: %d. Body: %s',
            $this->decodedResponseBody['request_id'],
            $this->request->getMethod(),
            $this->request->getUri()->getPath(),
            $this->response->getStatusCode(),
            $this->response->getBody()
        );
    }

    /**
     * Logs message depending on response status code.
     *
     * @param string $entry
     *   Log entry.
     * @param int $statusCode
     *   Response status code.
     */
    protected function logEntry(string $entry, int $statusCode): void
    {
        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->error($entry);

            return;
        }

        if ($statusCode >= Response::HTTP_BAD_REQUEST) {
            $this->logger->warning($entry);

            return;
        }

        $this->logger->info($entry);
    }
}

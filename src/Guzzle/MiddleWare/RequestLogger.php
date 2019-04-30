<?php

namespace Acquia\ContentHubClient\Guzzle\Middleware;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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
     * RequestLogger constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs any request.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler): \Closure
    {
        return function (Request $request, array $options) use ($handler) {

            $promise = function (ResponseInterface $response) use ($request) {
                try {
                    $this->logResponse($request, $response);
                } catch (\Exception $exception) {
                    $message = sprintf('Failed to log request. Reason: %s', $exception->getMessage());
                    $this->logger->critical($message);
                }

                return $response;
            };

            return $handler($request, $options)->then($promise);
        };
    }

    /**
     * Logs response.
     *
     * @param \GuzzleHttp\Psr7\Request $request
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function logResponse(Request $request, ResponseInterface $response): void {
        if (!$this->isTrackable($response)) {
            return;
        }

        $entry = implode('. ', $this->buildLogEntry($request, $response));

        $responseStatusCode = $response->getStatusCode();
        switch ($responseStatusCode) {
            case $responseStatusCode >= 500:
                $this->logger->error($entry);
                break;
            case $responseStatusCode >= 400:
                $this->logger->warning($entry);
                break;
            default:
                $this->logger->info($entry);
        }
    }

    /**
     * @param $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function buildLogEntry($request, ResponseInterface $response)
    {
        $body = $this->decodeResponse($response);

        $messages = [];
        $messages[] = sprintf('Request ID: %s', $body['request_id']);
        $messages[] = sprintf(
          'Method: %s. Path: %s. Status code: %d',
          $request->getMethod(),
          $request->getUri()->getPath(),
          $response->getStatusCode()
        );
        $messages[] = sprintf('Body: %s', $response->getBody());

        return $messages;
    }

    /**
     * Checks if a response can be tracked.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    protected function isTrackable(ResponseInterface $response): bool
    {
        $body = $this->decodeResponse($response);

        // Dont't track requests without ID.
        if (empty($body['request_id'])) {
            return false;
        }

        // Dont't track requests with sensitive data.
        if (isset($body['data']['data']['metadata']['settings'])) {
            return false;
        }

        return true;
    }

    /**
     * Decodes response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody(), true);
    }

}

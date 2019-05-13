<?php

namespace Acquia\ContentHubClient\Guzzle\Middleware;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RequestResponseLogger
 *
 * @package Acquia\ContentHubClient\Guzzle\Middleware
 */
class RequestResponseLogger
{
    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var mixed
     */
    private $decodedResponseBody;

    /**
     * RequestResponseLogger constructor.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
        $this->decodedResponseBody = json_decode($response->getBody(), true);
    }

    /**
     * Logs response/request data.
     *
     * @throws \Exception
     */
    public function log(): void
    {
        if (!$this->isTrackable()) {
            return;
        }

        $message = $this->buildLogMessage();

        $this->logMessage($message, $this->response->getStatusCode());
    }

    /**
     * Checks if a response can be tracked.
     *
     * @return bool
     * @throws \Exception
     */
    protected function isTrackable(): bool
    {
        // Dont't track requests without ID.
        if (empty($this->decodedResponseBody['request_id'])) {
            $message = sprintf(
                'Could not log response without ID. Response body: %s',
                $this->response->getBody()
            );
            throw new Exception($message);
        }

        // Dont't track requests with sensitive data.
        if (isset($this->decodedResponseBody['data']['data']['metadata']['settings'])) {
            return false;
        }

        return true;
    }

    /**
     * Builds log message.
     *
     * @return string
     *   Log message.
     */
    protected function buildLogMessage(): string
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
     * @param string $message
     *   Log message.
     * @param int $responseStatusCode
     *   Response status code.
     */
    protected function logMessage(string $message, int $responseStatusCode): void
    {
        if ($responseStatusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->error($message);

            return;
        }

        if ($responseStatusCode >= Response::HTTP_BAD_REQUEST) {
            $this->logger->warning($message);

            return;
        }

        $this->logger->info($message);
    }
}

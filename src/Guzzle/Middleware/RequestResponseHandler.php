<?php

namespace Acquia\ContentHubClient\Guzzle\Middleware;

use Closure;
use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RequestResponseHandler.
 */
class RequestResponseHandler {

  /**
   * Logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * RequestResponseHandler constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(LoggerInterface $logger) {
    $this->logger = $logger;
  }

  /**
   * Logs any response/request.
   *
   * @param callable $handler
   *   Request handler.
   *
   * @return \Closure
   *   Request handler.
   */
  public function __invoke(callable $handler): Closure {
    return function (RequestInterface $request, array $options) use ($handler) {
      $promise = function (ResponseInterface $response) use ($request) {
        try {
          (new RequestResponseLogger($request, $response,
            $this->logger))->log();
        }
        catch (Exception $exception) {
          $message = sprintf('Failed to make log entry. Reason: %s',
            $exception->getMessage());
          $this->logger->critical($message);
        }

        return $response;
      };

      return $handler($request, $options)->then($promise);
    };
  }

}

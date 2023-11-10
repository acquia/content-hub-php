<?php

namespace Acquia\ContentHubClient;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// @todo Remove `if condition` when we support guzzle >= 7.
if (defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')) {
  /**
   * Common client trait for guzzle 7 and above.
   */
  trait ContentHubClientCommonTrait {

    /**
     * The last call's response object.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * {@inheritdoc}
     */
    public function request(string $method, $uri, array $options = []): ResponseInterface {
      try {
        return $this->httpClient->request($method, $uri, $options);
      }
      catch (\Exception $e) {
        $response = $this->getExceptionResponse($method, $uri, $e);
        $this->response = $response;
        return $response;
      }
    }

    /**
     * Returns the response object from the last call.
     *
     * In case further examination needed e.g. status code or error message.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   The response object.
     */
    public function getResponse(): ResponseInterface {
      return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface {
      try {
        return $this->httpClient->requestAsync($method, $uri, $options);
      }
      catch (\Exception $e) {
        return $this->getExceptionResponse($method, $uri, $e);
      }
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface {
      try {
        return $this->httpClient->send($request, $options);
      }
      catch (\Exception $e) {
        return $this->getExceptionResponse($request->getMethod(), $request->getUri()->getPath(), $e);
      }
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface {
      try {
        return $this->httpClient->sendAsync($request, $options);
      }
      catch (\Exception $e) {
        return $this->getExceptionResponse($request->getMethod(), $request->getUri()->getPath(), $e);
      }
    }

    /**
     * Get a client configuration option.
     *
     * @param string|null $option
     *   The config option to retrieve.
     *
     * @return mixed
     *   The client configurations.
     */
    public function getConfig(?string $option = NULL) {
      return $option === NULL
        ? $this->config
        : ($this->config[$option] ?? NULL);
    }

  }
}
else {
  /**
   * Common client trait for guzzle 6.
   */
  trait ContentHubClientCommonTrait {

    /**
     * The last call's response object.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * {@inheritdoc}
     */
    public function request($method, $uri, array $options = []) {
      try {
        return $this->httpClient->request($method, $uri, $options);
      }
      catch (\Exception $e) {
        $response = $this->getExceptionResponse($method, $uri, $e);
        $this->response = $response;
        return $response;
      }
    }

    /**
     * Returns the response object from the last call.
     *
     * In case further examination needed e.g. status code or error message.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   The response object.
     */
    public function getResponse(): ResponseInterface {
      return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function requestAsync($method, $uri, array $options = []) {
      try {
        return $this->httpClient->requestAsync($method, $uri, $options);
      }
      catch (\Exception $e) {
        return $this->getExceptionResponse($method, $uri, $e);
      }
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request, array $options = []) {
      try {
        return $this->httpClient->send($request, $options);
      }
      catch (\Exception $e) {
        return $this->getExceptionResponse($request->getMethod(), $request->getUri()->getPath(), $e);
      }
    }

    /**
     * {@inheritdoc}
     */
    public function sendAsync(RequestInterface $request, array $options = []) {
      try {
        return $this->httpClient->sendAsync($request, $options);
      }
      catch (\Exception $e) {
        return $this->getExceptionResponse($request->getMethod(), $request->getUri()->getPath(), $e);
      }
    }

    /**
     * Get a client configuration option.
     *
     * @param string|null $option
     *   The config option to retrieve.
     *
     * @return mixed
     *   The client configurations.
     */
    public function getConfig($option = NULL) {
      return $option === NULL
        ? $this->config
        : ($this->config[$option] ?? NULL);
    }

  }
}

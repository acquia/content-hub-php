<?php
/**
 * @file
 * HMAC Response Signer
 */

namespace Acquia\ContentHubClient;

use Symfony\Component\HttpFoundation\Response;

class ResponseSigner extends Response {

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @var string $secretKey
     */
    protected $secretKey;

    /**
     * @var string $provider
     */
    protected $provider = 'Acquia';

    /**
     * @var string $algorithm
     */
    protected $algorithm = 'sha256';

    /**
     * @var string $method
     */
    protected $method = 'POST';

    /**
     * @var string $resource
     */
    protected $resource = '';

    /**
     * @var string $defaultContentType
     */
    protected $defaultContentType = 'application/json';

    /**
     * @var bool $signWithCustomHeaders
     */
    protected $signWithCustomHeaders = true;

    /**
     * Public constructor
     *
     * @param string $apiKey
     * @param string $secretKey
     * @param int    $status
     * @param array  $headers
     */
    public function __construct($apiKey, $secretKey, $status = 200, array $headers = [])
    {
        $headers += [
           'Content-Type' => $this->defaultContentType,
        ];
        parent::__construct($content = '', $status, $headers);

        // Set the date.
        $this->headers->set('Date', gmdate("D, d M Y H:i:s T"));

        // Setting up the keys.
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    /**
     * Whether to sign using custom headers or not.
     *
     * @param bool $sign
     */
    public function signWithCustomHeaders($sign = true)
    {
        $this->signWithCustomHeaders = $sign;
    }

    /**
     * Obtains the hashed body of the response.
     *
     * @return string
     */
    protected function getHashedBody()
    {
        return md5($this->getContent());
    }

    /**
     * Sets the method.
     *
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($this->method);
    }

    /**
     * Obtains the Content-Type Header.
     *
     * @return string
     */
    public function getContentType()
    {
        return strtolower($this->headers->get('Content-Type'));
    }

    /**
     * Sets the Custom Headers.
     *
     * @param array $headers
     */
    public function setCustomHeaders($headers = [])
    {
        $this->headers->replace($headers);
    }

    /**
     * Gets the Custom Headers.
     *
     * @return string
     */
    public function getCustomHeaders()
    {
        $headers = $this->headers->all();

        $canonicalizedHeaders = array();
        foreach ($headers as $header => $value) {
            $canonicalizedHeaders[] = strtolower($header) . ': ' . $this->headers->get($header);
        }

        sort($canonicalizedHeaders);
        return join("\n", $canonicalizedHeaders);
    }

    /**
     * Gets the Header 'Date'
     *
     * @return array|string
     */
    protected function getTimestamp()
    {
        return $this->headers->get('Date');
    }

    /**
     * Sets the Resource.
     *
     * @param $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Obtains the resource.
     *
     * @return string
     */
    protected  function getResource()
    {
        return $this->resource;
    }

    /**
     * Creates the message to sign.
     *
     * @return string
     */
    protected function getMessage()
    {
        $parts = array(
            $this->getMethod(),
            $this->getHashedBody(),
            $this->getContentType(),
            $this->getTimestamp(),
            $this->signWithCustomHeaders ? $this->getCustomHeaders() : '',
            $this->getResource(),
        );

        return join("\n", $parts);
    }

    /**
     * Obtains the signature.
     *
     * @return string
     */
    protected function getSignature()
    {
      $message = $this->getMessage();
      $digest = hash_hmac($this->algorithm, $message, $this->secretKey, true);
      return base64_encode($digest);
    }

    /**
     * Signs the response.
     */
    public function signResponse()
    {
        $signature = $this->getSignature();
        $signedMessage = $this->provider . ' ' . $this->apiKey . ':' . $signature;
        $this->headers->set('X-Acquia-Plexus-Authorization', $signedMessage);
    }

}
<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ContentHubClient extends Client
{
    // Override VERSION inherited from GuzzleHttp::ClientInterface
    const VERSION = '2.0.0';
    const LIBRARYNAME = 'AcquiaContentHubPHPLib';

    /**
     * The settings.
     *
     * @var \Acquia\ContentHubClient\Settings
     */
    protected $settings;

    /**
     * The logger responsible for tracking request failures.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Overrides \GuzzleHttp\Client::__construct()
     *
     * @param array $config
     * @param \Acquia\ContentHubClient\Settings $settings
     * @param \Acquia\Hmac\Guzzle\HmacAuthMiddleware $middleware
     * @param string $api_version
     */
    public function __construct(array $config = [], LoggerInterface $logger, Settings $settings, HmacAuthMiddleware $middleware, $api_version = 'v1')
    {
        $this->logger = $logger;
        $this->settings = $settings;
        // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
        // is there to make sure it does not disrupt previous configuration.
        if (!isset($config['base_uri']) && isset($config['base_url'])) {
            $config['base_uri'] = "{$config['base_url']}/$api_version";
        }
        else {
          $config['base_uri'] = "{$config['base_uri']}/$api_version";
        }

        // Setting up the User Header string
        $user_agent_string = self::LIBRARYNAME . '/' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent();
        if (isset($config['client-user-agent'])) {
            $user_agent_string = $config['client-user-agent'] . ' ' . $user_agent_string;
        }

        // Setting up the headers.
        $config['headers']['Content-Type'] = 'application/json';
        $config['headers']['X-Acquia-Plexus-Client-Id'] = $settings->getUuid();
        $config['headers']['User-Agent'] = $user_agent_string;

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        if (!isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }
        $config['handler']->push($middleware);

        parent::__construct($config);
    }

    /**
     * Pings the service to ensure that it is available.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     *
     * @since 0.2.0
     */
    public function ping()
    {
        $config = $this->getConfig();
        // Create a new client because ping is not behind hmac.
        $client = new Client(['base_uri' => $config['base_uri']]);
        return $this->getResponseJson($client->get("/ping"));
    }

    /**
     * Discoverability of the API
     *
     * @param string $endpoint
     *
     * @return array
     */
    public function definition($endpoint = '')
    {
        return $this->getResponseJson($this->request('options', $endpoint));
    }

    /**
     * Registers a new client for the active subscription.
     *
     * This method also returns the UUID for the new client being registered.
     *
     * @param string $name
     *   The human-readable name for the client.
     *
     * @return \Acquia\ContentHubClient\ContentHubClient
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public static function register(LoggerInterface $logger, $name, $url, $api_key, $secret, $api_version = 'v1')
    {
        $config = [
            'base_uri' => "$url/$api_version",
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => self::LIBRARYNAME . '/' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
            ],
            'handler' => HandlerStack::create(),
        ];

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        $key = new Key($api_key, $secret);
        $middleware = new HmacAuthMiddleware($key);
        $config['handler']->push($middleware);
        $client = new Client($config);
        $options['body'] = json_encode(['name' => $name]);
        try {
            $response = $client->post("/register", $options);
            $values = self::getResponseJson($response);
            $settings = new Settings($values['name'], $values['uuid'], $api_key, $secret, $url);
            $config = [
                'base_url' => $settings->getUrl()
            ];
            $client = new static($config, $logger, $settings, $settings->getMiddleware());
            // @todo remove this once shared secret is returned on the register
            // endpoint.
            // We need the shared secret to be fully functional, so an additional
            // request is required to get that.
            $remote = $client->getRemoteSettings();
            // Now that we have the shared secret, reinstantiate everything and
            // return a new instance of this class.
            $settings = new Settings($settings->getName(), $settings->getUuid(), $settings->getApiKey(), $settings->getSecretKey(), $settings->getUrl(), $remote['shared_secret']);
            return new static($config, $logger, $settings, $settings->getMiddleware());
        }
        catch (\Exception $exception) {
            if ($exception instanceof ClientException || $exception instanceof BadResponseException) {
              $logger->error(sprintf('Error registering client with name="%s" (Error Code = %d: %s)', $name, $exception->getResponse()->getStatusCode(), $exception->getResponse()->getReasonPhrase()));
              return;
            }
            if ($exception instanceof RequestException) {
              $logger->error(sprintf('Could not get authorization from Content Hub to register client %s. Are your credentials inserted correctly? (Error message = %s)', $name, $exception->getMessage()));
              return;
            }
            $logger->error(sprintf("An unknown exception was caught. Message: %s", $exception->getMessage()));
            return;
        }
    }

    /**
     * Checks Plexus to see if the client name is already in use.
     *
     * @param $name
     * @param $url
     * @param $api_key
     * @param $secret
     * @param string $api_version
     *
     * @return string $client_name
     *   The human-readable name for the client.
     */
    public static function clientNameExists($name, $url, $api_key, $secret, $api_version = 'v1') {
        $config = [
            'base_uri' => "$url/$api_version",
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => self::LIBRARYNAME . '/' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
            ],
            'handler' => HandlerStack::create(),
        ];

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        $key = new Key($api_key, $secret);
        $middleware = new HmacAuthMiddleware($key);
        $config['handler']->push($middleware);
        $client = new Client($config);
        $options['body'] = json_encode(['name' => $name]);
        try {
          $response = $client->get("/settings/clients/{$name}");
        }
        catch (\GuzzleHttp\Exception\ClientException $error) {
          return $error->getResponse()->getStatusCode() != 404;
        }
    }

  /**
   * Sends request to asynchronously create entities.
   *
   * @param \Acquia\ContentHubClient\CDFObject[] $objects
   *   Individual CDFObjects to send to ContentHub.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   */
    public function createEntities(CDFObject ...$objects)
    {
        $json = [
          'resource' => "",
        ];
        foreach ($objects as $object) {
          $json['entities'][] = $object->toArray();
        }
        $options['body'] = json_encode($json);
        return $this->post("/entities", $options);
    }

    /**
     * Returns an entity by UUID.
     *
     * @param  string                               $uuid
     *
     * @return \Acquia\ContentHubClient\CDFObject|array
     *   A CDFObject representing the entity or an array if there was no data.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     *
     * @todo can we return a CDFObject here?
     */
    public function getEntity($uuid)
    {
        $return = $this->getResponseJson($this->get("/entities/{$uuid}"));
        if (!empty($return['data']['data'])) {
          $data = $return['data']['data'];
          $object = new CDFObject($data['type'], $data['uuid'], $data['created'], $data['modified'], $data['origin']);
          foreach ($data['attributes'] as $key => $attribute) {
            $cdfAttribute = new CDFAttribute($key, $attribute['type'], $attribute['value']);
            $object->addAttribute($cdfAttribute);
          }
          return $object;
        }
        return $return;
    }

  /**
   * Updates an entity asynchronously.
   *
   * The entity does not need to be passed to this method, but only the resource URL.
   *
   * @param \Acquia\ContentHubClient\CDFObject $object
   *   The CDFObject
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   */
    public function putEntity(CDFObject $object)
    {
        $options['body'] = json_encode(['entities' => [$object->toArray()], 'resource' => ""]);
        return $this->put("/entities/{$object->getUuid()}", $options);
    }

  /**
   * Updates many entities asynchronously.
   *
   * @param \Acquia\ContentHubClient\CDFObject[] $objects
   *   The CDFObjects to update.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   */
    public function putEntities(CDFObject ...$objects)
    {
        $json = [
          'resource' => "",
        ];
        foreach ($objects as $object) {
          $json['entities'][] = $object->toArray();
        }
        $options['body'] = json_encode($json);
        return $this->put("/entities", $options);
    }

    /**
     * Deletes an entity by UUID.
     *
     * @param  string                                 $uuid
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function deleteEntity($uuid)
    {
        return $this->delete("/entities/{$uuid}");
    }

    /**
     * Purges all entities from the Content Hub.
     *
     * This method should be used carefully as it deletes all the entities for
     * the current subscription from the Content Hub. This creates a backup that
     * can be restored at any time. Any subsequent purges overwrite the existing
     * backup. Be VERY careful when using this endpoint.
     *
     * @return mixed
     */
    public function purge()
    {
        return $this->getResponseJson($this->post("/entities/purge"));
    }

    /**
     * Restores the state of entities before the previous purge.
     *
     * Only to be used if a purge has been called previously. This means new
     * entities added after the purge was enacted will be overwritten by the
     * previous state. Be VERY careful when using this endpoint.
     *
     * @return mixed
     */
    public function restore()
    {
        return $this->getResponseJson($this->post("/entities/restore"));
    }
    /**
     * Reindex a subscription.
     *
     * Schedules a reindex process.
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function reindex()
    {
        return $this->getResponseJson($this->post("/reindex"));
    }

    /**
     * Obtains Customer-Facing-Logs for the subscription.
     *
     * This is forward search request to Elastic Search.
     *
     * @param string $query
     *   An elastic search query.
     * @param array $query_options
     *   An array with the number of items to show in the list and offset.
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function logs($query = '', $query_options = [])
    {
        $query_options = $query_options + [
            'size' => 20,
            'from' => 0,
            'sort' => 'timestamp:desc'
        ];
        $options['body'] = empty($query) ? '{"query": {"match_all": {}}}' : $query;
        $endpoint = "/history?size={$query_options['size']}&from={$query_options['from']}&sort={$query_options['sort']}";
        $response = $this->post($endpoint, $options);
        return $this->getResponseJson($response);
    }

    /**
     * Retrieves active ElasticSearch mapping of entities.
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function mapping()
    {
        return $this->getResponseJson($this->get("/_mapping"));
    }

    /**
     * Lists Entities from the Content Hub.
     *
     * Example of how to structure the $options parameter:
     * <code>
     * $options = [
     *     'limit'  => 20,
     *     'type'   => 'node',
     *     'origin' => '11111111-1111-1111-1111-111111111111',
     *     'fields' => 'status,title,body,field_tags,description',
     *     'filters' => [
     *         'status' => 1,
     *         'title' => 'New*',
     *         'body' => '/Boston/',
     *     ],
     * ];
     * </code>
     *
     * @param array $options
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function listEntities($options = [])
    {
        $variables = $options + [
            'limit' => 1000,
            'start' => 0,
            'filters' => [],
        ];

        $url = "/entities?limit={$variables['limit']}&start={$variables['start']}";

        $url .= isset($variables['type']) ? "&type={$variables['type']}" :'';
        $url .= isset($variables['origin']) ? "&origin={$variables['origin']}" :'';
        $url .= isset($variables['language']) ? "&language={$variables['language']}" :'';
        $url .= isset($variables['fields']) ? "&fields={$variables['fields']}" :'';
        foreach ($variables['filters'] as $name => $value) {
            $filter = 'filter_' . $name;
            $variables[$filter] = $value;
            $url .= isset($value) ? sprintf('&filter:%s=%s', $name, $value) : '';
        }

        // Now make the request.
        return $this->getResponseJson($this->get($url));
    }

    /**
     * Searches for entities.
     *
     * @param  array                                  $query
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function searchEntity($query)
    {
        $options['body'] = json_encode((array) $query);
        return $this->getResponseJson($this->get("/_search", $options));
    }

    /**
     * Returns the Client, given the site name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getClientByName($name)
    {
        return $this->getResponseJson($this->get("/settings/clients/{$name}"));
    }

    public function getClients()
    {
      $data = $this->getResponseJson($this->get("/settings"));
      return $data['clients'];
    }

    public function getWebHooks()
    {
        $data = $this->getResponseJson($this->get("/settings"));
        return $data['webhooks'];
    }

    public function getWebHook($url)
    {
        $webhooks = $this->getWebHooks();
        foreach ($webhooks as $webhook) {
            if ($webhook['url'] == $url) {
                return $webhook;
            }
        }
        return [];
    }

    /**
     * Get the settings that were used to instantiate this client.
     *
     * @return \Acquia\ContentHubClient\Settings
     */
    public function getSettings() {
      return $this->settings;
    }

    /**
     * Obtains the Settings for the active subscription.
     *
     * @return Settings
     */
    public function getRemoteSettings()
    {
        return $this->getResponseJson($this->get("/settings"));
    }

    /**
     * Adds a webhook to the active subscription.
     *
     * @param $webhook_url
     *
     * @return mixed
     */
    public function addWebhook($webhook_url)
    {
        $options['body'] = json_encode(['url' => $webhook_url, 'version' => 2.0]);
        return $this->getResponseJson($this->post("/settings/webhooks", $options));
    }

    /**
     * Deletes a webhook from the active subscription.
     *
     * @param $uuid
     *   The UUID of the webhook to delete
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function deleteWebhook($uuid)
    {
        return $this->delete("/settings/webhooks/{$uuid}");
    }

  /**
   * Updates a webhook from the active subscription.
   *
   * @param $uuid
   *   The UUID of the webhook to update
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function updateWebhook($uuid, $url)
  {
    $options['body'] = json_encode(['url' => $url]);
    return $this->put("/settings/webhooks/{$uuid}", $options) ;
  }

  /**
   * Deletes a webhook from the active subscription.
   *
   * @param $uuid
   *   The UUID of the webhook to delete
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
   public function deleteClient()
   {
     $settings = $this->getSettings();
     return $this->delete("/settings/client/uuid/{$settings->getUuid()}");
   }


  /**
     * Regenerates a Shared Secret for the Subscription.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function regenerateSharedSecret()
    {
        return $this->getResponseJson($this->post("/settings/secret", ['body' => json_encode([])]));
    }

  /**
   * Gets a Json Response from a request.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *
   * @return mixed
   * @throws \Exception
   */
    public static function getResponseJson(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        return json_decode($body, TRUE);
    }

    public function __call($method, $args) {
        try {
            return parent::__call($method, $args);
        }
        catch (\Exception $e) {
            $exceptionResponse = $this->getExceptionMessage($method, $args, $e);
        }
        $this->logger->error((string) $exceptionResponse->getBody());
        return $exceptionResponse;
    }

  /**
   * Obtains the appropriate exception message for the selected exception.
   *
   * This is the place to set up exception messages per request.
   *
   * @param string $method
   *   The Request to Plexus, as defined in the content-hub-php library.
   * @param array $args
   *   The Request arguments.
   * @param \Exception $exception
   *   The Exception object.
   *
   * @return ResponseInterface The text to write in the messages.
   * The text to write in the messages.
   */
  protected function getExceptionMessage($method, array $args, \Exception $exception) {
    if ($exception instanceof ServerException) {
      return $this->getErrorResponse(500, sprintf('Could not reach the Content Hub. Please verify your hostname and Credentials. [Error message: %s]', $exception->getMessage()));
    }
    if ($exception instanceof ConnectException) {
      return $this->getErrorResponse(500, sprintf('Could not reach the Content Hub. Please verify your hostname URL. [Error message: %s]', $exception->getMessage()));
    }
    if ($exception instanceof ClientException || $exception instanceof BadResponseException) {
      $response = $exception->getResponse();
      switch ($method) {
        case 'getClientByName':
          // All good, means the client name is available.
          if ($response->getStatusCode() == 404) {
            return $response;
          }
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to connect to the Content Hub" (Error Code = %d: %s)', $response->getStatusCode(), $response->getReasonPhrase()));

        case 'addWebhook':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('There was a problem trying to register Webhook URL = %s. Please try again. (Error Code = %d: %s)', $args[0], $response->getStatusCode(), $response->getReasonPhrase()));

        case 'deleteWebhook':
          // This function only requires one argument (webhook_uuid), but
          // we are using the second one to pass the webhook_url.
          $webhook_url = isset($args[1]) ? $args[1] : $args[0];
          return $this->getErrorResponse($response->getStatusCode(), sprintf('There was a problem trying to <b>unregister</b> Webhook URL = %s. Please try again. (Error Code = %d: @%s)', $webhook_url, $response->getStatusCode(), $response->getReasonPhrase()));

        case 'purge':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error purging entities from the Content Hub [Error Code = %d: %s]', $response->getStatusCode(), $response->getReasonPhrase()));

        case 'readEntity':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error reading entity with UUID="%s" from Content Hub (Error Code = %d: %s)', $args[0], $response->getStatusCode(), $response->getReasonPhrase()));

        case 'createEntity':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to create an entity in Content Hub (Error Code = %d: %s)', $response->getStatusCode(), $response->getReasonPhrase()));

        case 'createEntities':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to create entities in Content Hub (Error Code = %d: %s)', $response->getStatusCode(), $response->getReasonPhrase()));

        case 'updateEntity':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to update an entity with UUID="%s" in Content Hub (Error Code = %d: %s)', $args[1], $response->getStatusCode(), $response->getReasonPhrase()));

        case 'updateEntities':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to update some entities in Content Hub (Error Code = %d: %s)', $response->getStatusCode(), $response->getReasonPhrase()));

        case 'deleteEntity':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to delete entity with UUID="@uuid" in Content Hub (Error Code = @error_code: @error_message)', $args[0], $response->getStatusCode(), $response->getReasonPhrase()));

        case 'searchEntity':
          return $this->getErrorResponse($response->getStatusCode(), sprintf('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Code = %d: %s)', $response->getStatusCode(), $response->getReasonPhrase()));

        default:
          return $response;
      }
    }
    if ($exception instanceof RequestException) {
      switch ($method) {
        // Customize the error message per request here.
        case 'createEntity':
          return $this->getErrorResponse(500, sprintf('Error trying to create an entity in Content Hub (Error Message: %s)', $exception->getMessage()));

        case 'createEntities':
          return $this->getErrorResponse(500, sprintf('Error trying to create entities in Content Hub (Error Message = %s)', $exception->getMessage()));

        case 'updateEntity':
          return $this->getErrorResponse(500, sprintf('Error trying to update entity with UUID="%s" in Content Hub (Error Message = %s)', $args[1], $exception->getMessage()));

        case 'updateEntities':
          return $this->getErrorResponse(500, sprintf('Error trying to update some entities in Content Hub (Error Message = %s)', $exception->getMessage()));

        case 'deleteEntity':
          return $this->getErrorResponse(500, sprintf('Error trying to delete entity with UUID="%s" in Content Hub (Error Message = %s)', $args[0], $exception->getMessage()));

        case 'searchEntity':
          return $this->getErrorResponse(500, sprintf('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Message = %s)', $exception->getMessage()));

        default:
          return $this->getErrorResponse(500, sprintf('Error trying to connect to the Content Hub. Are your credentials inserted correctly? (Error Message = %s)', $exception->getMessage()));
      }
    }
    return $this->getErrorResponse(500, sprintf('Error trying to connect to the Content Hub (Error Message = %s)', $exception->getMessage()));
  }

  protected function getErrorResponse($code, $reason) {
    return new Response($code, [], json_encode([]), '1.1', $reason);
  }

}

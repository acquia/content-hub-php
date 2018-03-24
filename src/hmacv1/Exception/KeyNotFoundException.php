<?php

namespace Acquia\ContentHubClient\hmacv1\Exception;

/**
 * Exception thrown for requests that are properly formed but are not
 * authenticated due to an invalid signature.
 */
class KeyNotFoundException extends InvalidRequestException {}

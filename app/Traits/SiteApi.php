<?php

namespace App\Traits;

use Akaunting\Version\Version;
use App\Utilities\Info;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

trait SiteApi
{
    public static $base_uri = 'https://api.akaunting.com/';

    protected static function siteApiRequest($method, $path, $extra_data = [])
    {
        return null;
    }

    public static function getResponse($method, $path, $data = [], $status_code = 200)
    {
        $response = static::siteApiRequest($method, $path, $data);

        $is_exception = (($response instanceof ConnectException) || ($response instanceof Exception) || ($response instanceof RequestException));

        if (!$response || $is_exception || ($response->getStatusCode() != $status_code)) {
            return false;
        }

        return $response;
    }

    public static function getResponseBody($method, $path, $data = [], $status_code = 200)
    {
        if (! $response = static::getResponse($method, $path, $data, $status_code)) {
            return [];
        }

        $body = json_decode($response->getBody());

        return $body;
    }

    public static function getResponseData($method, $path, $data = [], $status_code = 200)
    {
        if (! $body = static::getResponseBody($method, $path, $data, $status_code)) {
            return [];
        }

        if (! is_object($body)) {
            return [];
        }

        return $body->data;
    }
}

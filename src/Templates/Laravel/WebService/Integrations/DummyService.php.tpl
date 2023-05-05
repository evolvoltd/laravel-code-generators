<?php

namespace App\Services\Integrations;

use App\Exceptions\Dummy\ResponseException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Http;

class DummyService
{
    private $http;
    public $requestHeaders;
    public $requestBody;
    public $responseStatus;
    public $responseHeaders;
    public $responseBody;

    public function __construct()
    {
        if (!filter_var(config('services.webserviceName.connection_url'), FILTER_VALIDATE_URL))
            throw new \Exception('Bad webservice Dummy connection url!:' . config('services.webserviceName.connection_url'), 500);

        $this->http = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->baseUrl(config('services.webserviceName.connection_url'));
    }

    private function call($method, $uri, $data = null, $headers = [])
    {
        try {
            $response = $this->http->withHeaders($headers)->$method($uri, $data);

            $this->requestBody = $data;
            $this->requestHeaders = $headers;
            $this->responseStatus = $response->getStatusCode();
            $this->responseHeaders = $response->getHeaders();
            $this->responseBody = $response->getBody()->getContents();
            $logData = [
                'url' => 'POST ' . request()->fullUrl(),
                'body' => $data,
                'response_code' => $this->responseStatus,
                'response_body' => $this->responseBody
            ];
            logger()->channel('web-service-WEBSERVICE-NAME-data')->info(json_encode($logData, JSON_PRETTY_PRINT));

            return $response->json();
        } catch (ServerException $e) {
            $this->responseStatus = $e->getResponse()->getStatusCode();
            $this->responseHeaders = $e->getResponse()->getHeaders();
            $this->responseBody = $e->getResponse()->getBody()->getContents();
            $this->requestBody = $data;

            $logData = [
                'url' => 'POST ' . request()->fullUrl(),
                'body' => $data,
                'response_code' => $this->responseStatus,
                'response_body' => $this->responseBody
            ];
            logger()->channel('web-service-WEBSERVICE-NAME-data')->info(json_encode($logData, JSON_PRETTY_PRINT));

            throw new ResponseException("WEBSERVICE_NAME webservice error. Check response contents by calling accessing responseBody parameter.");
        }
    }

    public function getData()
    {
        try {
            return $this->call('get', 'api/test-data', [], []);

        } catch (ResponseException $e) {
            // examine
            // $this->webservice->requestBody,
            // $this->webservice->responseStatus,
            // $this->webservice->responseBody if needed
            throw $e;
        }
    }
}

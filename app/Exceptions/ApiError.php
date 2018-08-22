<?php

namespace App\Exceptions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

final class ApiError extends \Slim\Handlers\Error
{
    protected $logger;

    public function __construct($displayErrorDetails,Logger $logger)
    {
        parent::__construct($displayErrorDetails);
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        // Log the message
        $this->logger->critical($exception->getMessage());

        // create a JSON error string for the Response body
        $body = json_encode([
            'error' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $response
            ->withStatus(500)
            ->withHeader('Content-type', 'application/json')
            ->withBody(new Body(fopen('php://temp', 'r+')))
            ->write($body);
    }
}
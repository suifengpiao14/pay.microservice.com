<?php

namespace App\Exceptions;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;

final class Error extends \Slim\Handlers\Error
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
        $context=[
            'file'=>$exception->getFile(),
            'line'=>$exception->getLine(),
            'code'=>$exception->getCode(),
            'previous'=>$exception->getPrevious(),
            'trace'=>$exception->getTrace(),
        ];
        $this->logger->error($exception->getMessage(),$context);

        return parent::__invoke($request, $response, $exception);
    }
}
<?php declare(strict_types=1);

use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;

class HelloHandler implements RequestHandler
{
    public function handleRequest(Request $request): Response
    {
        return new Response(200, [], 'Hi world!');
    }
}

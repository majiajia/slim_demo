<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
class ExampleMiddleware
{
    public function __invoke(Request $request,Response $response,$next)
    {
        $response->getBody()->write("example class before");
        $response = $next($request,$response);
        $response->getBody()->write("example class after");
        return $response;
    }
}
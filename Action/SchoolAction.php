<?php
    use Psr\Container\ContainerInterface as ContainerInterface;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    class SchoolAction
    {
        protected $container;

        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;
        }

        public function __invoke(Request $request,Response $response,$args)
        {
            $response = $this->container->get('name');
            return $response;
        }
    }
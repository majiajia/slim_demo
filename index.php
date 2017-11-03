<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    require_once 'vendor/autoload.php';

    $config = [
        "displayErrorDetails"=>true,
        "addContentLengthHeader"=>false,
        "db"=>[
            "host"=>"123.207.115.220",
            "user"=>"zhongmeng",
            "pass"=>"zhong1104",
            "dbname"=>"xcx_meitu",
        ]
    ];
    $app = new \Slim\App(['settings'=>$config]);

    $container = $app->getContainer();
    $container["logger"] = function ($c) {
        $logger = new \Monolog\Logger("slim_logger");
        $file_handle = new \Monolog\Handler\StreamHandler("./logs/slim_demo.log");
        $logger->pushHandler($file_handle);
        return $logger;
    };

    $container["db"] = function ($c) {
        $db = $c["settings"]["db"];
        $pdo = new PDO("mysql:host=".$db["host"].";dbname=".$db["dbname"],$db["user"],$db["pass"]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        return $pdo;
    };

    $app->get('/hello/{name}',function (Request $request,Response $response) {
        $name = $request->getAttribute('name');
        $this->logger->addDebug("param:".$name);
        $response->getBody()->write($name);

        return $response;
    });

    $app->get('/tickets',function (Request $request,Response $response){
        $this->logger->addDebug("this is tickets api");
        $response->getBody()->write("");
        return $response;
    });
    $app->run();
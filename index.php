<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    require_once 'vendor/autoload.php';

    $config = [
        "displayErrorDetails"=>true,
        "addContentLengthHeader"=>false,
        "db"=>[
            'driver' => 'mysql',
            "host"=>"123.207.115.220",
            'database' => 'xcx_meitu',
            'username' => 'zhongmeng',
            'password' => 'zhong1104',
            'charset'   => 'utf8',
            'collation' => 'utf8_genernal_ci',
            'prefix'    => '',
        ],
        'determineRouteBeforeAppMiddleware' => true,

//        "routerCacheFile"=>__DIR__."/route_cache.txt",
    ];
    $app = new \Slim\App(['settings'=>$config]);

    $container = $app->getContainer();
    $container["logger"] = function ($c) {
        $logger = new \Monolog\Logger("slim_logger");
        $file_handle = new \Monolog\Handler\StreamHandler("./logs/slim_demo.log");
        $logger->pushHandler($file_handle);
        return $logger;
    };
    $container['errorHandler'] = function ($c) {
        return function (Request $request,Response $response,\Exception $exception) use($c) {
            return $c['response']->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write('Something went wrong!');
        };
    };
    $container['db'] = function ($container) {
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection($container['settings']['db']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    };
    /*
     * it show how to use mysql
    $container["db"] = function ($c) {
        $db = $c["settings"]["db"];
        $pdo = new PDO("mysql:host=".$db["host"].";dbname=".$db["dbname"],$db["user"],$db["pass"]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        return $pdo;
    };
    */
    $mw = function (Request $request,Response $response,$next) {
        $response->getBody()->write("  before  ");
        $response = $next($request,$response);
        $response->getBody()->write("  after  ");
        return $response;
    };
    $app->add(function (Request $req,Response $res,$next){
        $req->registerMediaTypeParser(
            'text/javascript',
            function ($input){
                return json_decode($input,true);
            }
        );
        return $next($req,$res);
    });
    $app->add(function (Request $req,Response $res,$next){
        $req = $req->withAttribute('name',rand(1,10000));
        return $next($req,$res);
    });
    $app->get('/hello/{name}',function (Request $request,Response $response) {
        $name = $request->getAttribute('name');
        $this->logger->addDebug("param:".$name);
        $response->getBody()->write($name);
        if($this->has("logger")) {
            $response->getBody()->write("have di logger");
        } else {
            $response->getBody()->write("donnot have di logger");
        }
        return $response;
    })->add($mw);

    $app->get('/tickets/',function (Request $request,Response $response){
        $this->logger->addDebug("this is tickets api");
        $response->getBody()->write("");
        return $response;
    });
    $app->get('/tickets1',function (Request $request,Response $response){
        $this->logger->addDebug("this is tickets api");
        $response->getBody()->write("");
        return $response;
    });
    $app->group('/utils',function () use ($app){
//        http://127.0.0.1/slim_demo/index.php/utils/date
        $app->get('/date',function (Request $req,Response $res){
            return $res->getBody()->write(date('Y-m-d H:i:s'));
        });
//        http://127.0.0.1/slim_demo/index.php/utils/date
        $app->get('/time',function (Request $req,Response $res){
            return $res->getBody()->write(time());
        });
    })->add(function (Request $req,Response $res,$next){
        $res->getBody()->write("now it is:");
        $res = $next($req,$res);
        $res->getBody()->write("enjoy");
        return $res;
    });
    $app->get("/test1",function (Request $req,Response $res) {
        $res->getBody()->write("before test1");
        $res->getBody()->write($req->getUri()->getUserInfo()) ;

        $res->getBody()->write("after test1");
        return $res;
    });
    $app->get("/test_json",function (Request $req,Response $res){
        $req->getParsedBody();

        $data = [
            'status'=>'1',
            'msg'=>'',
            'data'=>[
                'name'=>'name1',
                'age'=>'age1',
            ],
        ];
        $res = $res->withJson($data);
        return $res;
    });
    $app->get('/test_attr',function (Request $request,Response $response){
        $data = [
            'name'=>'jack',
            'age'=>'16',
        ];
        $response = $response->withjson($data,200);
        return $response;
    });

    $app->map(['GET','POST'],'/students',function (Request $request,Response $response){
        $this->get('cookies')->set('name',[
            'value' => 'john',
            'expires' => '7 days'
        ]);

    });
    $app->get('/school',\SchoolAction::class);
    $app->run();
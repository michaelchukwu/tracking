<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    require '../vendor/autoload.php';
    //get the settings
    require '../settings.php';
    // require 'func.php';
    // require '../dep.php';

    $app = new \Slim\App(['settings'=>$config]);
    $container = $app->getContainer();
    $container['view'] = new \Slim\Views\PhpRenderer('../templates/');
    $container['logger'] = function($c) {
        $logger = new \Monolog\Logger('my_logger');
        $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
        $logger->pushHandler($file_handler);
        return $logger;
    };
    $container['db'] = function ($c) {
        $db = $c['settings']['db'];
        $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
            $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    };

    $app->get('/', function (Request $request, Response $response){
        
        $response = $this->view->render($response, 'trackform.phtml');
        // $response->getBody()->write("Hello, $tracking_id, $se");
        return $response;
    });    
    $app->post('/tracking', function (Request $request, Response $response){
        $this->logger->addInfo("Tracking Requested");
        $data = $request->getParsedBody();
        $tracking_data = [];
        $tracking_data['tracking_id'] = filter_var($data['subject'], FILTER_SANITIZE_STRING);
        $tracking_id = $tracking_data['tracking_id'];
        $sql = 'SELECT * FROM shipments WHERE tracking_id = :tracking_id';
        $s = $this->db->prepare($sql);
        $s->bindParam(':tracking_id', $tracking_id);
        if ($s->execute()){
            // exit;
            
            return $response->withJson($s->fetch());
        }
        // $response = $this->view->render($response, 'trackform.phtml', ['shipment' => $shipment]);
        // $response->getBody()->write("Hello, $tracking_id, $se");
        return $response;
    });










    $app->post('/ticket/new', function (Request $request, Response $response) {
        $data = $request->getParsedBody();
        $ticket_data = [];
        $ticket_data['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
        $ticket_data['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);
    // ...
        return $response;
    });
    $app->run();
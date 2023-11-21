<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add routing middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// SQLite database connection
try {
    $database = new \PDO('sqlite:' . __DIR__ . '/../chat.db');
    // Enable PDO exceptions for error handling
    $database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    // Handle database connection error
    echo "Database Connection Error: " . $e->getMessage();
    exit();
}

// Define app routes
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->get('/groups', function (Request $request, Response $response) use ($database) {
    try {
        $groups = $database->query("SELECT * FROM groups")->fetchAll(\PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($groups));
        return $response;
    } catch (\PDOException $e) {
        // Handle database query error
        echo "Database Query Error: " . $e->getMessage();
        exit();
    }
});

// Run app
$app->run();

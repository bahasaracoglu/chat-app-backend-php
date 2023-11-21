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


// PDO bağlantısı
try {
    $database = new \PDO('sqlite:' . __DIR__ . '/../chat.db');
    $database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    // Hata durumunda hata mesajını ve kodunu daha ayrıntılı bir şekilde göster
    die("Database Connection Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
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
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\PDOException $e) {
        // Handle database query error
        echo "Database Query Error: " . $e->getMessage();
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/groups/messages/{id}', function (Request $request, Response $response, $args) use ($database) {
    try {
        $group_id = $args['id'];
        $messages = $database->query("SELECT * FROM messages WHERE group_id = $group_id")
        ->fetchAll(\PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\PDOException $e) {
        // Handle database query error
        echo "Database Query Error: " . $e->getMessage();
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->post('/groups/messages/{id}', function (Request $request, Response $response, $args) use ($database) {

    try {
        $data = $request->getParsedBody();
        $group_id = (int)$args['id'];
        $userId = (int)$data['user_id'];
        $messageText = $data['message_text'];
        $inserted_id = $database->prepare("INSERT INTO messages (group_id, user_id, message_text) VALUES ($group_id, $userId,$messageText)")
        ->fetchAll(\PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($inserted_id));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\PDOException $e) {
        // Handle database query error
        echo "Database Query Error: " . $e->getMessage();
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // Veritabanına mesajı ekle
 

    $response->getBody()->write(json_encode(['message' => 'Message sent successfully']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});




// Run app
$app->run();

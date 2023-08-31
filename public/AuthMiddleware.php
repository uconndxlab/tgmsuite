<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

// Open a connection to the SQLite database

class AuthMiddleware
{

    protected $db;

    public function __construct()
    {
        $this->db = new SQLite3('turfgrass.db');

    }
    
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Perform your authentication logic here.
        // For example, check if the user is authenticated based on your session or token mechanism.
        $isAuthenticated = $this->isAuthenticated(); // Replace this with your actual authentication logic.

        if (!$isAuthenticated) {
            // If the user is not authenticated, return a 401 Unauthorized response.
            $response = new \Slim\Psr7\Response();
            return $response->withStatus(302)->withHeader('Location', '/home');
        }

        // Continue to the next middleware or route handler.
        return $handler->handle($request);
    }

    public function isAuthenticated(): bool
    {
        // Implement your custom authentication logic here.
        // For example, check if the user is authenticated based on your session or token mechanism.

        // check for session
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        return false; // Replace this with your actual authentication logic.
    }

    public function getCurrentUser() {
        
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT * FROM users WHERE id = '$user_id'";
        $result = $this->db->query($sql);
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $user['id'] = $row['id'];
        $user['name'] = $row['name'];
        $user['email'] = $row['email'];


        return $user;

        

        

    }
}

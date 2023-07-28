<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
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
        return false; // Replace this with your actual authentication logic.
    }
}

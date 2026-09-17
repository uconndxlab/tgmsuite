# Athletic Field Assessment Tool

This product provides municipal groundskeepers with a bunch of tools they can use to assess, track, and report on the quality of their various turf fields.

## Requirements

- PHP 8 or later
- Composer

## Dependencies
This project relies on the following dependencies managed via Composer:

- Slim - A microframework for PHP
- Slim Twig View - Twig template renderer for Slim Framework
- PSR-7 - HTTP message interfaces

## Installation

1. Clone this repository to your local machine:

`git clone git@github.com:uconndxlab/tgmsuite.git`

2. Navigate to the project directory:

`cd tgmsuite`

3. Install Dependencies Using Composer:

`composer install`

4. Seed the Database:
   `cd public`

   `php seed.php`

5. Serve the app with PHP's built-in server
   `cd public`

   `php -S localhost:9090`

6. Success? Now the app should be running at http://localhost:9090. You'll need to create a username and password to start.


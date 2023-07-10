# PHP BLOG API

## GETTING STARTED

### `docker exec -it nfe-114-php bash`

Interact with PHP container

### `Composer instal`

Init vendor

### `docker-compose up -d --build`

Build and run the containers in detached mode from the docker-compose.yml file

### `docker-compose down`

Stop the running containers

### API PORT

API running on PORT 8080

### PHP MyAdmin PORT

PHP MyAdmin running on PORT 8895

***
<br/>

## DATABASE SCRIPT

```sql
CREATE TABLE users (
    id CHAR(36) NOT NULL PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    firstname VARCHAR(255) NOT NULL,
    role ENUM('USER','ADMIN') NOT NULL DEFAULT 'USER',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;

CREATE TABLE posts (
    id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    id CHAR(36) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;

CREATE TABLE post_categories (
    post_id CHAR(36) NOT NULL,
    category_id CHAR(36) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    PRIMARY KEY(post_id, category_id)
) ENGINE=InnoDB;
```

***
<br/>

## FILES

### `html/index.php`

This is the **entry point** of the application. <br/> 
index.php performs three main tasks: loading environment variables, defining route handlers, and passing incoming HTTP requests to the correct route handler based on the request's URL. <br/>

```php
require_once __DIR__ . '/vendor/autoload.php';
```
Loads PHP classes from the vendor/ directory. It's necessary for using any libraries that are managed by Composer, including the Dotenv library used in the next lines.

```php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
```
These lines load environment variables from the .env file.

```php
$routeHandlers = [
    new \App\Routes\UserRoutes(),
    new \App\Routes\PostRoutes(),
    new \App\Routes\CategoryRoutes(),
    new \App\Routes\LoginRoutes()
];
```
Each class in this array handles a different set of routes.

```php
$matchFound = false;

foreach ($routeHandlers as $routeHandler) {
    if ($routeHandler->handleRequest()) {
        $matchFound = true;
        break;
    }
}
```

Tries to handle the incoming HTTP. <br/>
handleRequest() method is defined in the [Router class](#htmlsrcroutesrouterphp), which the route classes in the $routeHandlers array extend from.

```php
if (!$matchFound) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['message' => 'No route found for this URL']);
    exit();
}
```

If none of the route handlers were able to handle the request, the script returns a 404 Not Found status code with a "No route found for this URL" message. 

<br/>

### `html/src/Config/database.php`

This file is responsible for establishing a connection to your MySQL database. It uses the Singleton design pattern to ensure that only one database connection is instantiated throughout the application. 

```php
use PDO;
use PDOException;
```
The script imports the **PDO** and **PDOException** classes from the global namespace. **PDO** is a database connection class included with PHP, and **PDOException** is an exception class used for errors related to PDO.

```php
try {
    self::$conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$dbname, self::$username, self::$password);
    self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
    die();  // stop the execution
}
```

This code tries to connect to the MySQL server using the **PDO** class. If the connection fails for any reason, a **PDOException** will be thrown, and the catch block will execute. The catch block outputs the error message and then stops execution of the PHP script.

```php
return self::$conn;
```

Finally, if the connection is successful, the **getConnection** method returns the **PDO** instance, which can be used to interact with the database.

<br/>

### `html/src/Routes/Router.php`

The abstract base Router class provides a fundamental routing structure which is designed to handle the application routing, directing HTTP requests to the appropriate action based on the requested URL and HTTP method.

```php
protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];
```

This protected property is an associative array where keys are HTTP methods ('GET', 'POST', 'PUT', 'DELETE'), and the values are arrays to contain the routes for each method.

```php
protected function sendResponse($data, $statusCode) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
```

This method sends a JSON response to the client. It sets the 'Content-Type' header to 'application/json', assigns the HTTP status code to the provided status code, and outputs the data in JSON format. It then terminates the script using exit().

```php
public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, "/");

        // Checks if there are any routes defined for the HTTP method of the request
        if (!isset($this->routes[$method])) {
            $this->sendResponse(['message' => 'Method Not Allowed'], 405);
            return false;
        }

        // For each route, it checks if the route matches the requested path
        foreach ($this->routes[$method] as $route => $action) {
            if (preg_match('~^' . $route . '$~i', $path, $params)) {
                array_shift($params);
                echo $action(...$params);
                return true;
            }
        }

        return false;
    }
```

This method handles incoming requests. It extracts the HTTP method and the requested path from the global $_SERVER variable. The method checks whether there are any routes defined for the current HTTP method in the $routes array. If not, it sends a 'Method Not Allowed' response with a 405 status code.

If there are defined routes, the method iterates over each route for the current HTTP method. Using a regular expression, it checks if the route matches the requested path. If a match is found, it calls the associated action, passing any parameters extracted from the URL. It then returns true.

If no matching route is found after checking all routes, it returns false.

<br/>

### `html/src/Routes/UserRoutes.php`






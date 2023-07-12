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
<br/>

This file is responsible for establishing a connection to your MySQL database. It uses the Singleton design pattern to ensure that only one database connection is instantiated throughout the application. 

<br/>

```php
use PDO;
use PDOException;
```
The script imports the **PDO** and **PDOException** classes from the global namespace. **PDO** is a database connection class included with PHP, and **PDOException** is an exception class used for errors related to PDO.

<br/>

```php
// Private constructor to prevent direct object creation in order to follow the singleton pattern
private function __construct() {}
```
The constructor is private, which means that objects of this class can't be created using the new keyword. This is part of the ***Singleton design pattern***, which is used to ensure that only one instance of the database connection is created.

<br/>

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

<br/>

```php
return self::$conn;
```

Finally, if the connection is successful, the **getConnection** method returns the **PDO** instance, which can be used to interact with the database.

<br/>

### `html/src/Routes/Router.php`

<br/>

The abstract base Router class provides a fundamental routing structure which is designed to handle the application routing, directing HTTP requests to the appropriate action based on the requested URL and HTTP method.

<br/>

```php
protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];
```

This protected property is an associative array where keys are HTTP methods ('GET', 'POST', 'PUT', 'DELETE'), and the values are arrays to contain the routes for each method.

<br/>

```php
protected function sendResponse($data, $statusCode) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }
```

This method sends a JSON response to the client. It sets the 'Content-Type' header to 'application/json', assigns the HTTP status code to the provided status code, and outputs the data in JSON format. It then terminates the script using exit().

<br/>

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
<br/>

***UserRoutes.php*** file extends the ***Router*** abstract class. This is where the routes specific to user operations are defined.

PostRoutes and CategoryRoutes follow the same logic.

<br/>

```php
class UserRoutes extends Router {
    function __construct() {
       $userController = new UserController();
       
       $this->routes = [
           'GET' => [
               'users' => [$userController, 'index'],
               'users/([a-f0-9\-]+)' => [$userController, 'show']
           ],
           'POST' => [
               'users' => [$userController, 'store']
           ],
           'PUT' => [
                'users/([a-f0-9\-]+)' => [$userController, 'update']
           ],
           'DELETE' => [
                'users/([a-f0-9\-]+)' => [$userController, 'destroy']
           ]
       ];
    }
}
```

In the ***__construct()*** method, an instance of ***UserController*** is created, which will handle the business logic of the user-related actions.

The routes for different HTTP methods are then set for this class.

<br/>

### `html/src/Models/User.php`
<br/>

The User.php file defines a User model class which includes methods for CRUD operations on users in the related database.

The User class uses the static getConnection method of the Database class to connect to the database for each operation. The use of the prepare and execute methods of the PDO object helps prevent SQL injection attacks by using prepared statements and parameter binding.

Both the Post and Category models follow the same logic although the [Post model](#htmlsrcmodelspostphp) is a bit more complex and make use of SQL transactions.

```php
private static function validate($data) {
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['name']) || !isset($data['firstname'])) {
        throw new \InvalidArgumentException("Missing required fields: email, password, name, firstname");
    }
    return;
}
```

Checks if the necessary data fields ('email', 'password', 'name', 'firstname') are present in the provided data. If not, it throws an ***InvalidArgumentException***
<br/>
<br/>

```php
public static function find($id) {
    $db = Database::getConnection();
    // SELECT * except password
    $query = 'SELECT id, email, `name`, firstname, `role`, created_at, updated_at FROM ' . self::$table . ' WHERE id = ? LIMIT 0,1';
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    // PDO::FETCH_ASSOC returns the data in an associative array.
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

Retrieves a user from the database thanks to his ID. It does not return the user's password for security reason though.
<br/>
<br/>

```php
public static function getAll() {
    $db = Database::getConnection();
    // SELECT * except password
    $query = 'SELECT id, email, `name`, firstname, `role`, created_at, updated_at FROM ' . self::$table;
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

Retrieves all users from the database, excluding their passwords.
<br/>
<br/>

```php
public static function create($data) {
        self::validate($data);
        $db = Database::getConnection();
        $uuid = Uuid::uuid4();
        $query = 'INSERT INTO ' . self::$table . ' (id, email, password, name, firstname, role) VALUES (:id, :email, :password, :name, :firstname, :role)';
        if (!isset($data['role'])) {
            $data['role'] = 'USER';
        } 
        $data['id'] = $uuid->toString();   
        $stmt = $db->prepare($query);
        $result = $stmt->execute($data);
        return $result;
    }
```

Creates a new user in the database. It first validates the provided data using the validate function. It generates a unique UUID for the new user and adds it to the data. If no role is provided, it defaults to 'USER'. The data is then inserted into the database via a prepared SQL statement.
<br/>
<br/>

```php
public static function update($id, $data) {
    // initialize our database connection
    $db = Database::getConnection();
    $query = 'UPDATE ' . self::$table . ' SET updated_at = NOW(), ';
    $updateData = [];

    foreach($data as $key => $value){
        $query .= $key . ' = :' . $key . ', ';
        // named parameters help prevent SQL injection attacks
        $updateData[':'.$key] = $value;
    }
    // rtrim function removes the trailing comma from our SQL query 
    $query = rtrim($query, ', ');
    $query .= ' WHERE id = :id';
    $updateData[':id'] = $id;

    $stmt = $db->prepare($query);
    $stmt->execute($updateData);
}
```

Updates a user in the database, based on their ID and the provided data.

<br/>
<br/>

```php
 public static function delete($id) {
    $db = Database::getConnection();
    $query = 'DELETE FROM ' . self::$table . ' WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $id]);
}
```

Deletes a user from the database based on his ID.

<br/>
<br/>

```php
public static function findByEmail($email) {
    $db = Database::getConnection();
    $query = 'SELECT * FROM ' . self::$table . " WHERE email = ? LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
} 
```

Retrieves a user from the database by their email. The SQL query is prepared, executed with the provided email, and the results are fetched as an associative array.

<br/>
<br/>

### `html/src/Models/Post.php`
<br/>

```php
public static function create($postData, $categoryData) {
    self::validate($postData, $categoryData);
    $db = Database::getConnection();
    
    // Generate a UUID
    $uuid = Uuid::uuid4();
    $postId = $uuid->toString();

    try {
        $db->beginTransaction();

        $query = 'INSERT INTO ' . self::$table . ' (id, user_id, title, content) VALUES (:id, :user_id, :title, :content)';
        $stmt = $db->prepare($query);
        // Include the UUID in the data to insert
        $postData['id'] = $postId;
        $stmt->execute($postData);

        // Insert post.id and category.id in the junction table
        $query = 'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)';
        $stmt = $db->prepare($query);
        $result = $stmt->execute([
            'post_id' => $postId,
            'category_id' => $categoryData['category_id']
        ]);
        // Commit the changes previously made in the database
        $db->commit();

        return $result;
    } catch(\PDOException $e) {
        // if anything goes wrong, we catch the exception, roll back the transaction, and then throw the exception.
        $db->rollback();
        throw $e;
    }
}
```

Inserts a new post into the database and associates it with a category. It does so inside a ***transaction*** to ensure that either both the post creation and its association with the category happen successfully, or none of them happen at all (to maintain data integrity).
<br/>
<br/>

```php
public static function update($id, $postData, $categoryId) {
    $db = Database::getConnection();
    
    try {
        // Start the transaction
        $db->beginTransaction();

        // Update the post in the posts table
        $updateQuery = 'UPDATE ' . self::$table . ' SET updated_at = NOW(), ';
        $updateData = [];

        foreach($postData as $key => $value){
            $updateQuery .= $key . ' = :' . $key . ', ';
            // Named parameters help prevent SQL injection attacks
            $updateData[':'.$key] = $value;
        }

        // rtrim function removes the trailing comma from the SQL query 
        $updateQuery = rtrim($updateQuery, ', ');
        $updateQuery .= ' WHERE id = :id';
        $updateData[':id'] = $id;

        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute($updateData);
        
        if ($categoryId) {
            // Update the category in the post_categories table
            $deleteQuery = 'DELETE FROM post_categories WHERE post_id = :post_id';
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->execute(['post_id' => $id]);
    
            $insertQuery = 'INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)';
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->execute(['post_id' => $id, 'category_id' => $categoryId]);
        }    

        // Commit the transaction
        $db->commit();
    } catch (\Exception $e) {
        // An error occurred; rollback the transaction
        $db->rollback();

        // Rethrow the exception
        throw $e;
    }
}
```

Updates a specific post's data and its associated category in the database. Like the create method, it uses a transaction to ensure either both operations are successful, or if an error occurs, neither operation affects the database.

<br/>

### `html/src/Auth/Authentication.php`
<br/>

This file defines an Authentication class which is responsible for handling user authentication. It uses the Firebase ***JWT*** (JSON Web Token) library for generating a JWT upon successful authentication.

<br/>

```php
class Authentication {
    private $jwtKey;

    public function __construct() {
        $this->jwtKey = $_ENV['JWT_SECRET'];
    }

    public function authenticate($email, $password) {
        $user = User::findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                "iat" => time(), // Issued at
                "exp" => time() + (60 * 60), // Token valid for 1 hour
                "data" => [
                    "userId" => $user['id'],
                    "role" => $user['role'],
                ]
            ];

            $jwt = JWT::encode($payload, $this->jwtKey, 'HS256'); // HS256: algorithm to sign the token
            return $jwt;
        }

        return false;
    }
}
```

The private property $jwtKey will store the ***JWT secret key*** used for signing tokens.

The constructor method sets $jwtKey to be the JWT_SECRET environment variable. 

The authenticate method takes an email and password as arguments, and attempts to authenticate the user. It first retrieves the user associated with the provided email using the findByEmail method of the User model.

If a user is found, it verifies the provided password with the hashed password stored in the user record using PHP's built-in password_verify function.

If the password verification is successful, it prepares a ***payload*** to be included in the ***JWT***. 

The ***payload*** includes:
* ***iat***: The time the token was issued at.
* ***exp***: The time the token will expire (set to 1 hour after issue).
* ***data***: An associative array that contains the user's ID and role.

The payload is then encoded into a ***JWT*** using the encode method from the ***Firebase JWT library***. The token is signed with the ***HS256*** algorithm using the secret key stored in $jwtKey.

If the JWT is successfully created, it is returned. If the user is not found or the password doesn't match, the method returns false, indicating failed authentication.

<br/>

### `html/src/Auth/Authorization.php`
<br/>

This file defines an Authorization class aimed to handle user authorization by verifying a provided ***JWT*** (JSON Web Token). It uses ***Firebase's JWT library*** to decode and verify the ***JWT***.

<br/>

```php
class Authorization {
    private $jwtKey;

    public function __construct() {
        $this->jwtKey = $_ENV['JWT_SECRET'];
    }

    public function authorize($jwt) { 
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtKey, 'HS256'));
            $user = User::find($decoded->data->userId);
            if (!$user) return false;
            return $decoded;
        } catch(ExpiredException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
```
A private property $jwtKey is declared in order to store the ***JWT secret key*** used to sign and verify tokens.

The constructor method is automatically called when a new instance of the class is created. It initializes $jwtKey with the value of the JWT_SECRET environment variable, which hold the secret key used for signing the JWTs.

The authorize method of this class takes a ***JWT*** as an argument and attempts to verify it.

First, it tries to decode the provided JWT using the JWT::decode method. The decode method is passed the JWT, the secret key, and the algorithm used for signing the JWT ('HS256'). If the JWT is successfully decoded, it means that it was signed with the same secret key and it's not tampered with or corrupted.

After successful decoding, it tries to find a user with the ID contained in the decoded ***JWT data*** ($decoded->data->userId). If the user is found, it returns the ***decoded JWT***. Otherwise, if the user is not found, it returns false.

If the ***JWT*** has expired, the ***ExpiredException*** will be thrown. This is caught and the method returns false.

Any other exceptions that are thrown (like invalid JWT format, signature verification failure) are caught by the final catch block and the method returns false.

<br/>

### `html/src/Controllers/BaseController.php`
<br/>

This file defines a BaseController which is declared as abstract, indicating that it can't be instantiated on its own, and instead is intended to be subclassed by other, more specific controllers.

<br/>

```php
abstract class BaseController {

    // Methods to be implemented by child classes
    abstract public function index();
    abstract public function show($id);
    abstract public function store();
    abstract public function update($id);
    abstract public function destroy($id);
```

Those 5 abstract methodsdon't contain any implementation in the abstract class itself. Instead, they define a contract that any concrete subclass must fulfill by providing an implementation for each of them.

<br/>

```php
// Class constants
protected const USER = 'USER';
protected const ADMIN = 'ADMIN';
```

Two protected class constants ,***USER*** and ***ADMIN*** , are declared.

<br/>

```php
// Get JSON input
protected function getJsonInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true);
}
```

Retrieves and decodes JSON input from the request.

<br/>

```php
// Return a JSON error response
protected function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
}
```

Sends a JSON response to the client with a specified HTTP status code (200 as default). It sets the response content type to 'application/json', sets the HTTP status code, and outputs the encoded data.

<br/>

```php
// Return a JSON error response
protected function jsonError($message, $statusCode = 500) {
    $this->jsonResponse(['error' => $message, $statusCode]);
}
```

Sends an error response to the client. It uses the jsonResponse method to return a JSON object containing an error message and a specified HTTP status code (500 as default).

<br/>

```php
// Check Authorization
protected function checkAuthorization($userRole = self::USER) {
    // Get JWT from the Authorization header
    $authHeader = getallheaders()['Authorization'] ?? '';
    
    // Extract JWT from Bearer token format
    $jwt = str_replace('Bearer ', '', $authHeader);

    $auth = new Authorization();

    // Validate the JWT and get the user payload data
    $userJwtData = $auth->authorize($jwt);
    
    if (!$userJwtData) {
        $this->jsonError('Not authorized', 403);
        exit();
    };

    return $userJwtData->data;
}
```

<a id="authorization"></a>
Checks if a request is authorized by validating the ***JWT*** token from the Authorization header. It gets the Authorization header, extracts the JWT token, creates a new [Authorization](#htmlsrcauthauthorizationphp) object, and uses it to verify the JWT. If the JWT is not valid or there's no user with the data contained in the JWT, the function sends a 'Not authorized' error and stops the script execution. Otherwise, it returns the data from the JWT payload.

<br/>

```php
// Check if User's role is ADMIN
protected function checkIsAdmin($userJwtData) {
    if ($userJwtData->role !== self::ADMIN) {
        $this->jsonError('Not authorized', 403);
        exit();
    }
}
```

<a id="adminauthorization"></a>
Checks if the user's role is ***ADMIN***. It accepts JWT data as an argument, checks the 'role' property, and if the role is not ADMIN, it sends a 'Not authorized' error and stops script execution.

<br/>

### `html/src/Controllers/UserController.php`
<br/>

This file declares the UserController class, which extends BaseController, meaning it inherits all the properties and methods of the [BaseController](#htmlsrccontrollersbasecontrollerphp). The PostController provides implementations for the abstract methods defined in BaseController and manages CRUD operations for user data.

Post and category Controllers follow the same logic as the following one.

<br/>

```php
// Get all users
public function index() {
    try {
        // Only ADMIN is able to access all the users info
        $userJwtData = $this->checkAuthorization();
        $this->checkIsAdmin($userJwtData);
        $users = User::getAll();
        $this->jsonResponse($users);
    } catch(\Exception $e) {
        $this->jsonError('Database error: ' . $e->getMessage());
    } 
}
```

Retrieves all users, but only if the authorized user is an admin (ADMIN). It uses the inherited [checkAuthorization](#authorization) and [checkIsAdmin](#adminauthorization) methods to ensure that the requester has the proper permissions. The users are retrieved using User::getAll(), and are then returned in JSON format using jsonResponse. If any exceptions are thrown during the process, a 'Database error' message is returned.

<br/>

```php
// Get a user by id
public function show($id) {
    try {
        // Only Admin or User who want to access his information who can get user/:id
        $userJwtData = $this->checkAuthorization();
        if ($userJwtData->role === self::ADMIN || $userJwtData->userId === $id) {
            $user = User::find($id);
            if ($user) {
                $this->jsonResponse($user);
            } else {
                $this->jsonError('User not found', 404);
            }
        } else {
            $this->jsonError('Not authorized', 403);
        }  
    } catch(\Exception $e) {
        $this->jsonError('Database error: '. $e->getMessage());
    }
}
```

Retrieves a user by his ID. It first checks the authorization of the requester, allowing access if the requester is an admin or if the requested user is the same as the authenticated user. If the requested user is found, the user data is returned in JSON format. If not, a 'User not found' error is returned. If the requester is not authorized to view the requested user, a 'Not authorized' error is returned.

<br/>

```php
// Create a new user
public function store() {
    try {
        // Get JSON input
        $data = $this->getJsonInput();
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT); 
        $result = User::create($data);
        $this->jsonResponse(['message' => 'User created', 'status' => $result], 201);
    } catch(\Exception $e) {
        $this->jsonError('Database error: ' . $e->getMessage());
    }
}
```

This method creates a new user. It retrieves the JSON input using the getJsonInput method, ***hashes the password*** using the password_hash function, and creates a new user using the User::create method. If successful, a success message and the new user's data are returned. If any exceptions are thrown during the process, a 'Database error' message is returned.

<br/>

```php
// Update a user
public function update($id) {
    try {
        // Only ADMIN or the corresponding user himself are able to update the user
        $userJwtData = $this->checkAuthorization();
        if ($userJwtData->role === self::ADMIN || $userJwtData->userId === $id) {
            // Get JSON input
            $data = $this->getJsonInput();
            User::update($id, $data);
            $user = User::find($id);
            if ($user) {
                $this->jsonResponse($user);
            } else {
                $this->jsonError('User not found', 404);
            }
        } else {
            $this->jsonError('Not authorized', 403);
        } 
    } catch(\Exception $e) {
        $this->jsonError('Database error: ' . $e->getMessage());
    }
}
```

This method updates a user's data. It first checks the [authorization](#htmlsrcauthauthorizationphp) of the requester, only allowing admins or the ecorresponding user himself to perform the operation. It then retrieves the JSON input, updates the user using User::update, and returns the updated user data. If any exceptions are thrown during the process, a 'Database error' message is returned.

<br/>

```php
// Delete a user
public function destroy($id) {
    try {
        // Only ADMIN is able to delete a user
        $userJwtData = $this->checkAuthorization();
        $this->checkIsAdmin($userJwtData);
        $user = User::delete($id);
        $this->jsonResponse($user);
    } catch(\Exception $e) {
        $this->jsonError('Database error: ' . $e->getMessage());
    }
}
```

This method deletes a user. It first checks the [authorization](#htmlsrcauthauthorizationphp) of the requester, only allowing admins to perform the operation. It then deletes the user using User::delete, and returns the deleted user's data. If any exceptions are thrown during the process, a 'Database error' message is returned.

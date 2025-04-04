<?php // This file includes generating functiosn (generating unique passwords, tokens, etc.)

// These functions uses "The Random\Randomizer class with heavy inspiration from this GitHub repo:
// https://github.com/valorin/random/blob/main/src/Generator.php#L195

// This function uses the "The Random\Randomizer class" to generate a unique password
function rdp_generate_password($length = 20, $returnHashed = false)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();

    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];
    $special = [
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        '[',
        '\\',
        ']',
        '^',
        '_',
        '`',
        '{',
        '|',
        '}',
        '~',
    ];
    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers, $special);
    $total = count($all) - 1;

    // Prepare empty password string
    $password = '';

    // Add random characters to the password until it reaches the desired length
    while (strlen($password) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total); // Get a random index using the randomizer
        $password .= $all[$randomCharIndex];
    }

    // Split the password, shuffle it and join it back together using shuffleArray from randomizer class!
    $password = $randomizer->shuffleArray(str_split($password));
    $password = implode('', $password);

    // Return a hashed password if needed
    if ($returnHashed) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // Otherwise, return the generated password
    return $password;
}

// This function uses the "The Random\Randomizer class" to generate a unique number
function rdp_generate_number($length = 10)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();

    // Prepare numbers that can be used
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    // Prepare empty number string and total count of numbers array minus 1
    // and add random numbers to the number until it reaches the desired length
    $total = count($numbers) - 1;
    $number = '';

    // First number cannot be 0
    $randomCharIndex = $randomizer->getInt(1, $total);
    $number .= $numbers[$randomCharIndex];

    while (strlen($number) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total);
        $number .= $numbers[$randomCharIndex];
    }

    // Return the generated number as an integer
    return (int)$number;
}

// This function uses the "The Random\Randomizer class" to generate a unique user_id
function rdp_generate_user_id($length = 96)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();

    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers);
    $total = count($all) - 1;

    // Prepare empty user_id string and add random characters to the user_id until it reaches the desired length
    $user_id = '';
    while (strlen($user_id) < $length) {
        // Insert a "-" after every 24 characters except for the last one
        if (strlen($user_id) % 24 == 0 && strlen($user_id) != 0) {
            $user_id .= '-';
            continue;
        }
        $randomCharIndex = $randomizer->getInt(0, $total);
        $user_id .= $all[$randomCharIndex];
    }

    // Return the generated user_id
    return $user_id;
}

// This function uses the "The Random\Randomizer class" to generate a unique CSRF
function rdp_generate_csrf($length = 384)
{
    // Create a new Randomizer object
    $randomizer = new Random\Randomizer();

    // Prepare characters that can be used
    $lowers =  [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $uppers =  [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
    ];
    $numbers =  [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    // Merge the arrays into one:
    $all = array_merge($lowers, $uppers, $numbers);
    $total = count($all) - 1;

    // Prepare empty CSRF string and add random characters to the CSRF until it reaches the desired length
    $csrf = '';
    while (strlen($csrf) < $length) {
        $randomCharIndex = $randomizer->getInt(0, $total);
        $csrf .= $all[$randomCharIndex];
    }

    // Return the generated CSRF
    return $csrf;
}






// Dynamic CSS pathing
function rdp_paths_css($cssPath)
{
    global $rdp_routes_base_url;
    return $rdp_routes_base_url . "/css/$cssPath";
    exit;
}

// Dynamic JavaScript pathing
function rdp_paths_js($jsPath)
{
    global $rdp_routes_base_url;
    return $rdp_routes_base_url . "/js/$jsPath";
    exit;
}

// Dynamic href (<a> tags) pathing
function rdp_paths_href($linkPath)
{
    global $rdp_routes_base_url;
    return $rdp_routes_base_url . "/$linkPath";
    exit;
}

// Dynamic image pathing ("images")
function rdp_paths_images($imagesPath)
{
    global $rdp_routes_base_url;
    return $rdp_routes_base_url . "/images/$imagesPath";
    exit;
}

// Dynamic video pathing ("videos")
function rdp_paths_videos($videosPath)
{
    global $rdp_routes_base_url;
    return $rdp_routes_base_url . "/videos/$videosPath";
    exit;
}

// Dynamic custom pathing for folder and file. You can choose one
// from the "/config/rdp_static_file_paths.php") or write your own.
// What you choose is the $customFolderPath. Then write the filename!
function rdp_paths_custom($customFolderPath, $customFilePath)
{
    global $rdp_routes_base_url;
    return $rdp_routes_base_url . "/$customFolderPath/$customFilePath";
    exit;
}

// Function to check if a route exists in the $rdp_routes array from "/routes/rdp_all_routes.php"
// It returns the route details and extracted parameters if a route was found
function rdp_routes_return_existing_route($rdp_routes)
{
    // When REQUEST_URI or REQUEST_METHOD doesn't even exist, return 'code' => "500"
    if (!isset($_SERVER['REQUEST_URI']) || !isset($_SERVER['REQUEST_METHOD'])) {
        return ['code' => "500"];
    }

    // Get the current request URI and method without the query string
    $rdp_request_uri = str_contains($_SERVER['REQUEST_URI'], '?') ? strtok($_SERVER['REQUEST_URI'], '?') : $_SERVER['REQUEST_URI'];

    // Check if "$rdp_request_uri" starts with "/rdp/src/public_html/" (default localhost base URI) and if so, change it to "/" + the rest of the URI
    global $rdp_routes_base_url_uri;
    $base_path = $rdp_routes_base_url_uri;
    if (str_starts_with($rdp_request_uri, $base_path)) {
        $rdp_request_uri = '/' . ltrim(substr($rdp_request_uri, strlen($base_path)), '/');
    }

    // Delete any "./" and "../" to prevent directory traversal attacks
    $rdp_request_uri = str_replace(['./', '../'], '', $rdp_request_uri);

    // Sanitize the request URI to prevent XSS attacks
    $rdp_request_uri = htmlspecialchars($rdp_request_uri, ENT_QUOTES, 'UTF-8');

    // Delete any single trailing "/" if it is not the base in the request URI to be flexible with the routes
    if (substr($rdp_request_uri, -1) == '/' && $rdp_request_uri !== '/') {
        $rdp_request_uri = substr($rdp_request_uri, 0, -1);
    }

    // Get the current request method
    $rdp_request_method = $_SERVER['REQUEST_METHOD'];

    // Loop through all routes and check if the current request URI and method match
    // while taking into account dynamic routes
    foreach ($rdp_routes as $route => $details) {
        // Remove last "/" in the route key to be consistent with the request URI
        if (substr($route, -1) == '/' && $route !== 'GET/') {
            $route = substr($route, 0, -1);
        }

        // Store current route details in an array
        $details['code'] = "500";
        $details['content_types'] = $details[0];
        $details['method'] = $rdp_request_method;
        $details['auth'] = $details[1];
        $details['route'] = $route;
        $details['uri'] = $rdp_request_uri;
        $details['params'] = [];

        // Keep only named keys by remvoing array elements [0],[1] and [2] if they exist!
        unset($details[0], $details[1], $details[2]);

        // Replace placeholders with regex patterns
        // Regex example: "GET\/user\/([a-zA-Z0-9_-]+)" for the route "GET/user/{id}"
        $pattern = preg_replace('/\{[a-zA-Z0-9_-]+\}/', '([a-zA-Z0-9_-]+)', $route);
        $pattern = str_replace('/', '\/', $pattern); // Escape slashes for regex

        // Match the current request URI and method with the route pattern
        if (preg_match('/^' . $pattern . '$/', $rdp_request_method . $rdp_request_uri, $matches)) {

            // Remove the full match to only keep the extracted parameters
            array_shift($matches);

            // Store params in the found route
            $details['params'] = $matches ?? []; // Add the extracted parameters to the details

            // If 'params' is not empty, replace the array index with the actual params from the $route
            if (isset($details['params']) && !empty($details['params'])) {

                // Split paths and grab all those with "{param}" in them in an array
                $params = explode('/', $route);
                $params = array_filter($params, function ($param) {
                    return str_contains($param, '{');
                });

                // Remove the "{" and "}" from the params
                $params = array_map(function ($param) {
                    return str_replace(['{', '}'], '', $param);
                }, $params);

                // Combine keys and values to create associative arrays for params
                $details['params'] = array_combine($params, $details['params']);
            }

            // Validate that correct content-type/Accept is allowed for the route
            if (isset($details['content_types']) && !rdp_routes_valid_content_type($details['content_types'])) {
                $details['code'] = "415";
                return $details; //
            }

            // Validate that the user is authenticated/authorized to access the route
            if (isset($details['auth']) && !rdp_routes_authenticate_authorize($details['auth'])) {
                $details['code'] = "403";
                return $details;
            }

            // Route is found, so return 200 OK
            $details['code'] = "200";
            return $details;
        }
    }
    // Return "404" string if no route was found
    $details['code'] = "404";
    return $details;
}

// Function to check if a valid route's content type is allowed (e.g., "text/html")
// It can be a list of allowed content types separated by "|", so it checks for that too
// by matching at least one content-type/accept in HTTP_ACCEPT or CONTENT_TYPE key.
function rdp_routes_valid_content_type($rdp_content_types_as_a_string)
{
    // Check if HTTP_ACCEPT or CONTENT_TYPE is even set
    if (!isset($_SERVER['HTTP_ACCEPT']) && !isset($_SERVER['CONTENT_TYPE'])) {
        return false;
    }

    // Check for "|" and split if it exists, otherwise treat as a string
    $rdp_content_types = str_contains($rdp_content_types_as_a_string, '|') ? explode('|', strtolower($rdp_content_types_as_a_string)) : strtolower($rdp_content_types_as_a_string);

    // Check if GET is used and if so, use HTTP_ACCEPT instead of CONTENT_TYPE
    $rdp_method_content = $_SERVER['REQUEST_METHOD'] == 'GET' ? strtolower($_SERVER['HTTP_ACCEPT']) : strtolower($_SERVER['CONTENT_TYPE']);

    // Check if "," exists in the content type and split if it does, otherwise treat as a string
    $rdp_method_content = str_contains($rdp_method_content, ",") ? explode(',', $rdp_method_content) : $rdp_method_content;

    // Match any content if both are arrays
    if (is_array($rdp_content_types) && is_array($rdp_method_content)) {
        return count(array_intersect($rdp_content_types, $rdp_method_content)) > 0;
    }

    // Match if the content type is in the array
    elseif (is_array($rdp_method_content)) {
        return in_array($rdp_content_types, $rdp_method_content);
    }

    // Match if both are strings and the same
    return $rdp_content_types === $rdp_method_content;
}

// Function to check if it is valid/authenticated/authorized to access the current route
function rdp_routes_authenticate_authorize($rdp_current_auth)
{
    // Check if the route is accessible to everyone
    if ($rdp_current_auth === 'all') {
        return true;
    }

    // Return false if not authenticated/authorized
    return false;
}

// Function to redirect if redirect key exists and contains the response code
// Redirect to a specific URI based on retrieved response code during route processing
function rdp_routes_redirect_on_code($redirectRouteKey = null, $redirectResponseCode = null)
{
    // Check if the route has a redirect URI
    if (isset($redirectRouteKey)) {

        // Split the redirectRouteKey by "|" to handle multiple redirections
        $redirects = explode('|', $redirectRouteKey);

        // Iterate through each redirect pair
        foreach ($redirects as $redirect) {

            // Split each redirect pair by the "/" character to separate the code and the route
            list($code, $route) = explode('/', $redirect, 2);

            // Check if the code matches the response code
            if ($code == $redirectResponseCode) {
                // Redirect to the URI specified in the route
                rdp_headers_location($route);
            }
        }
    }
}


// Sanitize the REQUEST_URI from the user (for example, when they use "../" to go up a directory)
// Also allows for a custom sanitization callback to be provided
function rdp_sanitize_request_uri($request_uri, $customSanitizationCallback = null)
{
    // If a custom function is provided, check it is not null and is a function and just call it
    if ($customSanitizationCallback !== null && is_callable($customSanitizationCallback)) {
        $request_uri = call_user_func($customSanitizationCallback, $request_uri);
    }

    // Remove all null bytes first
    $request_uri = str_replace(chr(0), '', $request_uri);

    // Then remove "\r\n" when combined
    $request_uri = str_replace("\r\n", '', $request_uri);

    // Finally return the sanitized request URI
    return filter_var($request_uri, FILTER_SANITIZE_URL);
}















// Connect to the SQL (MySQL or MariaDB) Database
function rdp_sql_db_connect()
{
    // Try to connect to the database
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        // Then set the charset to utf8
        $db->set_charset("utf8mb4");
        // Show no SQL errors when it is not locally
        if ($_SERVER['SERVER_NAME'] != "localhost") {
            mysqli_report(MYSQLI_REPORT_OFF);
            // Also no PHP errors
            error_reporting(0);
        }
        // Return the connection object
        return $db;
    }  // Just return null if any errors occur
    catch (mysqli_sql_exception $error) {
        return null;
    }
}


// This SQL Function creates the $type string by taking
// a reference variable (&$type) and returning the type
// of the $value variable which is then added to a variable
// that is NOT inside of this function but referred to!
// It is used by "rdp_sql_execute_statement_query" function!
function rdp_sql_binded_value_type(&$type, $value, $forcedType = "")
{
    // First check if $forcedType was written
    // Forced type is "s","i","d" or "b" for string, integer, double or blob
    if ($forcedType != "") {
        $type .= $forcedType;
        return;
    }
    // Check type of $value and add correct type to referenced $type
    // meaning it is NOT inside of this function but it is being changed
    // by the function itself!

    // Check if the string represents a numeric value
    // This is to check against "-1" and similar negative values
    if (is_numeric($value)) {
        // If so, treat it as a float or an integer depending on its value
        if ((int)$value == $value) {
            $type .= 'i'; // Treat as integer
        } else {
            $type .= 'd'; // Treat as double (float)
        }
    }
    // Is value an integer?
    elseif (is_int($value)) {
        $type .= 'i';
    }
    // Is value a float?
    elseif (is_float($value)) {
        $type .= 'd';
    }
    // Is value a string?
    elseif (is_string($value)) {
        $type .= 's';
    }
    // Then it must be blob!
    else {
        $type .= 'b';
    }
}


// SQL Execute Statement Query Function that takes a SQL Query and an array of parameters and returns the result set
// Function that executes a SQL Query with parameters and returns the result set or null when anything failed.
// It takes a mysqli connection, a query string, an array of parameters, a fetch type (ASSOC, NUM, BOTH) and
// a boolean for returning the last inserted id. Last part only works for INSERT queries.
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
function rdp_sql_execute_statement_query($mysqli, $query, $params = array(), $fetchType = "ASSOC", $return_last_inserted_id = false)
{
    // Prepare the query
    $stmt = $mysqli->prepare($query);

    // Check for errors in query preparation
    if (!$stmt) {
        return null;
    }

    // Santizie the parameters with escape string and strip tags before binding them
    $params = array_map(function ($param) use ($mysqli) {
        return $mysqli->real_escape_string(($param));
    }, $params);

    // Bind parameters dynamically
    if (!empty($params)) {
        // Get the types of parameters by calling the valueType function
        $types = '';

        // Loop through the parameters and get their types either automatically and/or forced
        foreach ($params as $index => $param) {
            // Check if the index of the array element is associative in terms of being a string
            // instead of a number and then send it to the binded_value_type function with the forced type
            if (mb_strtolower($index) == "s" || mb_strtolower($index) == "i" || mb_strtolower($index) == "d" || mb_strtolower($index) == "b") {
                rdp_sql_binded_value_type($types, $param, mb_strtolower($index));
            } // Otherwise call the binded_value_type  function normally
            else {
                // Call the binded_value_type  function to get the type of the parameter
                rdp_sql_binded_value_type($types, $param);
            }
        }

        // Make the array a list if it is not already (string keys were used)
        if (!array_is_list($params)) {
            $params = array_values($params);
        }

        // Bind parameters (where params is a list array)
        $stmt->bind_param($types, ...$params);

        // Check for errors in parameter binding
        if ($stmt->errno) {
            $stmt->close();
            return null;
        }
    }

    // Execute the query
    $stmt->execute();

    // Check for errors in query execution
    if ($stmt->errno) {
        $stmt->close();
        return null;
    }


    // Check if query started with SELECT
    if (str_starts_with(strtoupper($query), "SELECT")) {
        // Get & return the result set or just null
        $result = $stmt->get_result();
        if ($result) {
            // Check fetch type and return the result set according to it
            $rows = null;
            if (mb_strtoupper($fetchType) == "ASSOC") {
                $rows = $result->fetch_all(MYSQLI_ASSOC);
            } elseif (mb_strtoupper($fetchType) == "NUM") {
                $rows = $result->fetch_all(MYSQLI_NUM);
            } elseif (mb_strtoupper($fetchType) == "BOTH") {
                $rows = $result->fetch_all(MYSQLI_BOTH);
            } else {
                $rows = $result->fetch_all();
            }
            $stmt->close();
            return $rows;
        } else {
            $stmt->close();
            return null;
        }
    }
    // Otherwise check if it it is INSERT, UPDATE or DELETE so we check affected rows
    elseif (
        str_starts_with(strtoupper($query), "INSERT")
        || str_starts_with(strtoupper($query), "UPDATE")
        || str_starts_with(strtoupper($query), "DELETE")
    ) {
        // Get & return the affected rows or just null
        $result = $stmt->affected_rows;
        $stmt->close();
        if ($result > 0) {
            // Return the last inserted id if requested and the query was an INSERT
            if ($return_last_inserted_id && str_starts_with(strtoupper($query), "INSERT")) {
                return $mysqli->insert_id;
            } else {
                return true;
            }
        } else {
            return null;
        }
    }  // Otherwise it is a different query type besides CRUD, so fetch any result and return it
    else {
        // Get & return the result set or just null
        $result = $stmt->get_result();
        if ($result) {
            $stmt->close();
            return $result;
        } else {
            $stmt->close();
            return null;
        }
    }
}

// SQL Function to check if a single column value exists in a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to check if a single column value exists first!
function rdp_sql_check_single_column_value_exists($mysqli, $table, $column, $value)
{
    // Prepare the SQL Query
    $query = "SELECT COUNT(1) FROM `$table` WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $value,
    ];

    // Execute the SQL Query
    $result = rdp_sql_execute_statement_query($mysqli, $query, $params, "NUM");

    // Return true if the value exists, false otherwise
    return $result[0][0] > 0;
}


// SQL Function to create a user into the "rdp_users" table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate email/username NOT already taken first!
function rdp_sql_create_single_user($mysqli, $user, $return_last_inserted_id = false)
{
    // Prepare the SQL Query
    $query = "INSERT INTO rdp_users (user_id, user_username, user_fullname, user_email, user_password, user_created_at, user_updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Prepare the parameters
    $params = [
        $user['user_id'],
        $user['user_username'],
        $user['user_fullname'],
        $user['user_email'],
        $user['user_password'],
        $user['user_created_at'],
        $user['user_updated_at'],
    ];

    // Execute the SQL Query with or without returning the last inserted id
    return rdp_sql_execute_statement_query($mysqli, $query, $params, [], $return_last_inserted_id);
}

// SQL Function to delete a single row from chosen table by custom value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to delete row first!
function rdp_sql_delete_single_row_by_single_value($mysqli, $table, $where_column, $where_value)
{
    // Prepare the SQL Query
    $query = "DELETE FROM `$table` WHERE `$where_column` = ?";

    // Prepare the parameters
    $params = [
        $where_value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to delete a single row from chosen table by id
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to delete row first!
function rdp_sql_delete_single_row_by_single_id($mysqli, $table, $id)
{
    // Prepare the SQL Query
    $query = "DELETE FROM `$table` WHERE id = ?";

    // Prepare the parameters
    $params = [
        $id,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to delete a user from the "rdp_users" table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to delete user first!
function rdp_sql_delete_single_user($mysqli, $user_id)
{
    // Prepare the SQL Query
    $query = "DELETE FROM rdp_users WHERE user_id = ?";

    // Prepare the parameters
    $params = [
        $user_id,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to insert a single row into a chosen table WITHOUT returning the last inserted id
// IMPROTANT: "user_id" is NOT the last insert id, but it is used as Foreign Key in all default RDP tables!
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to insert row first!
function rdp_sql_insert_single_row_without_last_insert_id($mysqli, $table, $columnsSeparatedByCommas, $valuesAsAnArray)
{
    // Check if $columns is an array or a string and if string check if "," or ", " is used
    $columns = "";
    if (!is_string($columnsSeparatedByCommas) || !is_array($valuesAsAnArray)) {
        return null;
    } elseif (is_string($columnsSeparatedByCommas)) {
        if (!str_contains($columnsSeparatedByCommas, ",")) {
            $columns = $columnsSeparatedByCommas;
        } elseif (mb_strpos($columnsSeparatedByCommas, ", ") !== false) {
            $columns = str_replace(", ", ",", $columnsSeparatedByCommas);
        } elseif (mb_strpos($columnsSeparatedByCommas, ",") !== false) {
            $columns = str_replace(",", ",", $columnsSeparatedByCommas);
        }
    }

    // Prepare the SQL Query
    $query = "INSERT INTO `$table` ($columns) VALUES (";

    // Add the correct amount of question marks to the query
    for ($i = 0; $i < count($valuesAsAnArray); $i++) {
        $query .= "?";
        if ($i < count($valuesAsAnArray) - 1) {
            $query .= ", ";
        }
    }

    // Close the query
    $query .= ")";

    // Prepare the parameters
    $params = $valuesAsAnArray;

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, [], false);
}

// SQL Function to insert a single row into a chosen table WITH returning the last inserted id
// IMPROTANT: "user_id" is NOT the last insert id, but it is used as Foreign Key in all default RDP tables!
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to insert row first!
function rdp_sql_insert_single_row_with_last_insert_id($mysqli, $table, $columnsSeparatedByCommas, $valuesAsAnArray)
{
    // Check if $columns is an array or a string and if string check if "," or ", " is used
    $columns = "";
    if (!is_string($columnsSeparatedByCommas) || !is_array($valuesAsAnArray)) {
        return null;
    } elseif (is_string($columnsSeparatedByCommas)) {
        if (!str_contains($columnsSeparatedByCommas, ",")) {
            $columns = $columnsSeparatedByCommas;
        } elseif (mb_strpos($columnsSeparatedByCommas, ", ") !== false) {
            $columns = str_replace(", ", ",", $columnsSeparatedByCommas);
        } elseif (mb_strpos($columnsSeparatedByCommas, ",") !== false) {
            $columns = str_replace(",", ",", $columnsSeparatedByCommas);
        }
    }

    // Prepare the SQL Query
    $query = "INSERT INTO `$table` ($columns) VALUES (";

    // Add the correct amount of question marks to the query
    for ($i = 0; $i < count($valuesAsAnArray); $i++) {
        $query .= "?";
        if ($i < count($valuesAsAnArray) - 1) {
            $query .= ", ";
        }
    }

    // Close the query
    $query .= ")";

    // Prepare the parameters
    $params = $valuesAsAnArray;

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, [], true);
}

// SQL Function to insert into one table and then use its last insert id to insert into a second table with a foreign key (last insert id)
// IMPROTANT: "user_id" is NOT the last insert id, but it is used as Foreign Key in all default RDP tables!
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to insert row first!
function rdp_sql_insert_into_two_tables_with_last_insert_id($mysqli, $table1, $columnsSeparatedByCommas1, $valuesAsAnArray1, $table2, $columnsSeparatedByCommas2whereLastColumnIsForeignKey, $valuesAsAnArray2whereLastInsertIdIsLastElement)
{
    // Insert into the first table and get the last inserted id
    $last_inserted_id = rdp_sql_insert_single_row_with_last_insert_id($mysqli, $table1, $columnsSeparatedByCommas1, $valuesAsAnArray1);

    // Check if the first insert was successful
    if ($last_inserted_id) {

        // Insert into the second table with the last inserted id
        $valuesAsAnArray2whereLastInsertIdIsLastElement[] = $last_inserted_id;
        return rdp_sql_insert_single_row_without_last_insert_id($mysqli, $table2, $columnsSeparatedByCommas2whereLastColumnIsForeignKey, $valuesAsAnArray2whereLastInsertIdIsLastElement);
    } else {
        return null;
    }
}

// SQL Function to select a single row from a chosen table by any column value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select single row first!
function rdp_sql_select_single_row_by_single_value($mysqli, $table, $column, $value, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT * FROM `$table` WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, $fetchType);
}

// SQL Function to select chosen columns from a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select columns first!
function rdp_sql_select_columns_from_single_table($mysqli, $table, $columns, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT $columns FROM `$table`";

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, [], $fetchType);
}

// SQL Function to select chosen columns from a chosen table by any column value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select columns first!
function rdp_sql_select_columns_by_single_value($mysqli, $table, $columns, $column, $value, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT $columns FROM `$table` WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params, $fetchType);
}


// SQL Function to select and return a single value from a single column from a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to select single column value first!
function rdp_sql_select_single_value_from_single_column($mysqli, $table, $select_col, $where_col, $colval, $fetchType = "ASSOC")
{
    // Prepare the SQL Query
    $query = "SELECT `$select_col` FROM `$table` WHERE `$where_col` = ?";

    // Prepare the parameters
    $params = [
        $colval,
    ];

    // Execute the SQL Query
    $result = rdp_sql_execute_statement_query($mysqli, $query, $params, $fetchType);

    // Return the result or null
    return $result ? $result[0][$select_col] : null;
}

// SQL Function to retrieve all values of a single column from a chosen table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to retrieve all values of a single column first!
function rdp_sql_select_all_values_of_single_column($mysqli, $table, $column)
{
    // Prepare query and check for errors
    $query = "SELECT `$column` FROM `$table`";

    // Execute the SQL Query
    $result = rdp_sql_execute_statement_query($mysqli, $query, [], "ASSOC");

    // Return the result as an array of the column or null
    if ($result) {
        $column = array_column($result, $column);
        return $column;
    } else {
        return null;
    }
}

// SQL Function to update single column value from a chosen table by chosen value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to update single column value first!
function rdp_sql_update_single_column_value_by_single_value($mysqli, $table, $column, $value, $new_value)
{
    // Prepare the SQL Query
    $query = "UPDATE `$table` SET `$column` = ? WHERE `$column` = ?";

    // Prepare the parameters
    $params = [
        $new_value,
        $value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to update a user in the "rdp_users" table
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to update user first!
function rdp_sql_update_single_user($mysqli, $user)
{
    // Prepare the SQL Query
    $query = "UPDATE rdp_users SET user_username = ?, user_fullname = ?, user_email = ?, user_password = ?, user_updated_at = ? WHERE user_id = ?";

    // Prepare the parameters
    $params = [
        $user['user_username'],
        $user['user_fullname'],
        $user['user_email'],
        $user['user_password'],
        $user['user_updated_at'],
        $user['user_id'],
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}

// SQL Function to update a single row from a chosen table by chosen value
// IMPORTANT: Sanitize data before using this function. It only protects against SQL Injections!
// IMPORTANT: Validate authorization to update single row first!
function rdp_sql_update_single_row_by_single_column_value($mysqli, $table, $set_columnsSeparatedByCommas, $newValuesAsAnArray, $where_column, $where_value)
{
    // Check if $set_columns is an array or a string and if string check if "," or ", " is used
    $set_columns = "";
    if (!is_string($set_columnsSeparatedByCommas) || !is_array($newValuesAsAnArray)) {
        return null;
    } elseif (is_string($set_columnsSeparatedByCommas)) {
        if (!str_contains($set_columnsSeparatedByCommas, ",")) {
            $set_columns = $set_columnsSeparatedByCommas . " = ?";
        } elseif (mb_strpos($set_columnsSeparatedByCommas, ", ") !== false) {
            $set_columns = str_replace(", ", " = ?, ", $set_columnsSeparatedByCommas) . " = ?";
        } elseif (mb_strpos($set_columnsSeparatedByCommas, ",") !== false) {
            $set_columns = str_replace(",", " = ?,", $set_columnsSeparatedByCommas) . " = ?";
        }
    }
    // Prepare the SQL Query
    $query = "UPDATE `$table` SET $set_columns WHERE `$where_column` = ?";

    // Prepare the parameters
    $params = [
        ...$newValuesAsAnArray,
        $where_value,
    ];

    // Execute the SQL Query
    return rdp_sql_execute_statement_query($mysqli, $query, $params);
}









// Validate a valid URI
function rdp_validate_uri($uri)
{
    return filter_var($uri, FILTER_VALIDATE_URL);
}

// Validate IP address from $_SERVER['REMOTE_ADDR'] assuming it is set
// Used primarily to check allowed IPs in database (rdp_csrf, rdp_sessions, etc.)
function rdp_validate_allowed_ips($stringOfIPs)
{
    // If "$stringOfIPs" is null, it means any IP is allowed so return true
    if ($stringOfIPs === null) {
        return true;
    }

    // If $_SERVER['REMOTE_ADDR'] is not set or invalid IP, return false
    if (!isset($_SERVER['REMOTE_ADDR']) || !filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
        return false;
    }

    // If only one IP is allowed, it is not |-separated
    if (!str_contains($stringOfIPs, '|')) {
        return $_SERVER['REMOTE_ADDR'] === $stringOfIPs;
    }
    // Otherwise, explode and check if IP is in array
    else {
        $ips = explode('|', $stringOfIPs);
        $ips = array_map('trim', $ips);
        return in_array($_SERVER['REMOTE_ADDR'], $ips);
    }
}

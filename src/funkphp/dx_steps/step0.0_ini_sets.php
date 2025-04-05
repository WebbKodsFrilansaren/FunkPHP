<?php // IMPORTANT: All steps in 0 are meant to be "set and forget" in the sense that they are not meant to be changed after the initial setup of the application.
// Step 0.0 is setting all the ini_set() that are needed for the application to run properly.
// Add your own ini_set() here if needed and/or change the default ones.

//INI_SET_START_DELIMTIER//
ini_set('session.cache_limiter', 'public'); // Prevents caching of the session pages
session_cache_limiter(false); // Prevents caching of the session pages
ini_set('session.use_only_cookies', 1); // Only use cookies for session management
ini_set('session.use_strict_mode', 1); // Prevents session fixation attacks
ini_set('session.cache_expire', 30); // Session cache expiration time in minutes
ini_set('session.cookie_lifetime', 0); // 0 = until browser is closed
ini_set('session.name', 'fphp_id'); // We overwrite other cookie named "id" with this one
ini_set('session.sid_length', 192); // Length of the session ID
ini_set('session.sid_bits_per_character', 6); // Bits per character to increase entropy
//INI_SET_END_DELIMTIER//

// ALWAYS REMOVE THE FOLLOWING LINES IN PRODUCTION!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ALWAYS REMOVE THE LINES ABOVE IN PRODUCTION!
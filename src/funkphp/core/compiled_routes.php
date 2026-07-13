<?php
// compiled_routes.php - FunkPHP | FunkCLI created/updated 2026-07-13 19:24:16
    /**
    * -----------------------------------------------------
    * FUNKPHP AUTOMATICALLY GENERATED/CREATED COMPILED FILE
    * -----------------------------------------------------
    * DO NOT MANUALLY EDIT THIS FILE.
    * If you are currently editing this file to see if FunkPHP will "self-heal",
    * it won't. This is a micro-framework, not your therapist. If you alter this
    * source of truth, your app will most likely crash, and your peer will know
    * you do not understand how caching and/or compiled files work.
    * To fix your own self-sabotage (if it was the router files
    * 'compiled_routes.php' and/or 'pipeline_routes.php'),
    * run the following Terminal Command in Working Path '/src/cli': `php funk recompile`
    */
return array (
  'TRIE' => 
  array (
    'GET' => 
    array (
      '/' => 
      array (
      ),
      ':' => 
      array (
        'users' => 
        array (
          'by_ida' => 
          array (
          ),
        ),
      ),
    ),
    'POST' => 
    array (
      ':' => 
      array (
        'users' => 
        array (
          'by_id' => 
          array (
          ),
          'by_ida' => 
          array (
          ),
        ),
      ),
    ),
    'PUT' => 
    array (
    ),
    'DELETE' => 
    array (
    ),
    'PATCH' => 
    array (
    ),
  ),
  'METADATA' => 
  array (
    '<ALL>' => 
    array (
      'totalAllRoutes' => 5,
      'totalStaticRoutes' => 1,
      'totalDynamicRoutes' => 4,
      'minURICountAll' => 0,
      'maxURICountAll' => 2,
    ),
    'GET' => 
    array (
      'allRoutes' => 
      array (
        '/' => 1,
        '/:users' => 1,
        '/:users/by_ida' => 1,
      ),
      'staticRoutes' => 
      array (
        '/' => 1,
      ),
      'dynamicRoutes' => 
      array (
        '/:users' => 1,
        '/:users/by_ida' => 1,
      ),
      'minURICount' => 0,
      'maxURICount' => 2,
      'URICountExistsForNumber' => 
      array (
        0 => 1,
        1 => 1,
        2 => 1,
      ),
      'allRoutesCount' => 3,
      'staticRoutesCount' => 1,
      'dynamicRoutesCount' => 2,
    ),
    'PATCH' => 
    array (
      'allRoutes' => 
      array (
      ),
      'staticRoutes' => 
      array (
      ),
      'dynamicRoutes' => 
      array (
      ),
      'minURICount' => 0,
      'maxURICount' => 0,
      'URICountExistsForNumber' => 
      array (
      ),
      'allRoutesCount' => 0,
      'staticRoutesCount' => 0,
      'dynamicRoutesCount' => 0,
    ),
    'POST' => 
    array (
      'allRoutes' => 
      array (
        '/:users/by_id' => 1,
        '/:users/by_ida' => 1,
      ),
      'staticRoutes' => 
      array (
      ),
      'dynamicRoutes' => 
      array (
        '/:users/by_id' => 1,
        '/:users/by_ida' => 1,
      ),
      'minURICount' => 2,
      'maxURICount' => 2,
      'URICountExistsForNumber' => 
      array (
        2 => 1,
      ),
      'allRoutesCount' => 2,
      'staticRoutesCount' => 0,
      'dynamicRoutesCount' => 2,
    ),
    'PUT' => 
    array (
      'allRoutes' => 
      array (
      ),
      'staticRoutes' => 
      array (
      ),
      'dynamicRoutes' => 
      array (
      ),
      'minURICount' => 0,
      'maxURICount' => 0,
      'URICountExistsForNumber' => 
      array (
      ),
      'allRoutesCount' => 0,
      'staticRoutesCount' => 0,
      'dynamicRoutesCount' => 0,
    ),
    'DELETE' => 
    array (
      'allRoutes' => 
      array (
      ),
      'staticRoutes' => 
      array (
      ),
      'dynamicRoutes' => 
      array (
      ),
      'minURICount' => 0,
      'maxURICount' => 0,
      'URICountExistsForNumber' => 
      array (
      ),
      'allRoutesCount' => 0,
      'staticRoutesCount' => 0,
      'dynamicRoutesCount' => 0,
    ),
  ),
);

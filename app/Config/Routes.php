<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Pocasi::index');
$routes->get('stanice/zeme/(:num)', 'Pocasi::stanice/$1');
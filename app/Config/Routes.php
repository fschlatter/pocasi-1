<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Pocasi::index');
$routes->get('zeme/(:num)','Pocasi::zeme/$1');
$routes->get('stanice/(:num)','Pocasi::stanice/$1');
$routes->get('staniceAll', 'Pocasi::staniceAll');
$routes->get('zemeInfo/(:num)','Pocasi::zemeInfo/$1');
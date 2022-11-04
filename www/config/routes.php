<?php

use App\Base\Router;
use App\Controller\PageController;
use App\Controller\UserController;

/**
 * Registration
 */
Router::post('/register', UserController::class, 'register');


/**
 * User routes
 */

Router::get('/user/([\d]+)', UserController::class, 'get');
Router::get('/user/([\d]+)/pages', PageController::class, 'getByUser');
Router::get('/user/([\d]+)/friends', UserController::class, 'getFriends');
Router::put('/user/([\d]+)/friend/([\d]+)', UserController::class, 'makeFriend');
Router::get('/user/seed', UserController::class, 'seed');
Router::get('/user/search', UserController::class, 'search');


/**
 * Page routes
 */
Router::get('/page/([\d]+)', PageController::class, 'get');
Router::post('/user/([\d]+)/page', PageController::class, 'create');
<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::get('/favicon.ico', function () {
    return '';
});

Router::addRoute(['GET', 'POST', 'HEAD'], '/javdb/v1/movie/search', 'App\Controller\JavDbController@actionMovieSearch');
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/movie/subject',
    'App\Controller\JavDbController@actionMovieSubject'
);

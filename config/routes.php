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
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/spider/subject',
    'App\Controller\JavDbController@actionSpiderSubject'
);
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/spider/subject/test',
    'App\Controller\JavDbController@actionSpiderSubjectTest'
);
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/view/subject',
    'App\Controller\JavDbController@actionViewSubject'
);
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/ajax/process/subject',
    'App\Controller\JavDbController@actionAjaxProcessSubject'
);
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/view/casts',
    'App\Controller\JavDbController@actionViewCasts'
);
Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/ajax/process/casts',
    'App\Controller\JavDbController@actionAjaxProcessCasts'
);

Router::addRoute(
    ['GET', 'POST', 'HEAD'],
    '/javdb/v1/av-helper/subjects',
    'App\Controller\JavDbController@actionAvHelperSubjects'
);

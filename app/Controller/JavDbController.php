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

namespace App\Controller;

use App\Resource\Jeenpi\Search;
use App\Resource\Jeenpi\Subject;
use App\Services\Base\GuzzleService;
use App\Services\Jav\JavDbService;
use Hyperf\Di\Annotation\Inject;

class JavDbController extends AbstractController
{
    /**
     * @Inject
     * @var GuzzleService
     */
    protected $gs;

    public function actionMovieSearch()
    {
        $key_1 = $this->request->input('Key1');
        $jds = make(
            JavDbService::class,
            [
                sprintf(
                    'https://www.mgstage.com/search/cSearch.php?search_word=%s&x=76&x=14&search_shop_id=&type=top',
                    $key_1
                )
            ]
        );
        return Search::make($jds->spider()->search())->toArray();
    }

    public function actionMovieSubject()
    {
        $url = $this->request->input('Url');
        $jds = make(JavDbService::class, [$url]);
        $subject = $jds->spider()->subject();
        return Subject::make($subject)->toArray();
    }
}

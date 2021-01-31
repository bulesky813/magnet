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
use App\Services\Jav\JavDbService;
use App\Services\Queue\QueueService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Arr;
use function Hyperf\ViewEngine\view;

class JavDbController extends AbstractController
{
    /**
     * @Inject
     * @var QueueService
     */
    protected $qs;

    public function actionMovieSearch()
    {
        $key_1 = $this->request->input('Key1');
        $jds = make(JavDbService::class, [
            sprintf(
                'https://www.mgstage.com/search/cSearch.php?search_word=%s&x=76&x=14&search_shop_id=&type=top',
                $key_1
            )
        ]);
        $search_result = $jds->spider()->search();
        if (count($search_result) == 0) {
            $search_result = $jds->uriChange(sprintf(
                'https://www.javbus.com/search/%s&type=&parent=ce',
                $key_1
            ))->spider()->search();
        }
        return Search::make($search_result)->toArray();
    }

    public function actionMovieSubject()
    {
        $url = $this->request->input('Url');
        $jds = make(JavDbService::class, [$url]);
        $subject = $jds->spider()->subject();
        return Subject::make($subject)->toArray();
    }

    public function actionSpiderSubject()
    {
        $url = $this->request->input('url');
        $jds = make(JavDbService::class, [$url]);
        $search_result = $jds->spider()->search();
        collect($search_result)->each(function ($subject, $key) {
            $url = Arr::get($subject, 'alt', '');
            $this->qs->subject([
                'url' => Arr::get($subject, 'alt', '')
            ]);
        });
    }

    public function actionViewSubject()
    {
        $subjects = make(\App\Model\Subject::class)::query()
            ->orderBy("favorites", 'desc')
            ->offset(0)
            ->limit(10)
            ->get();
        return view('subject', ['subjects' => $subjects]);
    }
}

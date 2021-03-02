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

use App\Model\Casts;
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

    public function actionSpiderSubjectTest()
    {
        $url = $this->request->input('url');
        $page = 1;
        $jds = make(JavDbService::class, [sprintf($url, $page)]);
        $subjects = $jds->spider()->search();
        return [
            'code' => 0,
            'data' => $subjects,
            'message' => ''
        ];
    }

    public function actionSpiderSubject()
    {
        $url = $this->request->input('url');
        $page = 1;
        $jds = make(JavDbService::class, [sprintf($url, $page)]);
        do {
            $search_result = $jds->spider()->search();
            collect($search_result)->each(function ($subject, $key) {
                /*$this->qs->subject([
                    'url' => Arr::get($subject, 'alt', '')
                ]);*/
            });
            $page++;
            $jds->uriChange(sprintf($url, $page));
        } while ($jds->hasNextPage());
        return [
            'code' => 0,
            'data' => [
                'page' => $page
            ],
            'message' => ''
        ];
    }

    public function actionViewSubject()
    {
        $k = $this->request->input('k', '');
        $subjects = make(\App\Model\Subject::class)::query()
            ->where('process', 0)
            ->when($k, function ($query) use ($k) {
                $query->where('number', 'like', "%$k%");
            })
            ->orderBy("favorites", 'desc')
            ->offset(0)
            ->limit(20)
            ->get();
        return view('subject', ['subjects' => $subjects]);
    }

    public function actionViewCasts()
    {
        $mCasts = make(Casts::class)::query()
            ->where('process', 0)
            ->orderBy("id", "asc")
            ->first();
        if (!$mCasts) {
            return '';
        }
        $subjects = make(\App\Model\Subject::class)::query()
            ->whereIn('number', $mCasts->works ?: [])
            ->orderByRaw('content->\'$.year\' asc')
            ->get();
        return view('casts', ['casts' => $mCasts, 'subjects' => $subjects]);
    }

    public function actionAjaxProcessSubject()
    {
        try {
            $number = $this->request->input('number');
            $subjects = make(\App\Model\Subject::class)::query()
                ->where('number', $number)
                ->update(['process' => 1]);
            return [
                'code' => 0,
                'data' => [],
                'message' => ''
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function actionAjaxProcessCasts()
    {
        try {
            $casts = $this->request->input('casts');
            $star = $this->request->input('star');
            $result = make(Casts::class)::query()
                ->where('casts', $casts)
                ->update(['process' => 1, 'star' => $star]);
            return [
                'code' => 0,
                'data' => [],
                'message' => ''
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}

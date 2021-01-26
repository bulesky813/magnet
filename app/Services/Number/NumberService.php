<?php

namespace App\Services\Number;

use App\Model\Bt;
use App\Model\Number;
use Hyperf\DbConnection\Db;
use League\Flysystem\Filesystem;
use Hyperf\Di\Annotation\Inject;

class NumberService
{
    /**
     * @Inject
     * @var Filesystem
     */
    protected $fs;

    public function process(string $match, string $regex)
    {
        $bt = Bt::query()
            ->select(["name"])
            ->whereRaw(Db::raw(sprintf("match(`name`) AGAINST('+%s' IN BOOLEAN MODE)", $match)))
            ->get();
        $bt->each(function ($work, $key) use ($regex) {
            preg_match($regex, strtoupper($work->name), $matchs);
            if (count($matchs) > 0) {
                try {
                    Number::query()->insert([
                        'number' => str_replace(" ", '', $matchs[0]),
                        'process' => 0,
                        'local' => 0
                    ]);
                    echo $matchs[0] . " completed" . PHP_EOL;
                } catch (\Throwable $e) {
                }
            }
        });
        unset($bt);
        $dir_path = sprintf('/mnt/hgfs/Adult/sister/A-日本/%s', $match);
        if ($this->fs->has($dir_path)) {
            Number::where('number', 'like', $match . '%')->update(['local' => 0]);
            $files = $this->fs->listContents($dir_path);
            collect($files)->each(function ($file, $key) {
                Number::where('number', $file['filename'])->update(['local' => 1]);
            });
            unset($files);
        }
    }
}

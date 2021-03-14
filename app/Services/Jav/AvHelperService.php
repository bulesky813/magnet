<?php

namespace App\Services\Jav;

use DiDom\Document;
use DiDom\Element;
use DiDom\Query;
use Hyperf\Utils\Arr;

class AvHelperService
{
    protected $dom;
    protected $number;
    protected $casts;

    public function __construct(string $contents, string $number = "", string $casts = "")
    {
        $this->number = $number;
        $this->casts = $casts;
        $this->dom = new Document($contents ?: null);
    }


    public function findSplitElement(): array
    {
        $casts = [];
        collect($this->dom->find('//div[@class="user-area"]/div/div', Query::TYPE_XPATH))
            ->each(function (Element $element, $key) use (&$casts) {
                if (!$element->has('//br[@clear="all"]', Query::TYPE_XPATH)) {
                    return true;
                }
                collect(explode('<br clear="all">', $element->html()))
                    ->each(function (string $html, $key) use (&$casts) {
                        if (strpos(strtoupper($html), "【{$this->number}】") !== false) {
                            $doc = make(Document::class, [$html]);
                            collect($doc->find("a"))
                                ->first(function (Element $a, $key) use (&$casts) {
                                    if (strpos($a->getAttribute("href"), 'av-help.memo.wiki') !== false) {
                                        $casts[trim($a->text())] = [
                                            'url' => $a->getAttribute('href'),
                                            'name' => $a->text(),
                                        ];
                                        return true;
                                    }
                                    return false;
                                });
                            return false;
                        } else {
                            return true;
                        }
                    });
            });
        return $casts;
    }

    public function findTableElement(): array
    {
        $casts = [];
        collect($this->dom->find('//div[@class="user-area"]/div/div', Query::TYPE_XPATH))
            ->each(function (Element $element, $key) use (&$casts) {
                if (strpos($element->getAttribute("id"), 'content_block_') === false) {
                    return true;
                }
                $actress_index = 0;
                collect($element->find("//table/*/tr[1]", Query::TYPE_XPATH))
                    ->each(function (Element $tr, $key) use (&$actress_index) {
                        foreach ($tr->children() as $index => $child) {
                            if (in_array(trim(str_replace("　", '', $child->text())), ['ACTRESS', '女優名'])) {
                                $actress_index = $index;
                                return false;
                            }
                        }
                    });
                if ($actress_index == 0) {
                    return true;
                }
                collect($element->find("//table/tbody/tr", Query::TYPE_XPATH))
                    ->each(function (Element $element, $key) use ($actress_index, &$casts) {
                        $td = $element->find("td");
                        if (count($td) > 0) {
                            $find_number = false;
                            foreach ($td[0]->find("//*/text()", Query::TYPE_XPATH) as $number) {
                                if (preg_match("/[A-Za-z]{2,}\-\d+/", strtoupper($number), $matches)) {
                                    $find_number = $matches[0] == $this->number;
                                }
                            }
                            if (!$find_number) {
                                return true;
                            }
                            collect(isset($td[$actress_index])
                                ? $td[$actress_index]->find("a") : [])
                                ->each(function (Element $a, $key) use (&$casts) {
                                    $name = trim($a->text());
                                    if ($name != '?') {
                                        $casts[$name] = [
                                            'url' => $a->getAttribute("href"),
                                            'name' => $name,
                                        ];
                                    }
                                });
                            return false;
                        }
                        return true;
                    });
            });
        return $casts;
    }

    public function findCastsElement(): array
    {
        $casts = [];
        if ($this->dom->has('//div[@class="user-area"]/div/div/pre', Query::TYPE_XPATH)) {
            collect($this->dom->find('//div[@class="user-area"]/div/div', Query::TYPE_XPATH))
                ->each(function (Element $element, $key) use (&$casts) {
                    if (strpos($element->getAttribute("id"), 'content_block_') === false) {
                        return true;
                    }
                    $find_number = false;
                    foreach ($element->find("//a/@href|//a/text()", Query::TYPE_XPATH) as $href) {
                        if (preg_match("/[A-Za-z]{2,}\-\d+/", strtoupper($href), $matches)) {
                            foreach ($matches as $match) {
                                if ($match == $this->number) {
                                    $find_number = true;
                                }
                            }
                        }
                        if ($find_number) {
                            break;
                        }
                    };
                    if (!$find_number) {
                        return true;
                    }
                    $name = trim(str_replace(
                        '- AV女優大辞典wiki',
                        '',
                        Arr::get(
                            $this->dom->find('//meta[@property="og:title"]/@content', Query::TYPE_XPATH),
                            0,
                            ''
                        )
                    ));
                    $url = Arr::get(
                        $this->dom->find('//meta[@property="og:url"]/@content', Query::TYPE_XPATH),
                        0,
                        ''
                    );
                    $casts[$name] = [
                        'url' => $url,
                        'name' => $name
                    ];
                    return false;
                });
        }
        return $casts;
    }

    public function findSubjectsElement(): array
    {
        $outputs = [];
        collect($this->dom->find(
            sprintf('//div[@class="user-area"]/div/div/table/tbody/tr/td/a[text()="%s"]', $this->casts),
            Query::TYPE_XPATH
        ))->each(function (Element $element, $key) use (&$outputs) {
            $subject = [];
            $subject['subject'] = trim($element->parent()->parent()->first("//td/a/text()", Query::TYPE_XPATH));
            foreach ($element->parent()->find("//a", Query::TYPE_XPATH) as $casts) {
                $subject['casts'][] = [
                    'name' => trim($casts->text()),
                    'url' => $casts->getAttribute("href")
                ];
            }
            $outputs[$subject['subject']] = $subject;
        });
        return $outputs;
    }
}

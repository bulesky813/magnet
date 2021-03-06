<?php
return [
    'mgstage' => [
        'search' => [
            'alt' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/h5/a/@href',
            'images' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/h5/a/img/@src',
            'directors' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/p[3]/a/text()',
            'title' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/a/p/text()',
            'year' => '',
        ],
        'subject' => [
            'genres' => [
                '//div[@class="detail_data"]/table[2]/tr[9]/td/a/text()',
                '//div[@class="detail_data"]/table/tr[8]/td/a/text()'
            ],
            'images_medium' => [
                [
                    'xpath' => '//div[@class="detail_photo"]/h2/img/@src',
                    'eval' => '$value = str_replace(["_o1_","_p_"], ["_e_","_e_"], $value);'
                ],
                [
                    'xpath' => '//div[@class="detail_data"]/div/h2/img/@src',
                    'eval' => '$value = str_replace(["_o1_","_p_"], ["_e_","_e_"], $value);'
                ]
            ],
            'title' => '/html/body/div[2]/article[2]/div[1]/h1/text()',
            'rating' => [
                [
                    'xpath' => '//td[@class="review"]/text()',
                    'eval' => function ($value, $key) {
                        if (preg_match("/[\d.]+/", $value, $matchs)) {
                            return $matchs[0];
                        }
                        return false;
                    }
                ]
            ],
            'casts' => [
                '//div[@class="detail_data"]/table[2]/tr[1]/td/a',
                '//div[@class="detail_data"]/table/tr[1]/td/text()'
            ],
            'year' => [
                [
                    'xpath' => '//div[@class="detail_data"]/table/tr',
                    'eval' => function (\DiDom\Element $value, $key) {
                        if (strpos($value->find('th')[0]->text(), '配信開始日') !== false) {
                            try {
                                return \Carbon\Carbon::parse(trim($value->find('td')[0]->text()))
                                    ->format('Y/m/d');
                            } catch (\Throwable $e) {
                                return '1979/01/01';
                            }
                        } else {
                            return false;
                        }
                    }
                ]
            ],
            'summary' => '//p[@class="txt introduction"]/text()',
            'number' => [
                [
                    'xpath' => '//div[@class="detail_data"]/table/tr',
                    'eval' => function (\DiDom\Element $value, $key) {
                        if (strpos($value->find('th')[0]->text(), '品番') !== false) {
                            if (preg_match("/[A-Za-z]{2,}\-\d+/", $value->find('td')[0]->text(), $matchs)) {
                                return trim($matchs[0]);
                            }
                        } else {
                            return false;
                        }
                    }
                ]
            ],
            'images_content' => '//*[@id="sample-photo"]/dd/ul/li/a/@href',
            'favorites' => [
                [
                    'xpath' => '//dl[@class="detail_fav_cnt"]/text()',
                    'eval' => function (string $value, $key) {
                        if (preg_match("/[\d,]+/", $value, $matchs)) {
                            return $matchs[0];
                        } else {
                            return false;
                        }
                    }
                ],
                [
                    'xpath' => '//div[@class="detail_data"]/table/tbody/tr',
                    'eval' => function (\DiDom\Element $value, $key) {
                        if (strpos($value->find('td')[0]->text(), '登録数') !== false) {
                            return str_replace(',', '', $value->find('td')[1]->text());
                        } else {
                            return false;
                        }
                    }
                ]
            ]
        ]
    ],
    'javbus' => [
        'search' => [
            'alt' => '//*[@id="waterfall"]/div/a/@href',
            'images' => '//div[@id="waterfall"]/div/a/div[1]/img/@src',
            'directors' => '',
            'title' => '//div[@id="waterfall"]/div/a/div[1]/img/@title',
            'year' => '//div[@id="waterfall"]/div/a/div[2]/span/date[2]/text()',
        ],
        'subject' => [
            'genres' => '//span[@class="genre" and not(@onmouseover)]/label/a[1]/text()',
            'images_medium' => '//a[@class="bigImage"]/@href',
            'title' => '//div[@class="container"]/h3/text()',
            'rating' => '',
            'casts' => '//div[@class="star-name"]/a',
            'year' => [
                [
                    'xpath' => '//div[@class="col-md-3 info"]/p',
                    'eval' => function (\DiDom\Element $value, $key) {
                        if (count($value->find("span")) > 0 &&
                            strpos($value->find("span")[0]->text(), '發行日期') !== false) {
                            try {
                                return \Carbon\Carbon::parse(preg_replace("/[^\d\/\-]+/", "", $value->text()))
                                    ->format('Y/m/d');
                            } catch (\Throwable $e) {
                                return '1979/01/01';
                            }
                        } else {
                            return false;
                        }
                    }
                ]
            ],
            "number" => [
                [
                    'xpath' => '//div[@class="col-md-3 info"]/p[1]/span[2]/text()',
                    'eval' => function (string $value, $key) {
                        if (preg_match("/[A-Za-z]{2,}\-\d+/", $value, $matchs)) {
                            return trim(strtoupper($matchs[0]));
                        } else {
                            return false;
                        }
                    }
                ]
            ],
            "images_content" => '//a[@class="sample-box"]/@href'
        ]
    ],
    'dmm' => [
        'search' => [
            'alt' => '//ul[@id="list"]/li/div/p[@class="tmb"]/a/@href',
            'images' => '//span[@class="img"]/img/@src',
            'directors' => '',
            'title' => '//span[@class="txt"]/text()',
            'year' => '',
        ],
        'subject' => [
            'genres' => '//table[@class="mg-b20"]/tr[11]/td[2]/a/text()',
            'images_medium' => '//div[@id="sample-video"]/a/img/@src',
            'title' => '//h1[@id="title"]/text()',
            'rating' => '//p[@class="d-review__average"]/strong/text()',
            'casts' => '//table[@class="mg-b20"]/tr[6]/td[2]/span/a',
            'year' => '//table[@class="mg-b20"]/tr[4]/td[2]/text()',
            'summary' => '//div[@class="mg-b20 lh4"]/text()',
            'number' => '//table[@class="mg-b20"]/tr[12]/td[2]/text()',
            'images_content' => '//a[@name="sample-image"]/img/@src',
            'favorites' => '//span[@class="tx-count"]/span[1]/text()'
        ]
    ]
];

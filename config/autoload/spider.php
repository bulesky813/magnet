<?php
return [
    'mgstage' => [
        'search' => [
            'alt' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/h5/a/@href',
            'images' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/h5/a/img/@src',
            'directors' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/p[3]/a/text()',
            'title' => '/html/body/div[2]/article[2]/div[2]/div/ul/li/a/p/text()',
            'year' => '',
        ]
    ],
    'javbus' => [
        'search' => [
            'alt' => '//*[@id="waterfall"]/div/a/@href',
            'images' => '//div[@id="waterfall"]/div/a/div[1]/img/@src',
            'directors' => '',
            'title' => '//div[@id="waterfall"]/div/a/div[1]/img/@title',
            'year' => '',
        ]
    ]
];

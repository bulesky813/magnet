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
            'genres' => '//div[@class="detail_data"]/table[2]/tr[9]/td/a/text()',
            'images_medium' => '//div[@class="detail_photo"]/h2/img/@src',
            'title' => '/html/body/div[2]/article[2]/div[1]/h1/text()',
            'rating' => '//td[@class="review"]/text()',
            'casts' => '//div[@class="detail_data"]/table[2]/tr[1]/td/a'
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
            'genres' => '//span[@class="genre"]/a[1]/text()',
            'images_medium' => '//a[@class="bigImage"]/@href',
            'title' => '//div[@class="container"]/h3/text()',
            'rating' => '',
            'casts' => '//div[@class="star-name"]/a'
        ]
    ]
];
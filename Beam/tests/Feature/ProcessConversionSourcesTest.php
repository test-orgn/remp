<?php

namespace Tests\Feature;

use App\Account;
use App\Article;
use App\Console\Commands\ProcessConversionSources;
use App\Conversion;
use App\Model\ConversionCommerceEvent;
use App\Model\ConversionSource;
use App\Property;
use Carbon\Carbon;
use Remp\Journal\Journal;
use Remp\Journal\JournalContract;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ProcessConversionSourcesTest extends TestCase
{
    use RefreshDatabase;

    public function testCommandCall()
    {
        $paymentEvent = <<<JSON
[
    {
        "commerces":[
            {
                "id":"b2b725ee-9424-4e0a-a869-892a21c04993",
                "payment":{
                    "funnel_id":"",
                    "product_ids":[
                        "product_1"
                    ],
                    "revenue":{
                        "amount":18.99,
                        "currency":"EUR"
                    },
                    "transaction_id":""
                },
                "source":{
                },
                "step":"payment",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:36:47Z"
                },
                "user":{
                    "id":"26",
                    "remp_pageview_id":"",
                    "url":"http://localhost:63342/remp-sample-blog/index.html",
                    "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36"
                }
            }
        ],
        "tags":{
            "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358"
        }
    }
]
JSON;
        $pageviews1 = <<<JSON
[
    {
        "pageviews":[
            {
                "article":{
                    "id":"9",
                    "locked":false
                },
                "id":"551cce89-3d0a-40e0-b078-c09ab20d53c2",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:35:56Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "referer":"http://localhost:63342/remp-sample-blog/index.html?_ijt=kpug1k77p39icds5lv3bgeta46",
                    "remp_pageview_id":"551cce89-3d0a-40e0-b078-c09ab20d53c2",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                    },
                    "url":"http://localhost:63342/remp-sample-blog/single.html?_ijt=4vrl747pu6r74aa9c86papqcj2",
                    "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36"
                }
            },
            {
                "article":{
                    "id":"10",
                    "locked":false
                },
                "id":"4ef0d079-59ee-4e4c-b7ba-708aec2edc43",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:36:04Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "referer":"http://localhost:63342/remp-sample-blog/single.html?_ijt=4vrl747pu6r74aa9c86papqcj2",
                    "remp_pageview_id":"4ef0d079-59ee-4e4c-b7ba-708aec2edc43",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                    },
                    "url":"http://localhost:63342/remp-sample-blog/single2.html",
                    "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36"
                }
            },
            {
                "id":"6596b3f5-3588-49bd-a3ab-8ca32541c1b2",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:36:16Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "id":"26",
                    "referer":"http://localhost:63342/remp-sample-blog/single2.html",
                    "remp_pageview_id":"6596b3f5-3588-49bd-a3ab-8ca32541c1b2",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                    },
                    "url":"http://localhost:63342/remp-sample-blog/index.html",
                    "user_agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36"
                }
            }
        ],
        "tags":{
        }
    }
]
JSON;
        $pageviews2 = <<<JSON
[
    {
        "pageviews":[
            {
                "id":"ed580e88-f699-4bcd-bac0-96a5be99a57d",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:35:49Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "remp_pageview_id":"ed580e88-f699-4bcd-bac0-96a5be99a57d",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                    },
                    "url":"http:\/\/localhost:63342\/remp-sample-blog\/index.html?_ijt=kpug1k77p39icds5lv3bgeta46",
                    "user_agent":"Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/84.0.4147.105 Safari\/537.36"
                }
            }
        ],
        "tags":{
            "derived_referer_host_with_path":"",
            "derived_referer_medium":"direct",
            "derived_referer_source":""
        }
    },
    {
        "pageviews":[
            {
                "id":"6596b3f5-3588-49bd-a3ab-8ca32541c1b2",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:36:16Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "id":"26",
                    "referer":"http:\/\/localhost:63342\/remp-sample-blog\/single2.html",
                    "remp_pageview_id":"6596b3f5-3588-49bd-a3ab-8ca32541c1b2",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                    },
                    "url":"http:\/\/localhost:63342\/remp-sample-blog\/index.html",
                    "user_agent":"Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/84.0.4147.105 Safari\/537.36"
                }
            }
        ],
        "tags":{
            "derived_referer_host_with_path":"http:\/\/localhost:63342\/remp-sample-blog\/single2.html",
            "derived_referer_medium":"internal",
            "derived_referer_source":""
        }
    },
    {
        "pageviews":[
            {
                "article":{
                    "id":"9",
                    "locked":false
                },
                "id":"00a05dfd-e6d6-4ccb-b57f-1ee2d1191910",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:35:58Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "referer":"http:\/\/localhost:63342\/remp-sample-blog\/index.html?_ijt=kpug1k77p39icds5lv3bgeta46",
                    "remp_pageview_id":"00a05dfd-e6d6-4ccb-b57f-1ee2d1191910",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                        
                    },
                    "url":"http:\/\/localhost:63342\/remp-sample-blog\/single.html?_ijt=4vrl747pu6r74aa9c86papqcj2",
                    "user_agent":"Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/84.0.4147.105 Safari\/537.36"
                }
            },
            {
                "article":{
                    "id":"9",
                    "locked":false
                },
                "id":"a99b6c37-9ce8-495c-a47c-a5c67044daa0",
                "system":{
                    "property_token":"dc48299e-55bd-4b7b-89ce-3e410380fe39",
                    "time":"2020-08-06T14:36:33Z"
                },
                "user":{
                    "browser_id":"6f3b2834-5bdf-41d2-a9e3-b3a12a588358",
                    "referer":"http:\/\/localhost:63342\/remp-sample-blog\/index.html?_ijt=kpug1k77p39icds5lv3bgeta46",
                    "remp_pageview_id":"a99b6c37-9ce8-495c-a47c-a5c67044daa0",
                    "remp_session_id":"c01cea09-cf10-4dd4-bea6-0608cd43157c",
                    "source":{
                    },
                    "url":"http:\/\/localhost:63342\/remp-sample-blog\/single.html?_ijt=4vrl747pu6r74aa9c86papqcj2",
                    "user_agent":"Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/84.0.4147.105 Safari\/537.36"
                }
            }
        ],
        "tags":{
            "derived_referer_host_with_path":"http:\/\/localhost:63342\/remp-sample-blog\/index.html",
            "derived_referer_medium":"internal",
            "derived_referer_source":""
        }
    }
]
JSON;

        $account = factory(Account::class)->create();
        $property = factory(Property::class)->create(['account_id' => $account->id]);
        $referalArticle = factory(Article::class)->create([
            'external_id' => 9,
            'property_uuid' => $property->uuid,
        ]);
        $conversionArticle = factory(Article::class)->create([
            'external_id' => 10,
            'property_uuid' => $property->uuid,
        ]);
        $conversion = factory(Conversion::class)->create(['user_id' => 26, 'article_id' => $conversionArticle, 'events_aggregated' => true]);
        factory(ConversionCommerceEvent::class)->create(['conversion_id' => $conversion,'step' => 'payment', 'time' => Carbon::createFromTimeString('2020-08-06T14:36:47Z')]);

        // Mock Journal data
        $journalMock = Mockery::mock(Journal::class);
        $journalMock->shouldReceive('list')->andReturn(
            json_decode($paymentEvent),
            json_decode($pageviews1),
            json_decode($pageviews2)
        );

        // Bypass RempJournalServiceProvider binding
        $this->app->instance('mockJournal', $journalMock);
        $this->app->when(ProcessConversionSources::class)
            ->needs(JournalContract::class)
            ->give('mockJournal');

        $this->artisan(ProcessConversionSources::COMMAND, ['--conversion_id' => $conversion->id]);

        //retrieve processed conversion sources
        $conversionSources = ConversionSource::where(['conversion_id' => $conversion->id])->get();
        $firstConversionSource = $conversionSources->where('type', 'first')->first();
        $lastConversionSource = $conversionSources->where('type', 'last')->first();

        $this->assertEquals(2, $conversionSources->count());
        $this->assertEquals('direct', $firstConversionSource->referer_medium);
        $this->assertEquals('internal', $lastConversionSource->referer_medium);
        $this->assertNull($firstConversionSource->referer_host_with_path);
        $this->assertEquals('http://localhost:63342/remp-sample-blog/index.html', $lastConversionSource->referer_host_with_path);
        $this->assertEquals($referalArticle->external_id, $lastConversionSource->pageview_article_external_id);
    }
}

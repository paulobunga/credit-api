<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Web',
    'as' => 'web',
    'middleware' => [
        "domain:" . env('WEB_DOMAIN')
    ],
], function ($api) {
    $api->get("/", ['as' => 'index', 'uses' => 'PageController@index']);
});

<?php

$api->group([
    'namespace' => 'App\Http\Controllers\Web',
    'as' => 'web',
    'middleware' => [
        "domain:www.credit-api.test|credit-api.test"
    ],
], function ($api) {
    $api->get("/", ['as' => 'index', 'uses' => 'PageController@index']);
});

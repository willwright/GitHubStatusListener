<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware('api')
    ->group(function(){
        Route::match(['get','post'],'events/github', 'MeCodeNinja\GitHubWebhooks\Http\Controllers\Events\GithubController@index');
    });
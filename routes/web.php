<?php

use App\Models\Link;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/visit/{short_link}', function (string $short_link) {
    $link = Link::where('short_link', $short_link)->first();

    if (!$link) {
        abort(404);
    }

    $link->views++;
    $link->save();
    $link->refresh();
    dd($link);
//    return Redirect::to($link->full_link, 301);
});

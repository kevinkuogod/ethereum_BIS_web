<?php

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'TraceController@index')->name('trace.index');
Route::post('/trace/get_leager_site_data', 'TraceController@list_asset_main_leager_site')->name('trace.list_asset_main_leager_site');
Route::post('/trace/get_switch_site_data', 'TraceController@get_switch_site_data')->name('trace.get_switch_site_data');
Route::post('trace/get_validation_data', 'TraceController@get_validation_data')->name('trace.get_validation_data');
Route::post('trace/get_video_data', 'TraceController@get_video_data')->name('trace.get_video_data');
Route::post('trace/manual_trigger_background_job', 'TraceController@manual_trigger_background_job')->name('trace.manual_trigger_background_job');
// Route::get('trace/manual_trigger_background_job', 'TraceController@manual_trigger_background_job')->name('trace.manual_trigger_background_job');
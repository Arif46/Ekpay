<?php

use Illuminate\Support\Facades\Route;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/helper-test', function () {
    return user_id();
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/helper-test', function () {
    return user_id();
});

$router->get('/notification-event-test', 'NotificationController@fireEventTest');
$router->get('/notification-sender/send-notification', 'NotificationController@sendNotification');
$router->post('/notification-sender/send-notification', 'NotificationController@sendNotification');
$router->get('/notification-sender/notification', 'NotificationController@notifications');
$router->post('/device-token/store', 'DeviceTokenController@notifications');
$router->get('/notification-received/list', 'NotificationController@receivedNotifications');
$router->get('/notification-seen/{id}', 'NotificationController@notificationSeen');


/******************** Data Archive Module *********************/
Route::group(['prefix' => '/data-archive'], function () {
    Route::get('/database-backup', 'DataArchiveController@dumpDB');
    //download file path from storage
    Route::get('download-backup-db', 'DataArchiveController@downloadBackupDb');
    Route::get('db-backup-files', 'DataArchiveController@getDbBackupFiles');
    Route::delete('db-backup-delete', 'DataArchiveController@deleteDbBackupFile');
});

Route::get('download-attachment', 'DownloadController@downloadAttachment');
Route::get('generate-base64-image', 'DownloadController@generateBase64Image');

Route::get('common-dropdowns', function () {
    // Caching commonly used dropdown for 24 hours = 86400s and this should be only on live server
    // $value = \Illuminate\Support\Facades\Cache::remember('commonDropdown', 0, function () {
    //     return [];
    // });
    $list = [
        'serviceTypeList' => \App\Library\DropDowns::serviceTypeList(),
        'eventTypeList' => \App\Library\DropDowns::eventTypeList(),
        'eventList' => \App\Library\DropDowns::eventList(),
        'serviceProcessStepList' => \App\Library\DropDowns::serviceProcessStepList(),
        'fairStallList' => \App\Library\DropDowns::fairStallList(),
        'patientCategoryList' => \App\Library\DropDowns::patientCategoryList(),
        'stallTypeList' => \App\Library\DropDowns::stallTypeList(),
        'patientList' => \App\Library\DropDowns::patientList(),
        'stallList' => \App\Library\DropDowns::stallList(),
        'employerList' => \App\Library\DropDowns::employerList(),
        'supplierInfoList' => \App\Library\DropDowns::supplierInfoList(),
        'circularList' => \App\Library\DropDowns::circularList(),
        'groupList' => \App\Library\DropDowns::groupList(),
        'instituteList' => \App\Library\DropDowns::instituteList(),
        'skillList' => \App\Library\DropDowns::skillList(),
        'educationLevelList' => \App\Library\DropDowns::educationLevelList(),
        'degreeList' => \App\Library\DropDowns::degreeList(),
    ];
    return response()->json([
        'success' => true,
        'data' => $list
    ]);
});

Route::get('common-dropdowns-external-patient', function () {
    // Caching commonly used dropdown for 24 hours = 86400s and this should be only on live server
    // $value = \Illuminate\Support\Facades\Cache::remember('commonDropdown', 0, function () {
    //     return [];
    // });
    $list = [
        'serviceTypeList' => \App\Library\DropDowns::serviceTypeList(),
        'patientCategoryList' => \App\Library\DropDowns::patientCategoryList(),
        'groupList' => \App\Library\DropDowns::groupList(),
        'instituteList' => \App\Library\DropDowns::instituteList(),
        'skillList' => \App\Library\DropDowns::skillList(),
        'educationLevelList' => \App\Library\DropDowns::educationLevelList(),
        'degreeList' => \App\Library\DropDowns::degreeList(),
        'circularList' => \App\Library\DropDowns::circularList(),
    ];
    return response()->json([
        'success' => true,
        'data' => $list
    ]);
});

Route::get('common-dropdowns-external-employer', function () {
    $list = [
        'serviceTypeList' => \App\Library\DropDowns::serviceTypeList(),
        'patientCategoryList' => \App\Library\DropDowns::patientCategoryList(),
        'groupList' => \App\Library\DropDowns::groupList(),
        'instituteList' => \App\Library\DropDowns::instituteList(),
        'skillList' => \App\Library\DropDowns::skillList(),
        'educationLevelList' => \App\Library\DropDowns::educationLevelList(),
        'degreeList' => \App\Library\DropDowns::degreeList(),
        'circularList' => \App\Library\DropDowns::circularList(),
    ];
    return response()->json([
        'success' => true,
        'data' => $list
    ]);
});

Route::get('common-dropdowns-external-stall-participant', function () {
    $list = [
        'eventTypeList' => \App\Library\DropDowns::eventTypeList(),
        'eventList' => \App\Library\DropDowns::eventList(),
        'stallTypeList' => \App\Library\DropDowns::stallTypeList(),
        'fairStallList' => \App\Library\DropDowns::fairStallList(),
    ];
    return response()->json([
        'success' => true,
        'data' => $list
    ]);
});

include('configuration.php');
include('eventManagement.php');
include('patientPanel.php');
include('stallParticipant.php');
include('managementInformationSystem.php');
include('ims.php');
include('employer_panel.php');
include('job_fair_management.php');
include('adaim.php');
include('addadm.php');
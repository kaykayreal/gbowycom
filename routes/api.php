<?php

use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\testController;
use App\Models\remitaInflightCollection;
use App\Http\Controllers\remita\RITSController;
use App\Http\Controllers\RefreshTokenController;
use App\Http\Controllers\etranzact\etzController;
use App\Http\Controllers\gbowy\gbowyUserController;
use App\Http\Controllers\etranzact\etzTxnController;
use App\Http\Controllers\Vendors\capricornController;
use App\Http\Controllers\Vendors\creditSwitchController;
use App\Http\Controllers\etranzact\eTzInflightController;
use App\Http\Controllers\remita\RemitaInflightController;
use App\Http\Controllers\gbowyNet\gbowyNetWalletController;

Route::group(['prefix' => 'flutterwave'], function () {
    Route::post('/flutterWebhook', [APIController::class, 'flutterWebhook'])->name('flutterWebhook');

    // Add more routes specific to the APIController if needed
});

//etx public  routes 
Route::group(['prefix' => 'eTz'], function () {
    Route::post('/notification/payment', [eTzInflightController::class, 'eTzCPayNotification'])->name('eTzCPayNotification');
});

//to include protection! 
Route::group(['prefix' => 'gbowyNetWallet', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/notification/payment', [gbowyNetWalletController::class, 'gbowyNetWallets']);
});
//etz protected routes
Route::group(['prefix' => 'eTz', 'middleware' => 'enforce.json', 'auth:sanctum'], function () {
    Route::get('/init/encrypt', [etzController::class, 'encript'])->name('encript');
    Route::get('/init/getCorporates', [etzController::class, 'eTzGetCorporate'])->name('eTzGetCorporate');

    Route::post('/init/getAllCorporate', [etzController::class, 'getCorporate']);
    Route::post('/init/getSalaryInfo', [etzController::class, 'getSalaryInfo']);
    Route::post('/init/getEmployeeCorporate', [etzController::class, 'getEmployeeCorporate']);
    Route::post('/init/getCorporateEmployees', [etzController::class, 'getCorporateEmployees'])->name('eTzGetCorporateEmployees');

    Route::post('/Loans/createLoan', [etzController::class, 'createLoan']);


    // this is for client to force new token from service provider.
    Route::get('/init/getToken', [etzController::class, 'getToken'])->name('getToken');
});


Route::group(['prefix' => 'remita'], function () {
    Route::post('/notification/payment', [RemitaInflightController::class, 'remitaInflightCollections'])->name('RemitaNotification');
});

//gbowy public routes
Route::group(['prefix' => 'admin'], function () {
    Route::post('/user/createToken', [gbowyUserController::class, 'createToken']);
    Route::post('/user/register', [gbowyUserController::class, 'register']);
});

//gbowy protected routes 
Route::group(['prefix' => 'admin', 'middleware' => 'enforce.json', 'auth:sanctum'], function () {

    Route::post('/token/refreshToken', [gbowyUserController::class, 'refreshToken']);
    Route::post('loan/createLoan', [etzTxnController::class, 'createLoan']);
    Route::post('loan/getLoanDetails', [etzController::class, 'getLoanDetails']);
    Route::post('loan/getLoanHistory', [etzController::class, 'getLoanHistory']);
    Route::post('loan/createRepayment', [etzTxnController::class, 'createRepayment']);
    Route::post('loan/repay', [etzController::class, 'createRepaymentNew']);

    //remita endpoints

});

Route::group(['prefix' => 'cs'], function () {
    Route::post('/lookup', [creditSwitchController::class, 'cablePlans']);
    Route::post('/lookupStartimes', [creditSwitchController::class, 'cableStartimes']);
    Route::post('/validateMultichoice', [creditSwitchController::class, 'validateMultiChoiceCustomer']);
    Route::post('/Requery', [creditSwitchController::class, 'txnRequery']);
    Route::post('/getBalance', [creditSwitchController::class, 'getBalance']);
    Route::post('/getInsurancePackages', [creditSwitchController::class, 'getInsurancePackages']);
});

Route::group(['prefix' => 'cp'], function () {

    Route::post('/lookup', [capricornController::class, 'lkup']);
    Route::post('/verifyTxn', [capricornController::class, 'verifyTxn']);
});

//test api_endpoints
Route::group(['prefix' => 'tests'], function () {
    Route::post('/encrypt', [testController::class, 'encrypt']);
    Route::post('/decrypt', [testController::class, 'decrypt']);
    Route::get('/getsalary', [testController::class, 'getSalaryInfo']);
    Route::post('/lookup', [testController::class, 'lkup']);
    //creditswitch
    Route::post('/csairtime', [creditSwitchController::class, 'vendAirtime']);
    Route::post('/csSMS', [creditSwitchController::class, 'pushSMS']);
    Route::get('/csDataPlans', [creditSwitchController::class, 'dataPlans']);

    //rits test endpoints 
    Route::post('/getaccount', [testController::class, 'getaccount']);
    Route::get('/getnewtoken', [RITSController::class, 'getnewRITSToken']);
    Route::get('/gettoken', [RITSController::class, 'getRemitaRequestHeaders']);

    //dot net notification test endpoints
    Route::get('/generateToken', [RemitaInflightController::class, 'generateDotNetToken']);
    Route::get('/notify', [RemitaInflightController::class, 'notifyRemita']);

    //capricorn open endpoints

});

Route::group(['prefix' => 'capricorn'], function () {
    //capricorn open endpoints
    Route::get('/getCapricornBalance', [capricornController::class, 'getBalance']);
});

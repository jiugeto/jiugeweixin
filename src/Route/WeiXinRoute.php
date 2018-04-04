<?php

/**
 * 微信对接、管理路由
 */


use JiugeTo\WeiXinLaravel5\Facades\Auth;


/***
 * 用户端
 */
Route::group(['prefix'=>'wx'],function () {
    //获取用户微信信息
    Route::get('auth',function(){ return Auth::getCode(); });
    Route::get('auth/wxinfo',function(){ return Auth::getWxInfo(); });
    //微信token验证、消息推送
    Route::get('check',function(){ return Auth::checkSignature(); });
    Route::post('check',function(){ return Auth::responseMsg(); });
});

/***
 * 后台微信管理
 */
Route::group(['prefix'=>'admin/wx'],function () {
    //关注回复信息
    Route::get('msg/follow', 'MsgController@getFollow');
    Route::post('msg/follow', 'MsgController@setFollow');
    //消息路由
    Route::post('msg/{id}', 'MsgController@update');
    Route::resource('msg', 'MsgController');
});
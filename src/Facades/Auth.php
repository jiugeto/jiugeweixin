<?php
namespace JiugeTo\WeiXinLaravel5\Facades;

use Illuminate\Support\Facades\Facade;
use JiugeTo\WeiXinLaravel5\Controllers\WX\AuthController as JiugeAuth;
use JiugeTo\WeiXinLaravel5\Controllers\WX\MsgController as JiugeMsg;

class Auth extends Facade
{
    /**
     * 微信授权
     */

    public static function getCode()
    {
        return JiugeAuth::getCode();
    }

    public static function getWxInfo()
    {
        return JiugeAuth::getWxInfo();
    }

    public static function checkSignature()
    {
        return JiugeMsg::checkSignature();
    }

    public static function responseMsg()
    {
        return JiugeMsg::responseMsg();
    }
}
<?php
namespace JiugeTo\WeiXinLaravel5\Facades;

use Illuminate\Support\Facades\Facade;
use JiugeTo\WeiXinLaravel5\Controllers\Admin\AdminMsgController as JiugeMsg;

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
}
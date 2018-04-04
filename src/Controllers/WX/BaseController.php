<?php
namespace JiugeTo\WeiXinLaravel5\Controllers\WX;

use JiugeTo\WeiXinLaravel5\Controllers\BaseController as Controller;

class BaseController extends Controller
{
    /**
     * 基本控制器
     */

    public static function __construct()
    {
        define('WX_DOMAIN',config('jiugewx.domain').'/wx/');
    }
}
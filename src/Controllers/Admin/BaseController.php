<?php
namespace JiugeTo\WeiXinLaravel5\Controllers\Admin;

use JiugeTo\WeiXinLaravel5\Controllers\BaseController as Controller;

class BaseController extends Controller
{
    /**
     * 基本控制器
     */

    public static function __construct()
    {
        define('ADMIN_WX_DOMAIN',config('jiugewx.domain').'/admin/wx/');
    }
}
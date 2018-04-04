<?php
namespace JiugeTo\WeiXinLaravel5\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * 基本控制器
     */

    protected static $prefix;

    /**
     * curl请求
     * $url地址、$mehod方法、$type格式、$string数据
     */
    public static function getCurl($url,$method='get',$type='json',$string='')
    {
        $curl = curl_init(); //初始化
        //设置参数
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        if ($method=='post') {
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$string);
        }
        $output = curl_exec($curl); //采集
        if ($type=='json') {
//            echo curl_errno($curl);
//            if (curl_errno($curl)) {
//                return curl_errno($curl);
//            } else {
//                return json_decode($output,true);
//            }
            if (curl_errno($curl)) { return false; }
            return json_decode($output,true);
        }
    }
}
<?php
namespace JiugeTo\WeiXinLaravel5\Controllers\WX;

use Illuminate\Http\Request;
use Session;

class AuthController extends BaseController
{
    /**
     * 微信网页授权
     */

    public static function __construct()
    {
        parent::__construct();
        self::$prefix = WX_DOMAIN.'/auth';
        view()->share('prefix',self::$prefix);
    }

    /**
     * 获取code
     */
    public static function getCode()
    {
        //https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
        $appid = env('WECHAT_APPID');
        $redirect_uri = urlencode(self::$prefix."/wxinfo");
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize";
        $url .= '?appid='.$appid;
        $url .= '&redirect_uri='.$redirect_uri;
        $url .= '&response_type=code';
        $url .= '&scope=snsapi_userinfo';
        $url .= '&state=123';
        $url .= '#wechat_redirect';
        header('location:'.$url);
    }

    /**
     * 获取用户微信信息
     */
    public static function getWxInfo()
    {
        //////////获取token
        $tokenArr = self::getToken();
        //////////获取用户信息
        //GET（请使用https协议） https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        $url2 = 'https://api.weixin.qq.com/sns/userinfo';
        $url2 .= '?access_token='.$tokenArr['token'];
        $url2 .= '&openid='.$tokenArr['openid'];
        $url2 .= '&lang=zh_CN';
        $curl2 = self::getCurl($url2,'get');
        return array(
            'openid' => $curl2['openid'],
            'nickname' => $curl2['nickname'],
            'sex' => $curl2['sex'],
            'country' => $curl2['country'],
            'province' => $curl2['province'],
            'city' => $curl2['city'],
            'head' => $curl2['headimgurl'],
        );
    }

    /**
     * 用户详细授权，access_token
     */
    public static function getToken()
    {
        //GET（请使用https协议） https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
        $appid = env('WECHAT_APPID');
        $appsecret = env('WECHAT_SECRET');
        $code = $_GET['code'];
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $url .= '?appid='.$appid;
        $url .= '&secret='.$appsecret;
        $url .= '&code='.$code;
        $url .= '&grant_type=authorization_code ';
        $curl = self::getCurl($url,'get');
        $token = $curl['access_token'];
        $openid = $curl['openid'];
        return array(
            'token' => $token,
            'openid' => $openid,
        );
    }
}
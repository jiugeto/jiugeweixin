<?php
namespace JiugeTo\WeiXinLaravel5\Controllers\WX;

use JiugeTo\WeiXinLaravel5\Models\MsgModel;
use Illuminate\Support\Facades\Input;

class MsgController extends BaseController
{
    /**
     * 微信验证
     * 消息回复
     */

    protected static $msgTime; //回复消息时间

    public static function __construct()
    {
        parent::__construct();
        self::$msgTime = time();
    }

    /**
     * 验证token
     */
    public static function checkSignature()
    {
        $data = Input::all();
        $signature = $data["signature"];
        $timestamp = $data["timestamp"];
        $nonce = $data["nonce"];
        $echostr = $data["echostr"];
        $token = config('jiugewx.wechat_token');
        //对数组进行排序
        $tmpArr = array($nonce,$timestamp,$token);
        sort($tmpArr);
        $tmpStr = sha1(implode($tmpArr));
        //这样利用只有微信端和我方了解的token作对比,验证访问是否来自微信官方.
//        file_put_contents("weixin.log", "\n111", FILE_APPEND);
        if($tmpStr==$signature && $echostr){
            //第一次接入微信API
            echo $echostr; exit;
        } else {
            ////第二次回复消息////
//            file_put_contents("weixin.log", "\n222", FILE_APPEND);
            self::responseMsg();
        }
    }

    /**
     * 消息回复，事件推送
     * 接收数据包模板
     * 回复数据包模板
     * **纯文本
    <xml>
    <ToUserName><![CDATA[toUser]]></ToUserName>
    <FromUserName><![CDATA[FromUser]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[subscribe]]></Event>
    </xml>
     * ********
     * **图文
     *  <xml>
    <ToUserName><![CDATA[toUser]]></ToUserName>
    <FromUserName><![CDATA[fromUser]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>2</ArticleCount>
    <Articles>
    <item>
    <Title><![CDATA[title1]]></Title> 
    <Description><![CDATA[description1]]></Description>
    <PicUrl><![CDATA[picurl]]></PicUrl>
    <Url><![CDATA[url]]></Url>
    </item>
    <item>
    <Title><![CDATA[title]]></Title>
    <Description><![CDATA[description]]></Description>
    <PicUrl><![CDATA[picurl]]></PicUrl>
    <Url><![CDATA[url]]></Url>
    </item>
    </Articles>
    </xml>
     */

    /**
     * 对接微信服务器的测试
     */
    public static function responseMsg()
    {
        $postXml = file_get_contents('php://input');
        $postObj = simplexml_load_string($postXml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (empty($postObj)) { echo "";exit; }
        libxml_disable_entity_loader(true);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $temp = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>".time()."</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";
        if (strtolower($postObj->Event)=="subscribe") { //关注事件
            $model = MsgModel::where('genre',1)->where('del',0)->first();
            if ($model) {
                $contentStr = $model->name."\n" .$model->detail;
            } else {
                $contentStr = "欢迎关注智慧东站测试公众号！";
            }
            $resultStr = sprintf($temp,$fromUsername,$toUsername,$contentStr);
            echo $resultStr; exit;
        }
        if ($keyword=strtolower(trim($postObj->Content))) { //关键词回复
            $model = MsgModel::where('keyword',$keyword)
                ->where('genre',2)
                ->where('del',0)
                ->first();
            if (!$model) { //无匹配关键字
                $contentStr = "你的关键字无匹配信息，";
                $contentStr .= self::getNoMsg();
                $resultStr = sprintf($temp,$fromUsername,$toUsername,$contentStr);
                echo $resultStr; exit;
            }
            $contentStr = $model->name."\n" .$model->detail;
            $resultStr = sprintf($temp,$fromUsername,$toUsername,$contentStr);
            echo $resultStr; exit;
        }
    }

    public static function getNoMsg()
    {
        $wordModel = MsgModel::where('genre',1)->orderBy('id','asc')->first();
        if ($wordModel && $wordModel->link) {
            $wordStr = implode('，',json_decode($wordModel->link,true));
        } else {
            $wordStr = '暂无关键字';
        }
        $noMsgTpl = "输入暂无匹配信息！\n以下是友情提示哦：\n现有关键字回复为\n";
        $noMsgTpl .= $wordStr."\n";
        $noMsgTpl .= "输入可回复对应内容\n...";
        return $noMsgTpl;
    }
}
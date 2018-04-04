<?php
namespace JiugeTo\WeiXinLaravel5\Controllers\Admin;

use Illuminate\Http\Request;

class MsgController extends BaseController
{
    /**
     * 微信消息
     */

    protected $view = 'admin.weixin.msg.'; //当前视图
    protected $url = 'weixin/msg'; //当前路由

    public function __construct()
    {
        parent::__construct();
        $this->crumbs = $this->getCrumbs($this->url);
        view()->share('crumbs',$this->crumbs);
        $this->prefix_url = ADMIN_DOMAIN.'/'.$this->url;
        view()->share('prefix_url',$this->prefix_url);
        view()->share('jiugeForm',new JiugeForm($this->prefix_url));
    }

    public function index()
    {
        $datas = MsgModel::where('genre',2)
            ->where('del',0)
            ->orderBy('id','desc')
            ->paginate($this->limit);
        $datas->limit = $this->limit;
        return view($this->view.'index',array(
            'datas' => $datas,
        ));
    }

    public function create()
    {
        return view($this->formView.'create',array(
            'sels' => $this->formElementArr,
            'options' => array(),
        ));
    }

    public function store(Request $request)
    {
        $data = $this->getData($request);
        $data['created_at'] = time();
        MsgModel::create($data);
        $this->setKeyWords();
        return redirect($this->prefix_url);
    }

    public function edit($id)
    {
        return view($this->formView.'edit',array(
            'sels' => $this->formElementArr,
            'options' => array(),
            'data' => $this->getModelById($id),
        ));
    }

    public function update(Request $request,$id)
    {
        $model = $this->getModelById($id);
        $data = $this->getData($request);
        $data['updated_at'] = time();
//        //判断图片
//        if ($data['thumb'] && $model['thumb'] && file_exists(ltrim($model['thumb']))) {
//            $oldThumb = $model['thumb'];
//        }
        MsgModel::where('id',$id)->update($data);
//        if (isset($oldThumb)) { unlink(ltrim($oldThumb)); }
        $this->setKeyWords();
        return redirect($this->prefix_url);
    }

    public function show($id)
    {
        return view($this->formView.'show',array(
            'fieldArr' => $this->formShowArr,
            'data' => $this->getModelById($id),
        ));
    }

    /**
     * 收集、验证
     */
    public function getData(Request $request)
    {
        if (!$request->keyword || !$request->name || !$request->detail || !$request->link) {
            echo "<script>alert('参数信息不全！');history.go(-1);</script>";exit;
        }
        return array(
            'keyword' => $request->keyword,
            'name' => $request->name,
            'genre' => 2,
            'detail' => $request->detail,
            'thumb' => isset($thumb) ? $thumb : '',
            'link' => $request->link,
        );
    }

    /**
     * 获取所有关键字，设置关注回复
     */
    public function setKeyWords()
    {
        $models = MsgModel::where('genre',2)->where('del',0)->get();
        $wordArr = array();
        foreach ($models as $model) {
            $wordArr[$model->id] = $model->keyword;
        }
        $wordStr = $wordArr ? json_encode($wordArr) : '';
        MsgModel::where('genre',1)->update(array('link'=>$wordStr));
    }

    /**
     * 通过ID获取Model
     */
    public function getModelById($id)
    {
        $model = MsgModel::find($id);
        if (!$model) {
            echo "<script>alert('记录不存在！');history.go(-1);</script>";exit;
        }
        return array(
            'id' => $model->id,
            'keyword' => $model->keyword,
            'name' => $model->name,
            'detail' => $model->detail,
            'thumb' => $model->thumb,
            'link' => $model->link,
        );
    }

    /**
     * 关注回复信息
     */
    public function getFollow()
    {
        $model = MsgModel::where('genre',1)->first();
        $wordModle = MsgModel::where('genre',1)->first();
        $words = $wordModle->link ? json_decode($wordModle->link) : array();
        return view($this->view.'follow',array(
            'data' => $model,
            'words' => $words,
        ));
    }

    /**
     * 设置关注回复信息
     */
    public function setFollow(Request $request)
    {
        if (!$request->name || !$request->detail) {
            echo "<script>alert('信息不完整！');history.go(-1);</script>";exit;
        }
        MsgModel::where('genre',1)
            ->update(array(
                'name' => $request->name,
                'detail' => $request->detail,
                'updated_at' => time(),
            ));
        return redirect($this->prefix_url.'/follow');
    }

//    /**
//     * 用户关注列表
//     */
//    public function getSubscribes()
//    {
//        dd('关注列表，开发中...！');
//        return view($this->view.'subscribe');
//    }
//
//    /**
//     * 用户消息请求记录
//     */
//    public function getMsgRecodes()
//    {
//        dd('消息记录，开发中...！');
//        return view($this->view.'subscribe');
//    }

    /**
     * 获取 Model
     */
    public function getModel()
    {
        return new MsgModel();
    }
}
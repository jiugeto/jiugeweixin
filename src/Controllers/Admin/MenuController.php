<?php
namespace App\Http\Controllers\Admin\WeiXin;

use App\Models\WeiXin\MenuModel;
use App\Utilities\JiugeForm;
use Illuminate\Http\Request;

class MenuController extends BaseController
{
    /**
     * 微信菜单
     */

    protected $view = 'admin.weixin.menu.'; //当前视图
    protected $url = 'weixin/menu'; //当前路由
    protected $formElementArr = [//表单元素
        //array(表单选项,中文名称,name字段名称,表单提示,js验证规则)
        array(1,'菜单名称','name','菜单名称，2-20字符',1),
        array(5,'事件类型','genre','事件类型',6),
        array(5,'父级菜单','pid','类型',6),
        array(1,'跳转地址','link','跳转网址',7),
    ];

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
//        dd(\Hash::make('jiuge_123'));
        $datas = MenuModel::all();
        return view($this->view.'index',array(
            'datas' => $datas,
        ));
    }

    public function create()
    {
        if (MenuModel::count()==18) {
            echo "<script>alert('微信规则：菜单数量已达上限，请编辑现有菜单！');history.go(-1);</script>";exit;
        }
        return view($this->formView.'create',array(
            'sels' => $this->formElementArr,
            'options' => array(
                'genre' => $this->getModel()->getGenres(),
                'is_use' => $this->getModel()->getUses(),
                'pid' => $this->getModel()->getParents(),
            ),
        ));
    }

    public function store(Request $request)
    {
        if (MenuModel::where('pid',$request->pid)->count() == 5) {
            echo "<script>alert('微信规则，该级子菜单已达5个上限，不能再增加！');history.go(-1);</script>";exit;
        }
        $data = $this->getData($request);
        $data['created_at'] = time();
        MenuModel::create($data);
        return redirect($this->prefix_url);
    }

    public function edit($id)
    {
        return view($this->formView.'edit',array(
            'sels' => $this->formElementArr,
            'options' => array(
                'genre' => $this->getModel()->getGenres(),
                'is_use' => $this->getModel()->getUses(),
                'pid' => $this->getModel()->getParents(),
            ),
            'data' => $this->getModelById($id),
        ));
    }

    public function update(Request $request,$id)
    {
        $olds = MenuModel::where('pid',$request->pid)->get();
        foreach ($olds as $old) {
            $oldIds[] = $old->id;
        }
        if (count($olds)==5 && !in_array($id,$oldIds)) {
            echo "<script>alert('微信规则，该级子菜单已达5个上限，不能再增加！');history.go(-1);</script>";exit;
        }
        $model = $this->getModelById($id);
        $data = $this->getData($request);
        $data['updated_at'] = time();
        MenuModel::where('id',$id)->update($data);
        return redirect($this->prefix_url);
    }

    /**
     * 收集、验证
     */
    public function getData(Request $request)
    {
        if (!$request->name || !$request->pid || !$request->link ||
            !array_key_exists($request->genre,$this->getModel()->getGenres())) {
            echo "<script>alert('参数错误！');history.go(-1);</script>";exit;
        }
        $link = $request->link;
        if (mb_substr($link,0,4)!='http') {
            $link = 'http'.$link;
        }
        return array(
            'name' => $request->name,
            'pid' => $request->pid,
            'genre' => $request->genre,
            'link' => $link,
        );
    }

    /**
     * 通过ID获取Modle
     */
    public function getModelById($id)
    {
        $model = MenuModel::find($id);
        if (!$model) {
            echo "<script>alert('记录不存在！');history.go(-1);</script>";exit;
        }
        $link = mb_substr($model->link,0,4)!='http' ? 'http'.$model->link : $model->link;
        return array(
            'id' => $model->id,
            'name' => $model->name,
            'genre' => $model->genre,
            'genreNmae' => $model->getGenreName(),
            'link' => $link,
            'is_use' => $model->is_use,
            'useName' => $model->getUseName(),
            'pid' => $model->pid,
            'parentName' => $model->getParentName(),
            'createTime' => $model->createTime(),
        );
    }

    /**
     * 删除菜单
     */
    public function setDel($id)
    {
        MenuModel::where('id',$id)->update(array('del'=>1));
        return redirect($this->prefix_url);
    }

    /**
     * 还原菜单
     */
    public function setRedo($id)
    {
        MenuModel::where('id',$id)->update(array('del'=>0));
        return redirect($this->prefix_url);
    }

    /**
     * 更新微信菜单
     */
    public function setWeiXinMenu($use)
    {
        if (env('APP_ENV')=='local') {
            echo "<script>alert('你在本地内网开发，没有微信服务器权限！');history.go(-1);</script>";exit;
        }
        if (!in_array($use,[0,1])) {
            echo "<script>alert('参数错误！');history.go(-1);</script>";exit;
        }
        if ($use==1) {
            //开始使用
            $models = MenuModel::where('pid',0)
                ->where('del',0)
                ->get();
            if (count($models)<3) {
                echo "<script>alert('菜单不足！');history.go(-1);</script>";exit;
            }
            $menuArr = array();
            foreach ($models as $key=>$model) {
                $menuArr[$key]['name'] = $this->cnToEn($model->name);
//                $menuArr[$key]['name'] = $model->name;
                $subs = (is_array($model->getSubs())&&$model->getSubs()) ?
                    $model->getSubs() : array();
                if ($subs) {
                    //有子菜单
                    foreach ($subs as $k=>$sub) {
                        $menuArr[$key]['sub_button'][$k]['name'] = $this->cnToEn($sub['name']);
//                        $menuArr[$key]['sub_button'][$k]['name'] = $sub['name'];
//                        if (in_array($sub['genre'],[1,3,5])) {
//                            $menuArr[$key]['sub_button'][$k]['type'] = 'click';
//                            $menuArr[$key]['sub_button'][$k]['key'] = trim($sub['key']);
//                        } else {
                            $menuArr[$key]['sub_button'][$k]['type'] = 'view';
                            $menuArr[$key]['sub_button'][$k]['url'] = trim($sub['url']);
//                        }
                    }
                } else {
                    //无子菜单
                    if ($model->genre==1) {
                        $menuArr[$key]['type'] = 'click';
                        $menuArr[$key]['key'] = trim($model->link);
                    } else {
                        $menuArr[$key]['type'] = 'view';
                        $menuArr[$key]['url'] = trim($model->link);
                    }
                }
            }
            $menuArr = array('button'=>$menuArr);
            $curl = $this->setWeiXinStart($this->enToCn(json_encode($menuArr)));
            if (!$curl || $curl['errcode']) {
                echo "<script>alert('错误代号".$curl['errcode']."，操作失败！');history.go(-1);</script>";exit;
            }
            //成功，更新字段is_use
            if (!$curl['errcode']) { $use = 1; }
        } else {
            //暂停使用
            $curl = $this->setWeiXinStop();
            if (!$curl) {
                echo "<script>alert('操作失败');history.go(-1);</script>";exit;
            }
            //成功，更新字段is_use
            if (!$curl['errcode']) { $use = 0; }
        }
        MenuModel::where('del',0)
            ->update(['is_use'=> $use]);
        return redirect($this->prefix_url);
    }

    /**
     * 设置微信菜单使用
     */
    public function setWeiXinStart($menuArr)
    {
        //POST（请使用https协议） https://api.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN
        $url = $this->wxUrl.'menu/create?access_token='.$this->getToken();
        return $this->getCurl($url,'post','json',$menuArr);
    }

    /**
     * 设置微信菜单暂停
     */
    public function setWeiXinStop()
    {
        //https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=ACCESS_TOKEN
        $url = $this->wxUrl.'menu/delete?access_token='.$this->getToken();
        return $this->getCurl($url,'get');
    }

    /**
     * 获取 Model
     */
    public function getModel()
    {
        return new MenuModel();
    }
}
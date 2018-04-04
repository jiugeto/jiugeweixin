<?php
namespace JiugeTo\WeiXinLaravel5\Models;

class MenuModel extends BaseModel
{
    /**
     * 微信菜单
     */

    protected $table = 'wx_menu';
    protected $fillable = [
        'id','name','genre','link','pid','is_use',
        'del','created_at','updated_at',
    ];

    /**
     * 菜单类型：1左边-click，2左边-view，3中间-click，4中间-view，5右边-click，6右边-view，
     */
    public function getGenres()
    {
        return array(
            1=>'左边-click点击事件','左边-view跳转页面',
            '中间-click点击事件','中间-view跳转页面',
            '右边-click点击事件','右边-view跳转页面',
        );
    }

    /**
     * 是否使用：0未使用，1使用中
     */
    public function getUses()
    {
        return array(
            '未使用','使用中',
        );
    }

    public function getGenreName()
    {
        $genres = $this->getGenres();
        return array_key_exists($this->genre,$genres) ? $genres[$this->genre] : '';
    }

    public function getUseName()
    {
        $uses = $this->getUses();
        return array_key_exists($this->is_use,$uses) ? $uses[$this->is_use] : '';
    }

    /**
     * 获取父级
     */
    public function getParents()
    {
        $query = MenuModel::where('pid',0)
            ->where('del',0);
        $menuArr = array();
        foreach ($query->get() as $model) {
            $menuArr[$model->id] = $model->name;
        }
        return $menuArr;
    }

    /**
     * 获取子菜单
     */
    public function getSubs()
    {
        $models = MenuModel::where('pid',$this->id)
            ->where('del',0)
            ->get();
        if (!count($models)) { return '一级菜单'; }
        $menuArr = array();
        foreach ($models as $k=>$model) {
            $menuArr[$k]['id'] = $model->id;
            $menuArr[$k]['name'] = $model->name;
            $menuArr[$k]['type'] = $this->getGenreName();
            $menuArr[$k]['link'] = $this->link;
            $menuArr[$k]['genre'] = $model->genre;
//            if (in_array($model->genre,[1,3,5])) {
//                $menuArr[$k]['key'] = $model->link;
//            } else {
                $menuArr[$k]['url'] = $model->link;
//            }
        }
        return $menuArr;
    }

    /**
     * 获取父级菜单
     */
    public function getParentName()
    {
        $model = MenuModel::where('id',$this->pid)
            ->where('del',0)
            ->first();
        return $model ? $model->name : '一级菜单';
    }
}
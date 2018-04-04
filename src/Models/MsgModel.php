<?php
namespace JiugeTo\WeiXinLaravel5\Models;

class MsgModel extends BaseModel
{
    /**
     * 消息回复
     */

    protected $table = 'wx_msg';
    protected $fillable = [
        'id','keyword','name','genre','detail','thumb','link',
        'del','created_at','updated_at',
    ];

    /**
     * 接收类型：1关注，2关键词，
     */
    public function getGenres()
    {
        return array(
            1=>'关注','关键词',
        );
    }

    public function getGenreName()
    {
        $genres = $this->getGenres();
        return array_key_exists($this->genre,$genres) ? $genres[$this->genre] : '';
    }
}
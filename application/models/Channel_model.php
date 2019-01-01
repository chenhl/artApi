<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Search
 *
 * @author Administrator
 */
class Channel_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 列表
     * @param type $condition
     * @return type
     */
    public function getList($condition = array()) {

        $return = array(
            array('id' => 0, 'name' => '推荐', 'code' => 'all'),
            array('id' => 1, 'name' => '热点', 'code' => 'news'),
            array('id' => 2, 'name' => '人物', 'code' => 'artist'),
            array('id' => 3, 'name' => '展览', 'code' => 'exhibit'),
            array('id' => 4, 'name' => '画廊', 'code' => 'gallery'),
//            array('id' => 5, 'name' => '院校', 'code' => 'edu'),
        );
        return $return;
    }

    public function cateChannel() {
        
        $cate_channel = array(
          6=>'news',
          9=>'artist',
          10=>'exhibit',
          11=>'gallery',  
        );
        return $cate_channel;
    }
    

}

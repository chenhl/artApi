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
class Collection_model extends Base_model {

    private $table='art_collection';
    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 添加收藏
     * @param type $data
     * @return type
     */
    public function add($data) {
        return $this->pdo_insert($data, $this->table);
    }
    /**
     * 收藏列表
     * @param type $condition
     * @return type
     */
    public function getList($condition = array()) {
            
    }

    

}

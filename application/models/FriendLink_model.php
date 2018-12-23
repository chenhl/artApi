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
class FriendLink_model extends Base_model {

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

        $param = array();
        $where = ' l.siteid=1 and l.passed=1';
        $param[':siteid'] = 1;
        $fields_n = 'l.linkid,l.linktype,l.name,l.url,l.logo';
        $query = 'select ' . $fields_n
                . ' from v9_link as l'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetchAll(PDO::FETCH_ASSOC);
        
        return $return;
    }

    

}

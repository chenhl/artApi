<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 内部接口
 *
 * @author Administrator
 */
class Member_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 注册
     * @param type $data
     * @return type
     */
    public function doReg($data) {

        $where = " m.islock=0 ";
        $param = array();
        if (is_numeric($data['login_name'])) {
            $where .= " and m.mobile=:login_name";
        } else {
            $where .= " and m.email=:login_name";
        }
        $param[':login_name'] = $data['login_name'];

        $query = 'select * from v9_member as m where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetch(PDO::FETCH_ASSOC);
        if (empty($return)) {
            return array();
        }
        if ($return['password'] != Util::setPassWord($data['password'], $return['encrypt'])) {
            return array();
        }
        return $return;
    }

    /**
     * 登录
     * @param type $data
     * @return type
     */
    public function doLogin($data) {

        $where = " m.islock=0 ";
        $param = array();
        if (is_numeric($data['login_name'])) {
            $where .= " and m.mobile=:login_name";
        } else {
            $where .= " and m.email=:login_name";
        }
        $param[':login_name'] = $data['login_name'];

        $query = 'select * from v9_member as m where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetch(PDO::FETCH_ASSOC);
        if (empty($return)) {
            return FALSE;
        }
        if ($return['password'] != Util::setPassWord($data['password'], $return['encrypt'])) {
            return FALSE;
        }
        return $return;
    }

}

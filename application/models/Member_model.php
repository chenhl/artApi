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

    private $follow_max = 400;
    private $collect_max = 1000;

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 我的关注列表
     * @param type $condition
     * @param type $page
     * @param type $pageSize
     */
    public function getFollowList($condition, $page, $pageSize) {
        $param = array();
        $where = ' f.uid=:userid';
        $param[':userid'] = $condition['uid'];

        $return = array();
        //统计最多关注 400个;

        $query = 'select count(fuid) as total from art_follow as f '
                . 'where' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $count = $db->fetch(PDO::FETCH_ASSOC);
        if ($this->follow_max && $count['total'] > $this->follow_max) {
            $return['total'] = $this->follow_max;
        } else {
            $return['total'] = $count['total'];
        }

        //内容
        $start = ($page - 1) * $pageSize;
        if ($this->follow_max && $start > $this->follow_max) {
            $return['list'] = array();
        } else {
            $where .= ' m.islock=0 ';
            $limit = 'limit ' . $start . ',' . $pageSize;
            $fields = 'm.userid as uid,m.nickname as uname,m.userpic as upic';
            $query = 'select ' . $fields
                    . ' from art_follow as f'
                    . ' left join v9_member as m on m.userid=f.fuid'
                    . ' where ' . $where . $limit;
            $db = $this->db->conn_id->prepare($query);
            $db->execute($param);
            $return['list'] = $db->fetchAll(PDO::FETCH_ASSOC);
        }

        return $return;
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

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
     * 添加关注
     * @param type $data
     */
    public function addFollow($data) {

        $insert_data = array();
        $insert_data['uid'] = $data['uid'];
        $insert_data['fuid'] = $data['fuid'];
        $insert_data['create_time'] = Util::genHttpDateTime();
        return $this->pdo_insert($data, 'art_follow');
    }

    /**
     * 取消关注
     * @param type $data
     */
    public function cancelFollow($data) {
        $sql = 'delete from art_follow where uid = ' . intval($data['uid'])
                . ' and fuid=' . intval($data['fuid']);
        return $this->query($sql);
    }
############################登录相关
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

    /**
     * 登录
     * @param type $data
     * @return type
     */
    public function doAuth($data) {

        //判断用户是否存在
        $param = array();
        if (is_numeric($data['login_name'])) {
            $where = ' m.mobile=:login_name';
        } else {
            $where = ' m.email=:login_name';
        }
        $param[':login_name'] = $data['login_name'];

        $query = 'select * from v9_member as m where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $userInfo = $db->fetch(PDO::FETCH_ASSOC);

        if (!empty($userInfo)) {
            //存在，登录
            //一：禁止登录
            if (!empty($userInfo['islock'])) {
                return array(
                    'islock' => $userInfo['islock']
                );
            }
            return $userInfo;
        } else {
            //不存在
            //注册
            return $this->_insert_member($data);
        }
    }

    /**
     * 整理auth信息成member信息
     * @param type $param
     */
    private function _formatAuth($param) {

        return $param;
    }
    /**
     * 入库member
     * @param type $data
     * @return type
     */
    private function _insert_member($data) {
        $insert_data = $this->_formatAuth($data);
        return $this->pdo_insert($insert_data, 'v9_member');
    }

    public function getUserInfo($param){
        
    }
    
    
##########################登录相关    
}

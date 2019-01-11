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

    public function getFansList($condition, $page, $pageSize) {
        return $this->_getList($condition, $page, $pageSize, 'fans');
    }

    public function getFollowList($condition, $page, $pageSize) {
        return $this->_getList($condition, $page, $pageSize, 'follow');
    }

    /**
     * 我的关注列表
     * @param type $condition
     * @param type $page
     * @param type $pageSize
     */
    private function _getList($condition, $page, $pageSize, $type = 'follow') {
        $param = array();
        if ($type == 'follow') {
            $where = ' f.uid=:userid';
            $param[':userid'] = $condition['uid'];
            $join = ' left join v9_member as m on f.fuid = m.uid';
        } else {//type==fans
            $where = ' f.fuid=:userid';
            $param[':userid'] = $condition['uid'];
            $join = ' left join v9_member as m on f.uid = m.uid';
        }
        $return = array();
        //统计最多关注 400个;

        $query = 'select count(id) as total from art_follow as f '
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
//            $where .= ' m.islock=0 ';
            $limit = ' limit ' . $start . ',' . $pageSize;
            $fields = 'f.is_friend,m.uid as uid,m.nickname as uname,m.description,m.userpic as upic';
            $query = 'select ' . $fields
                    . ' from art_follow as f'
                    . $join
                    . ' where ' . $where . $limit;
            $db = $this->db->conn_id->prepare($query);
            $db->execute($param);
//            
            $list = $db->fetchAll(PDO::FETCH_ASSOC);
//            echo $query;
//            print_r($list);
            $return['list'] = array();
            if ($list) {
                if ($type == 'follow') {
                    foreach ($list as $row) {
                        if ($row['is_friend']) {//相互
                            $row['is_followed'] = TRUE;
                            $row['is_following'] = TRUE;
                            $row['relation_status'] = 2;
                        } else {//已关注
                            $row['is_followed'] = FALSE;
                            $row['is_following'] = TRUE;
                            $row['relation_status'] = 1;
                        }
                        $row['u_url'] = $this->author_url($row['uid']);
                        $row['upic'] = $this->imgurl($row['upic']);
                        $return['list'][] = $row;
                    }
                } else {
                    foreach ($list as $row) {
                        if ($row['is_friend']) {//相互
                            $row['is_followed'] = TRUE;
                            $row['is_following'] = TRUE;
                            $row['relation_status'] = 2;
                        } else {//关注
                            $row['is_followed'] = FALSE;
                            $row['is_following'] = FALSE;
                            $row['relation_status'] = 0;
                        }
                        $row['u_url'] = $this->author_url($row['uid']);
                        $row['upic'] = $this->imgurl($row['upic']);
                        $return['list'][] = $row;
                    }
                }
            }
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
        //查询uid是否被关注
        $fo = $this->getOneFollow($data['fuid'], $data['uid']);
        if ($fo) {
            $insert_data['is_friend'] = 1;
            try {
                $this->begin_transaction();
                $id = $this->pdo_insert($data, 'art_follow');
                //更新friend
                $update = array(
                    'is_friend' => 1,
                );
                $whe = array(
                    'id' => $fo['id'],
//                    'uid' => $insert_data['fuid'],
//                    'fuid' => $insert_data['uid'],
                );
                $this->pdo_update($whe, $update, 'art_follow');
                $this->begin_commit();
            } catch (Exception $exc) {
                $this->begin_back();
//                echo $exc->getTraceAsString();
                return FALSE;
            }
        } else {
            $id = $this->pdo_insert($data, 'art_follow');
        }
        return $id;
    }

    /**
     * 取消关注
     * @param type $data
     */
    public function cancelFollow($data) {
        //查询uid是否被关注
        $fo = $this->getOneFollow($data['fuid'], $data['uid']);
        if ($fo) {
            try {
                $this->begin_transaction();
                //删除
                $sql = 'delete from art_follow where uid = ' . intval($data['uid'])
                        . ' and fuid=' . intval($data['fuid']);
                $res = $this->query($sql);
                //更新friend
                $update = array(
                    'is_friend' => 0,
                );
                $whe = array(
                    'id' => $fo['id'],
                );
                $this->pdo_update($whe, $update, 'art_follow');

                $this->begin_commit();
            } catch (Exception $exc) {
                $this->begin_back();
                return FALSE;
            }
        } else {
            $sql = 'delete from art_follow where uid = ' . intval($data['uid'])
                    . ' and fuid=' . intval($data['fuid']);
            $res = $this->query($sql);
        }

        return $res;
    }

    private function getOneFollow($uid, $fuid) {
        $param = array();
        $where = ' f.uid=:uid';
        $param[':uid'] = $uid;
        $where .= ' and f.fuid=:fuid';
        $param[':fuid'] = $fuid;

        $query = 'select id '
                . ' from art_follow as f'
                . ' where ' . $where . ' limit 1';
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        return $db->fetch(PDO::FETCH_ASSOC);
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
        $userInfo = $this->_getUserInfo($data);

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
        $return = array();
        $return['uid'] = Util::genUid(); //todo并发不是太大时可唯一
        $return['m_uid'] = md5($return['uid']);

        $return['email'] = $param['email'];

        return $return;
    }

    /**
     * 入库member
     * @param type $data
     * @return array 用户基本信息，可供登录用
     */
    private function _insert_member($data) {
        $insert_data = $this->_formatAuth($data);
        $userid = $this->pdo_insert($insert_data, 'v9_member');
        $insert_data['userid'] = $userid;
        return $insert_data;
    }

    private function _getUserInfo($data) {
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
        return $userInfo;
    }

    public function getUserInfo($m_uid) {

        $userInfo = $this->_getUserInfo(array('m_uid' => $m_uid));
        return $userInfo;
    }

##########################登录相关    
}

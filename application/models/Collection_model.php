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

    private $table = 'art_collection';
    private $collect_max = 1000;

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 是否收藏
     * @param type $data
     * @return type
     */
    public function isCollected($uid, $aid) {
        $param = array();
        $where = ' uid=:uid';
        $param[':uid'] = $uid;
        $where .= ' and aid=:aid';
        $param[':aid'] = $aid;

        $query = 'select id from ' . $this->table
                . 'where ' . $where . ' limit 1';
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $data = $db->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 添加收藏
     * @param type $data
     * @return type
     */
    public function add($data) {
        $insert_data = array();
        $insert_data['uid'] = $data['uid'];
        $insert_data['aid'] = $data['aid'];
        $insert_data['create_time'] = Util::genHttpDateTime();

        return $this->pdo_insert($insert_data, $this->table);
    }

    /**
     * 取消收藏
     * @param type $data
     */
    public function cancel($data) {
        $sql = 'delete from ' . $this->table . ' where uid = ' . intval($data['uid'])
                . ' and aid=' . intval($data['aid']);
        return $this->query($sql);
    }

    /**
     * 收藏列表
     * @param type $condition
     * @return type
     */
    public function getList($condition, $page, $pageSize) {
        $param = array();
        $where = ' c.uid=:userid';
//        $param[':userid'] = $condition['uid'];
        $param[':userid'] = 1;//test
        $return = array();
        //统计最多关注 1000个;
        $query = 'select count(aid) as total from ' . $this->table . ' as c '
                . 'where' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $count = $db->fetch(PDO::FETCH_ASSOC);
        if ($this->collect_max && $count['total'] > $this->collect_max) {
            $return['total'] = $this->collect_max;
        } else {
            $return['total'] = $count['total'];
        }

        //内容
        $start = ($page - 1) * $pageSize;
        if ($this->collect_max && $start > $this->collect_max) {
            $return['list'] = array();
        } else {
            $limit = 'limit ' . $start . ',' . $pageSize;
            $query = 'select aid '
                    . ' from ' . $this->table . ' as c '
                    . ' where ' . $where . $limit;
            $db = $this->db->conn_id->prepare($query);
            $db->execute($param);
            $_data = $db->fetchAll(PDO::FETCH_ASSOC);
            if ($_data) {
                $aids = array_column($_data, 'aid');
                //查询文章数据
                $this->load->model(array('search_model'));
                $condition_search = array();
                $condition_search['aids'] = $aids;
                $res = $this->search_model->getListFromSolor($condition, $page, $pageSize);
                $return['list'] = $res['docs'];
            }
        }

        return $return;
    }

}

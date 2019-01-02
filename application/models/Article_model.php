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
class Article_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 文章列表 cron etl 使用
     * @param type $condition
     * @param type $page
     * @param type $page_size
     * @return type
     */
    public function etl_article($condition = array(), $page = 0, $page_size = 0) {
//        $this->_etl_article($condition, $page, $page_size, 'list');
        $sql = $this->_etl_article_sql($condition, $page, $page_size, 'list');

        $this->db->conn_id->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        //perpare里的游标属性不是必须的
        $db = $this->db->conn_id->prepare($sql['sql'], array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $db->execute($sql['param']);
        while ($row = $db->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    /**
     * 文章统计 cron etl 使用
     * @param type $condition
     * @return type
     */
    public function etl_article_count($condition = array()) {
//        $data = $this->_etl_article($condition, 0, 0, 'count');
        $sql = $this->_etl_article_sql($condition, 1, 1, 'count');
        $db = $this->db->conn_id->prepare($sql['sql']);
        $db->execute($sql['param']);
        $data = $db->fetchAll(PDO::FETCH_ASSOC);
//        print_r($data);
        return $data[0]['total'];
    }

    /**
     * 文章列表
     * 要么yeild 要么return 不能同存
     * @param type $condition
     * @param type $page
     * @param type $page_size
     * @param type $type
     * @return type
     */
    private function _etl_article($condition, $page, $page_size, $type) {
        $where = ' n.status=99 ';
        $param = array();
        if (!empty($condition['date_time'])) {
            $where .= ' and n.update_time>=:date_time';
            $param[':date_time'] = $condition['date_time'];
        }

        if (!empty($condition['id'])) {
            $where .= ' and n.id=:id';
            $param[':id'] = $condition['id'];
        }

        if ($page && $page_size) {
            $limit = ' limit ' . ($page - 1) * $page_size . ',' . $page_size;
        } else {
            $limit = '';
        }
//        $fields_n = 'n.id,n.aid,n.uid,n.uname,n.userpic,n.collect_num,n.like_num,n.comment_num,n.status,n.catid,n.title,n.thumb,n.thumbs,n.keywords,n.tags,n.description,n.create_time,n.update_time,';
        if ($type == 'list') {
            $fields = 'n.*,'
                    . 'd.content,d.content_search,'
                    . 'm.nickname,m.username,m.userid,m.userpic as m_userpic ';
        } else {
            $fields = 'count(n.id) as total ';
        }

        $query = 'select ' . $fields
                . ' from v9_news as n'
                . ' left join v9_news_data as d on n.id=d.id'
                . ' left join v9_member as m on n.uname=m.nickname'
                . ' where ' . $where . $limit;
//        $start_time = microtime(TRUE);
//            echo $query;exit;
        //游标+yield生成器
        $this->db->conn_id->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        //perpare里的游标属性不是必须的
        $db = $this->db->conn_id->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $db->execute($param);
        while ($row = $db->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }

//            $db = $this->db->conn_id->prepare($query);
//            $db->execute($param);
//            $return = $db->fetchAll(PDO::FETCH_ASSOC);
//            return $return;
        //test
//        $newLine = PHP_SAPI == 'cli' ? "\n" : '<br />';
//        $i = 0;
//        foreach ($this->cursor($db) as $row) {
////            var_dump($row);
////            echo $row['id'] . $newLine;
//            $i++;
//        }
//        $end_time = microtime(TRUE);
//        echo "消耗内存：" . (memory_get_usage() / 1024 / 1024) . "M" . $newLine;
//        echo "时间：" . ($end_time - $start_time) . $newLine;
//        echo "处理数据行数：" . $i . $newLine;
//        echo "success";
        //一次全取
//        $db = $this->db->conn_id->prepare($query);
//        $db->execute($param);
//        $return = $db->fetchAll(PDO::FETCH_ASSOC);
//        $end_time = microtime(TRUE);
//        echo "消耗内存：" . (memory_get_usage() / 1024 / 1024) . "M" . $newLine;
//        echo "时间：".($end_time-$start_time) . $newLine;
//        echo "处理数据行数：" . count($return) . $newLine;
//        echo "success";
//        $return = $db->fetchAll(PDO::FETCH_ASSOC);
//        return $return;
    }

    private function _etl_article_sql($condition, $page, $page_size, $type) {
        $where = ' n.status=99 ';
        $param = array();
        if (!empty($condition['date_time'])) {
            $where .= ' and n.update_time>=:date_time';
            $param[':date_time'] = $condition['date_time'];
        }

        if (!empty($condition['id'])) {
            $where .= ' and n.id=:id';
            $param[':id'] = $condition['id'];
        }

        if ($page && $page_size) {
            $limit = ' limit ' . ($page - 1) * $page_size . ',' . $page_size;
        } else {
            $limit = '';
        }
//        $fields_n = 'n.id,n.aid,n.uid,n.uname,n.userpic,n.collect_num,n.like_num,n.comment_num,n.status,n.catid,n.title,n.thumb,n.thumbs,n.keywords,n.tags,n.description,n.create_time,n.update_time,';
        if ($type == 'list') {
            $fields = 'n.*,'
                    . 'd.content,d.content_search,'
                    . 'm.nickname,m.username,m.userid,m.userpic as m_userpic ';
        } else {
            $fields = 'count(n.id) as total ';
        }

        $query = 'select ' . $fields
                . ' from v9_news as n'
                . ' left join v9_news_data as d on n.id=d.id'
                . ' left join v9_member as m on n.uname=m.nickname'
                . ' where ' . $where . $limit;
        return array('sql' => $query, 'param' => $param);
    }

    private function cursor($sth) {
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function getDetail($condition) {
        $where = ' n.status=99 ';
        $param = array();
        if (!empty($condition['aid'])) {
            $where .= ' and n.aid=:aid';
            $param[':aid'] = $condition['aid'];
        }
        $query = 'select n.id,n.aid,n.uid,n.uname,n.userpic,n.collect_num,n.like_num,n.comment_num,n.status,n.catid,n.title,n.thumb,n.thumbs,n.keywords,n.tags,n.description,n.create_time,n.update_time,'
                . 'd.content,d.content_search,'
                . 'm.nickname,m.username,m.userid,m.userpic as m_userpic '
                . ' from v9_news as n'
                . ' left join v9_news_data as d on n.id=d.id'
                . ' left join v9_member as m on n.uname=m.nickname'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetch(PDO::FETCH_ASSOC);
        if (!empty($return['tags'])) {
            $return['tags'] = json_decode($return['tags'], TRUE);
        }
        $return['upic'] = $this->imgurl($return['userpic']);
        $return['u_url'] = $this->author_url($return['uid']);
        return $return;
//        return $this->mockDetail();
    }

    public function aboutCate($condition) {
        $where = ' c.type=1 and c.parentid!=0';
        $param = array();
        if (!empty($condition['catid'])) {
            $where .= ' and c.catid=:catid';
            $param[':catid'] = $condition['catid'];
        }

        $query = 'select catid,catname,catdir '
                . ' from v9_category as c'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($return)) {
            foreach ($return as $key => $row) {
                $return[$key]['url'] = '/' . $row['catdir'];
            }
        }
        return $return;
    }

    public function aboutArticle($condition) {

        $param = array();
        if (!empty($condition['catid'])) {
            $where = ' p.catid=:catid';
            $param[':catid'] = $condition['catid'];
        }

        $query = 'select catid,title,content,keywords '
                . ' from v9_page as p'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetch(PDO::FETCH_ASSOC);

        return $return;
    }

}

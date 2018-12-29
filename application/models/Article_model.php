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
        $data = $this->_etl_article($condition, $page, $page_size, 'list');
        return $data;
    }

    /**
     * 文章统计 cron etl 使用
     * @param type $condition
     * @return type
     */
    public function etl_article_count($condition = array()) {
        $data = $this->_etl_article($condition);
        return $data[0]['total'];
    }

    /**
     * 文章列表sql
     * @param type $condition
     * @param type $page
     * @param type $page_size
     * @param type $type
     * @return type
     */
    public function _etl_article($condition = array(), $page = 0, $page_size = 0, $type = 'list') {

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
                    . 'm.nickname,m.username,m.userid,m.userpic as m_userpic '
            ;
        } else {
            $fields = 'count(n.id) as total ';
        }
        

        $query = 'select ' . $fields
                . ' from v9_news as n'
                . ' left join v9_news_data as d on n.id=d.id'
                . ' left join v9_member as m on n.uname=m.nickname'
                . ' where ' . $where . $limit;
//        $start_time = microtime(TRUE);
        //游标+yield生成器
        $this->db->conn_id->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        //perpare里的游标属性不是必须的
        $db = $this->db->conn_id->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $db->execute($param);
        while ($row = $db->fetch(PDO::FETCH_ASSOC)) {
            yield $row;
        }
        
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

    private function mockDetail() {

        $data = '{
  "aid": 6093075,
  "ud": 7669697,
  "uname": "authorName",
  "upic": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "title": "ab",
  "mobile_title": "ab！",
  "tags": [
    {
      "id": 19031,
      "name": "tag"
    }
  ],
  "cate_id": "111",
  "cate_name": "news",
  "content": "aff",
  "create_time": "2018-10-24 10:11:13"
}';
        $return = json_decode($data, TRUE);
        return $return;
//        return array(json_decode($data, TRUE));
    }

}

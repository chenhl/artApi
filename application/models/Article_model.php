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
     * 文章列表
     * @param type $condition
     * @return type
     */
    public function etl_article($condition = array()) {

        $where = ' n.status=99 ';
        $param = array();
        if (!empty($condition['date_time'])) {
            $where .= ' and n.update_time>=:date_time';
            $param[':date_time'] = $condition['date_time'];
        }
//        $fields_n = 'n.id,n.aid,n.uid,n.uname,n.userpic,n.collect_num,n.like_num,n.comment_num,n.status,n.catid,n.title,n.thumb,n.thumbs,n.keywords,n.tags,n.description,n.create_time,n.update_time,';
        $fields_n = 'n.*,';
        $query = 'select ' . $fields_n
                . 'd.content,d.content_search,'
                . 'm.nickname,m.username,m.userid,m.userpic as m_userpic '
                . ' from v9_news as n'
                . ' left join v9_news_data as d on n.id=d.id'
                . ' left join v9_member as m on n.uname=m.nickname'
                . ' where ' . $where;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetchAll(PDO::FETCH_ASSOC);
        return $return;
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
        return $return;
//        return $this->mockDetail();
    }

    private function mockDetail() {

        $data = '{
  "aid": 6093075,
  "ud": 7669697,
  "uname": "authorName",
  "upic": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "title": "刚刚！云南省级机构改革首批6部门挂牌成立！",
  "mobile_title": "刚刚！云南省级机构改革首批6部门挂牌成立！",
  "tags": [
    {
      "id": 19031,
      "name": "艺术家"
    }
  ],
  "cate_id": "111",
  "cate_name": "news",
  "content": "刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！刚刚！云南省级机构改革首批6部门挂牌成立！",
  "create_time": "2018-10-24 10:11:13"
}';
        $return = json_decode($data, TRUE);
        return $return;
//        return array(json_decode($data, TRUE));
    }

}

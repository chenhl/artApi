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
        $query = 'select n.id,n.aid,n.uid,n.uname,n.userpic,n.collect_num,n.like_num,n.comment_num,n.status,n.catid,n.title,n.thumb,n.thumbs,n.keywords,n.tags,n.description,n.create_time,n.update_time,'
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

    

}

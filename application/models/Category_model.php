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
class Category_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 分类列表
     * @param type $condition
     * @return type
     */
    public function getList($condition = array()) {

        $where = " type=0 ";
        $param = array();
        if (!empty($condition['catid'])) {
            $where .= " and c.catid=:catid";
            $param[':catid'] = $condition['catid'];
        }
        $order = ' order by listorder asc';
        $query = 'select catid,catname,listorder,modelid,letter,catdir from v9_category where ' . $where . $order;
        $db = $this->db->conn_id->prepare($query);
        $db->execute($param);
        $return = $db->fetchAll(PDO::FETCH_ASSOC);
        return $return;
    }

    private function defData() {

        $data = '{
  "id": 8480410,
  "authorId": 22447533,
  "authorName": "test",
  "authorPic": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "focus": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "picUrl": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "images": [
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/1981cb05d052486a9c21c44a4a0af049.jpeg",
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/5c811c4ee52e46618b1027a49bfdc979.jpeg",
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/4517eab8e5f04f119a67683b6d2788bf.jpeg"
  ],
  "title": "test",
  "mobileTitle": "test",
  "tags": [
    {
      "id": 40810824,
      "name": "书法"
    }
  ],
  "outerLink": "",
  "categoryId": 76,
  "categoryName": "",
  "createTime": "Y-m-d H:i:s"
}';
        return array(json_decode($data, TRUE));
    }

    private function _connect() {

        $this->solr_url;
    }

}

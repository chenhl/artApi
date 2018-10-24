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
class Channel_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conDB();
    }

    /**
     * 列表
     * @param type $condition
     * @return type
     */
    public function getList($condition = array()) {

        $return = array(
            array('id' => 0, 'name' => '推荐', 'code' => 'all'),
            array('id' => 1, 'name' => '热点', 'code' => 'news'),
            array('id' => 2, 'name' => '人物', 'code' => 'artist'),
            array('id' => 3, 'name' => '展览', 'code' => 'exhibit'),
            array('id' => 4, 'name' => '画廊', 'code' => 'gallery'),
            array('id' => 5, 'name' => '院校', 'code' => 'edu'),
        );
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

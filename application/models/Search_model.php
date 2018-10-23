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
class Search_model extends Base_model {

    public function __construct() {
        parent::__construct();
        $this->conSOLR();
    }

    /**
     * curl查询solr
     * 
     * @param type $condition
     * @param type $page
     * @param type $pagesize
     * @param type $facet_return
     * @return type
     */
    public function getListFromSolor($condition, $page = 1, $pagesize = 20, $facet_return = false) {
        $param = array();
        //默认条件
        $param['fq'] = array();
        $param['fq'][] = 'fq=is_deleted:0';
        //返回字段
        $param['fl'] = '&fl=*';
        //默认查询字段
        $param['df'] = '&df=text'; //solr7.2中 schema已不在支持参数 defaultSearchField
        ##########查询条件
        //关键字
        if (!empty($condition['q'])) {
            $param['q'] = 'q=' . urlencode($condition['q']);
            if ($condition['q.op']) {//solr7.2中 schema已不在支持参数defaultOperator
                $param['q'] .= '&q.op=' . $condition['q.op']; // AND OR
            } else {
                $param['q'] .= '&q.op=AND'; // AND OR
            }
            //排序
            $param['sort'] = '';
        } else {
            $param['q'] = 'q=*:*';
            //排序
            $param['sort'] = '&sort=articleId+desc';
        }
        //cate
        if (!empty($condition['categoryId'])) {
            $param['fq'][] = 'fq=categoryId:' . $condition['categoryId'];
        }
        //author
        if (!empty($condition['authorId'])) {
            $param['fq'][] = 'fq=authorId:' . $condition['authorId'];
        }

        //返回facet
        if ($facet_return) {
            $facets = array();
            if ($facets) {
                $param['facet'] = '&facet=on&facet.mincount=1';
                foreach ($facets as $value) {
                    $param['facet'] .= '&facet.field=' . $value;
                }
            }
        }
        //分页
        if ($pagesize > 0) {
            $param['start'] = '&start=' . intval(($page - 1) * $pagesize);
            $param['rows'] = '&rows=' . $pagesize;
        } else {
            $param['start'] = '&start=0';
            $param['rows'] = '&rows=0'; //返回0，有facet
        }


//        $url = $this->solr_url.'?';
        $uri = '';
        $uri .= $param['q'] . $param['df'] . $param['fl'];
        if ($param['fq']) {
            $uri .= '&' . join('&', $param['fq']);
        }
        $uri .= !empty($param['facet']) ? $param['facet'] : '';
        $uri .= $param['sort'];
        $uri .= $param['start'] . $param['rows'];

        return $this->defData();
//        $this->load->library(array("lib_curl"));
//        $res = Lib_curl::httpRequest($this->solr_url, $uri);
//        $return = json_decode($res, TRUE);
//        return $return;
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

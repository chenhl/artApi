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
//        echo $this->solr_url;
        $param = array();
        //默认条件
        $param['fq'] = array();
        $param['fq'][] = 'fq=status:99';
        //返回字段
        $param['fl'] = '&fl=*';
        //默认查询字段
        $param['df'] = '&df=text'; //solr7.2中 schema已不在支持参数 defaultSearchField
        ##########查询条件
        //关键字
        if (!empty($condition['q'])) {
            $param['q'] = 'q=' . urlencode($condition['q']);
            if (!empty($condition['q.op'])) {//solr7.2中 schema已不在支持参数defaultOperator
                $param['q'] .= '&q.op=' . $condition['q.op']; // AND OR
            } else {
                $param['q'] .= '&q.op=AND'; // AND OR
            }
            //排序
            $param['sort'] = '';
        } else {
            $param['q'] = 'q=*:*';
            //排序
            $param['sort'] = '&sort=aid+desc';
        }
        //cate
        if (!empty($condition['cate_id'])) {
            $param['fq'][] = 'fq=cate_id:' . $condition['cate_id'];
        }
        //author
        if (!empty($condition['uid'])) {
            $param['fq'][] = 'fq=uid:' . $condition['uid'];
        }

        //文章id 单个
        if (!empty($condition['aid'])) {
            $param['fq'][] = 'fq=aid:' . $condition['aid'];
        }
        //多个
        if (!empty($condition['aids'])) {
            $param['fq'][] = 'fq=aid:(' . join('+OR+', $condition['aids']) . ')';
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
//        print_r($param);
//        echo $this->solr_url;
//        echo $uri;
        $this->load->library(array("lib_curl"));
        $res = Lib_curl::httpRequest($this->solr_url, $uri);
        $return = json_decode($res, TRUE);
        if ($return['response']['numFound'] > 0) {

            foreach ($return['response']['docs'] as $key => $row) {
                if ($row['image']) {
                    $return['response']['docs'][$key]['image'] = $this->imgurl($row['image']);
                }
                if (!empty($row['images'])) {
                    $imgs = json_decode($row['images'], TRUE);
                    if (!empty($imgs) && count($imgs) >= 4) {//
                        $_tmp = array();
                        foreach ($imgs as $img) {
                            $_tmp[] = $this->imgurl($img);
                        }
                        $return['response']['docs'][$key]['images'] = $_tmp;
                    } else {
                        $return['response']['docs'][$key]['images'] = array();
                    }
                }
                if ($row['focuspic']) {
                    $return['response']['docs'][$key]['focuspic'] = $this->imgurl($row['focuspic']);
                }
                if ($row['upic']) {
                    $return['response']['docs'][$key]['upic'] = $this->imgurl($row['upic']);
                }
                $return['response']['docs'][$key]['a_url'] = $this->article_url($row['aid']);
                $return['response']['docs'][$key]['u_url'] = $this->author_url($row['uid']);
            }
            return array(
                'total' => $return['response']['numFound'],
                'list' => array_values($return['response']['docs']),
            );
        } else {
            return array(
                'total' => 0,
                'list' => array(),
            );
        }
    }

    private function mockFeed() {

        $data = '{
  "aid": 8608168,
  "uid": 17101705,
  "uname": "艺术家网",
  "upic": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "focus": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "image": "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
  "images": [
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/43feefdab91d46f2802c10d1f6102e71.jpeg",
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/1981cb05d052486a9c21c44a4a0af049.jpeg",
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/5c811c4ee52e46618b1027a49bfdc979.jpeg",
    "//5b0988e595225.cdn.sohucs.com/c_fill,w_150,h_100,g_faces,q_70/images/20181010/4517eab8e5f04f119a67683b6d2788bf.jpeg"
  ],
  "title": "刚刚！云南省级机构改革首批6部门挂牌成立！",
  "mobile_title": "刚刚！云南省级机构改革首批6部门挂牌成立！",
  "tags": [
    {
      "id": 90370113,
      "name": "书法"
    },
    {
      "id": 90370113,
      "name": "艺术家"
    }
  ],
  "outer_link": "",
  "cate_id": 73,
  "cate_name": "",
  "create_time": "2018-10-24 10:11:13"
}';
        $arr = json_decode($data, TRUE);
        $return = array();
        for ($index = 0; $index < 10; $index++) {
            $_tmp = $arr;
            $_tmp['title'] = $arr['title'] . $index;
            $_tmp['mobile_title'] = $arr['mobile_title'] . $index;
            if ($index == 1) {
                $_tmp['images'] = array();
            }
            if ($index == 2) {
                $_tmp['image'] = '';
                $_tmp['images'] = array();
            }
            $return[] = $_tmp;
        }
        return $return;
//        return array(json_decode($data, TRUE));
    }

}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of etl
 *
 * @author chl
 */
class CronEtl extends Base_Controller {

    private $cate_info = array();
    private $area_info = array();
    private $xml_dir = '/data/solr/xml/';

    public function __construct() {
        parent::__construct();
        $this->load->model(array('article_model'));
    }

    private function _init() {
        ini_set('memory_limit', '500M');
        //初化数据
        $this->cate_info = $this->_getCate();
        $this->area_info = $this->_getArea();
    }

    /**
     * 全量更新
     */
    public function full_import() {
//        $domain = 'www-test.babyonlinedress.cn';
        $this->_init();
        //文章
        $_data = $this->article_model->etl_article();
        //机构（）

        $data = $this->_parseData($_data);
        $this->_parse2addxml($data);
    }

    /**
     * 增量更新 (监听news表)
     * @param type $domain
     */
    public function delta_import($date_time) {
        $this->_init();
//        $date_time = date('Y-m-d H:i:s',$_date_time);
        echo "param time:" . $date_time . "\n";
        $date_time = date('Y-m-d H:i:s', strtotime($date_time) - 60);
        echo "sql time:" . $date_time . "\n";

        //增量
        $conditon = array('date_time' => $date_time);
        $_data = $this->article_model->etl_article($conditon);

        //解析生成xml
        $data = $this->_parseData($_data);
        $this->_parse2addxml($data, TRUE);
    }

    /**
     * 取所有的三级分类
     * @param type $domain
     * @return type
     */
    private function _getCate() {

        return array();
    }

    /**
     * 取所有的三级分类
     * @param type $domain
     * @return type
     */
    private function _getArea() {

        return array();
    }

    /**
     * 解析数据库来的数据到solr可用的字段
     * @param type $data
     */
    private function _parseData(&$data, $channel) {
        if (empty($data)) {
            return array();
        }

        $return = array();
        foreach ($data as $row) {
            $_temp = array();
            $_temp['id'] = $channel . '-' . $row['id'];
            $_temp['aid'] = $row['id'];
            $_temp['status'] = $row['status'];

            $_temp['uid'] = $row['userid'];
            $_temp['uname'] = $row['nickname'];
            $_temp['upic'] = $row['userpic'];

            $_temp['channel'] = $channel;
            $_temp['cate_id'] = $row['catid'];
            $_temp['cate_name'] = $this->cate_info[$row['catid']]['name'];

            $_temp['collection_num'] = $row['collection_num'] ? $row['collection_num'] : 0;
            $_temp['comment_num'] = $row['comment_num'] ? $row['comment_num'] : 0;
            $_temp['like_num'] = $row['like_num'] ? $row['like_num'] : 0;

            $_temp['create_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['create_time']));
            $_temp['update_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['update_time']));

            $_temp['title'] = htmlspecialchars($row['title']);
            $_temp['keywords'] = htmlspecialchars($row['keywords']);
            $_temp['tags'] = isset($row['tags']) ? $row['tags'] : '';
            $_temp['content'] = !empty($row['content_search']) ? $row['content_search'] : strip_tags($row['content']);

            $_temp['focuspic'] = isset($row['focuspic']) ? $row['focuspic'] : '';
            $_temp['image'] = $row['image'];
            $_temp['images'] = $row['images'];

            //
            $_temp['zhuban'] = isset($row['zhuban']) ? $row['zhuban'] : '';
            $_temp['xieban'] = isset($row['xieban']) ? $row['xieban'] : '';
            if (!empty($row['start_time'])) {
                $_temp['start_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['start_time']));
            } else {
                $_temp['start_time'] = '000-00-00T00:00:00Z';
            }
            if (!empty($row['end_time'])) {
                $_temp['end_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['end_time']));
            } else {
                $_temp['end_time'] = '000-00-00T00:00:00Z';
            }

            $return[] = $_temp;
        }
        return $return;
    }

    /**
     * 生成到xml格式
     * @param type $data
     */
    private function _parse2addxml(&$data, $delta = false) {
        $_xmldatakey = $this->_xmldatakey();
        if ($delta) {
            $file = $this->xml_dir . 'delta_import.xml';
        } else {
            $file = $this->xml_dir . 'full_import.xml';
        }
        $xml = "<add>\t\n";
        foreach ($data as $row) {
            $xml .= "<doc>\t\t\n";
            foreach ($row as $key => $value) {
                if (in_array($key, $_xmldatakey)) {
                    $xml .= "<field name=\"" . $key . "\"><![CDATA[" . $value . "]]></field>\t\n";
                } elseif (is_array($value)) {
                    foreach ($value as $_value) {
                        if (!$_value) {
                            continue;
                        }
                        $xml .= "<field name=\"" . $key . "\">" . $_value . "</field>\t\n";
                    }
                } else {
                    $xml .= "<field name=\"" . $key . "\">" . $value . "</field>\t\n";
                }
            }
            $xml .= "</doc>\t\n";
        }
        $xml .= "</add>\t\n";
        file_put_contents($file, $xml);
        echo $file . " finish\n";
    }

}

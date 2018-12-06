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
    private $exhibit_meta = array();
    private $gallery_meta = array();
    private $xml_dir = FCPATH . 'xml/';

    public function __construct() {
        parent::__construct();
        if (!is_cli()) {
            exit;
        }
        $this->load->model(array('article_model', 'category_model'));
    }

    private function _init() {
        ini_set('memory_limit', '500M');
        //初化数据
        $this->cate_info = $this->_getCate();
        $this->area_info = $this->_getArea();
        $this->exhibit_meta = $this->_getExhibitMeta();
        $this->gallery_meta = $this->_getGalleryMeta();
    }

    /**
     * 全量更新
     */
    public function full_import() {
//        $domain = 'www-test.babyonlinedress.cn';

        $this->_init();
        //文章
        $condition = array('id' => 854);
        $_data = $this->article_model->etl_article($condition);
//        print_r($_data);
        //机构（）

        $data = $this->_parseData($_data, 'news');
//        $this->_parse2addxml($data);
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
        $data = $this->category_model->getList();
        $return = array();
        if ($data) {
            foreach ($data as $row) {
                $return[$row['catid']] = $row;
            }
        }
        return $return;
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
            $_temp['aid'] = $row['aid'];
            $_temp['status'] = $row['status'];

            $_temp['uid'] = !empty($row['uid']) ? $row['uid'] : 0;
            $_temp['uname'] = $row['uname'];
            $_temp['upic'] = $row['userpic'];

            $_temp['channel'] = $channel;
            $_temp['cate_id'] = $row['catid'];
            $_temp['cate_name'] = $this->cate_info[$row['catid']]['catname'];

            $_temp['collect_num'] = isset($row['collect_num']) ? $row['collect_num'] : 0;
            $_temp['comment_num'] = isset($row['comment_num']) ? $row['comment_num'] : 0;
            $_temp['like_num'] = isset($row['like_num']) ? $row['like_num'] : 0;

            $_temp['create_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['create_time']));
            $_temp['update_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['update_time']));

            $_temp['title'] = htmlspecialchars($row['title']);
            $_temp['keywords'] = htmlspecialchars($row['keywords']);
            $_temp['tags'] = isset($row['tags']) ? json_decode($row['tags'], TRUE) : array();
            $_temp['content'] = !empty($row['content_search']) ? $row['content_search'] : strip_tags($row['content']);

            $_temp['focuspic'] = isset($row['focuspic']) ? $row['focuspic'] : '';
            $_temp['image'] = isset($row['thumb']) ? $row['thumb'] : '';
            $_temp['images'] = isset($row['thumbs']) ? $row['thumbs'] : '';

            //
            $_temp['zhuban'] = isset($row['zhuban']) ? $row['zhuban'] : '';
            $_temp['xieban'] = isset($row['xieban']) ? $row['xieban'] : '';
            if (!empty($row['start_time'])) {
                $_temp['start_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['start_time']));
            } else {
                $_temp['start_time'] = '1970-01-01T08:00:00Z';
            }
            if (!empty($row['end_time'])) {
                $_temp['end_time'] = date("Y-m-d\TH:i:s\Z", strtotime($row['end_time']));
            } else {
                $_temp['end_time'] = '1970-01-01T08:00:00Z';
            }
            //attr
            if (!empty($row['spider_attr'])) {
                $spider_attr = json_decode($row['spider_attr'], TRUE);
                print_r($this->exhibit_meta);
                print_r($spider_attr);
                $attr = array();
                if (json_last_error() == JSON_ERROR_NONE) {
                    foreach ($spider_attr as $_key => $_val) {
                        $_val = $this->_parseBase($_val);
                        $_attr = $this->exhibit_meta[$_key];
                        if ($_attr['first']) {
                            $_val = $_val[0];
                        }
                        if ($_attr['parse']) {
                            $parse = $_attr['parse'];
                            $_val = $this->$parse($_val);
                        }
                        $attr[$_attr['code']] = $_val;
                    }
                }
                $_temp['attr'] = $attr;
            }

            $return[] = $_temp;
        }
        print_r($return);
        return $return;
    }

    private function _xmldatakey() {
        return array(
            'title',
            'keywords',
            'content'
        );
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

    private function _getExhibitMeta() {
        $meta = array(
            array('txt' => '展览名称：', 'code' => 'name', 'first' => TRUE, 'parse' => '', 'multi' => FALSE),
            array('txt' => '展览时间：', 'code' => 'times', 'first' => TRUE, 'parse' => '_parseTimes', 'multi' => FALSE),
            array('txt' => '开幕时间：', 'code' => 'open_time', 'first' => TRUE, 'parse' => '', 'multi' => FALSE),
            array('txt' => '展览城市：', 'code' => 'area', 'first' => TRUE, 'parse' => '_parseArea', 'multi' => FALSE),
            array('txt' => '展览地址：', 'code' => 'address', 'first' => TRUE, 'parse' => '', 'multi' => FALSE),
            
            array('txt' => '展览咨询：', 'code' => 'consultation', 'first' => FALSE, 'parse' => '','multi'=>FALSE),
            array('txt' => '展览备注：', 'code' => 'msg', 'first' => FALSE, 'parse' => '','multi'=>FALSE),
            
            array('txt' => '展览机构：', 'code' => 'org', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
            array('txt' => '主办单位：', 'code' => 'org_main', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
            array('txt' => '承办单位：', 'code' => 'org_manager', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
            array('txt' => '策 展 人：', 'code' => 'plan', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
            array('txt' => '艺术总监：', 'code' => 'art_chief', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
            array('txt' => '参展人员：', 'code' => 'artists', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
            array('txt' => '参展艺术家：', 'code' => 'artists', 'first' => FALSE, 'parse' => '','multi'=>TRUE),
        );
        $return = array();
        foreach ($meta as $row) {
            $return[$row['txt']] = array(
                'code' => $row['code'],
                'first' => $row['first'],
                'parse' => $row['parse'],
            );
        }
        return $return;
    }

    private function _parseBase($val) {
        $return = array();
        foreach ($val as $value) {
            $value = trim($value);
            if (!empty($value)) {
                $return[] = $value;
            }
        }
        return $return;
    }

    private function _parseTimes($val) {
        return $val;
    }

    private function _parseArea($val) {
        return $val;
    }

    private function _getGalleryMeta() {
        $meta = array(
            array('txt' => '所在城市：', 'code' => 'area'),
            array('txt' => '主营项目：', 'code' => 'main_item'),
        );
        $return = array();
        foreach ($meta as $row) {
            $return[$row['txt']] = $row['code'];
        }
        return $return;
    }

}

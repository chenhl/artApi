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
    private $xml_dir = '';
    private $xml_file_pre = '';

    /**
     * 数据库查询分页，每个xml的大小
     * 游标+yeild
     * @var type 
     */
    private $page_size = 5000;

    /**
     * 
     * 写入xml文件分页，分批写入xml
     * @var type 
     */
    private $sub_page_size = 500;

    public function __construct() {
        parent::__construct();
        if (!is_cli()) {
            exit;
        }
        $this->load->model(array('article_model', 'category_model', 'channel_model'));
        $this->xml_dir = $_SERVER['SOLR_XML_PATH'];
    }

    private function _init() {
        ini_set('memory_limit', '1000M');
        //初化数据
        $this->cate_info = $this->_getCate();
        $this->area_info = $this->_getArea();
        $this->exhibit_meta = $this->_getExhibitMeta();
        $this->gallery_meta = $this->_getGalleryMeta();
    }

    /**
     * 全量更新
     */
    public function full_import($page_size = 0) {
        $this->_init();
        if ($page_size) {
            $this->page_size = $page_size;
        }
        $this->xml_file_pre = $this->xml_dir . 'full_import_';
        $condition = array();
        $this->_import($condition);

//        // test
//        $page=1;
//        $start_time = microtime(TRUE);
//        $data = $this->article_model->etl_article($condition, $page, $this->page_size);
//        $newLine = PHP_SAPI == 'cli' ? "\n" : '<br />';
//        $i = 0;
//        foreach ($data as $row) {
//            echo $row['id'] . $newLine;
//            $i++;
//        }
//        $end_time = microtime(TRUE);
//        echo "消耗内存：" . (memory_get_usage() / 1024 / 1024) . "M" . $newLine;
//        echo "时间：" . ($end_time - $start_time) . $newLine;
//        echo "处理数据行数：" . $i . $newLine;
//        echo "success";
    }

    /**
     * 增量更新 
     * @param type $date_time
     */
    public function delta_import($date_time, $page_size = 0) {
        $this->_init();
        if ($page_size) {
            $this->page_size = $page_size;
        }
        $this->xml_file_pre = $this->xml_dir . 'delta_import_';
//        $date_time = date('Y-m-d H:i:s',$_date_time);
        echo "param time:" . $date_time . "\n";
        $date_time = date('Y-m-d H:i:s', strtotime($date_time) - 60);
        echo "sql time:" . $date_time . "\n";

        $condition = array('date_time' => $date_time);
        $this->_import($condition);
    }

    private function _import($param) {
        $condition = array();
        if (!empty($param['date_time'])) {
            $condition['date_time'] = $param['date_time'];
        }
        $page = 1;
        //总量
        $total = $this->article_model->etl_article_count($condition);
        if (empty($total)) {
            echo 'count:0';
            exit;
        }
        $max_sub_page = $this->page_size / $this->sub_page_size;

        do {
            $file = $this->xml_file_pre . $page . '.xml';
            echo $file . ' start :' . date('Y-m-d H:i:s') . "\n";
            file_put_contents($file, "<add>\t\n", LOCK_EX);
            //分页
//            $this->page_size=2;
            $_data = $this->article_model->etl_article($condition, $page, $this->page_size);
            $i = 0;
            $sub_page = 0;
//            $_data_num = ;
            foreach ($_data as $row) {
                $j = $i % $this->sub_page_size;
                if ($j == 0) {//新的分组开始
                    $sub_data = array();
                    $sub_data[$j] = $row;
                } else {
                    $sub_data[$j] = $row;
                }
                $i++;

                $m = $i % $this->sub_page_size;
                if ($m == 0) {//分组结束 写入数据
                    $sub_page++;
                    $data = $this->_parseData($sub_data);
                    $this->_parse2addxml($data, $file);
                    if ($sub_page == $max_sub_page) {
                        file_put_contents($file, "</add>\t\n", FILE_APPEND | LOCK_EX);
                    }
                    $is_write = true;
                } else {
                    $is_write = FALSE;
                }
            }

            if (!$is_write) {
                $data = $this->_parseData($sub_data);
                $this->_parse2addxml($data, $file);
                file_put_contents($file, "</add>\t\n", FILE_APPEND | LOCK_EX);
                $next = FALSE;
            } else {
                $page++;
                $next = TRUE;
            }
            //下一步循环
//            if ($page * $this->page_size < $total) {
//                $page++;
//                $next = TRUE;
//            } else {
//                $next = FALSE;
//            }
//        print_r($_data);
//            $data = $this->_parseData($_data);
//            $this->_parse2addxml($data, $page);
            //test
//            $next = FALSE;
        } while ($next);
    }

    /**
     * 取所有的三级分类
     * 
     * @return type
     */
    private function _getCate() {
        $data = $this->category_model->getList();
        $cate_channel = $this->channel_model->cateChannel();
        $return = array();
        if ($data) {
            foreach ($data as $row) {
                $row['channel'] = isset($cate_channel[$row['catid']]) ? $cate_channel[$row['catid']] : '';
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
    private function _parseData($data) {
        if (empty($data)) {
            return array();
        }

        $return = array();
        foreach ($data as $row) {
            $_temp = array();
            $channel = $this->cate_info[$row['catid']]['channel'];
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


            if ($row['catid'] == 10) {//exhibit attr
                $this->_parseExhibitAttr($_temp, $row);
            } elseif ($row['catid'] == 11) {//gallery attr
                $this->_parseGalleryAttr($_temp, $row);
            }


            $return[] = $_temp;
        }
//        print_r($return);
        return $return;
    }

    private function _xmldatakey() {
        return array(
            'title',
            'keywords',
            'content',
            'attr_s_address',
            'attr_s_consultation',
            'attr_s_msg',
        );
    }

    /**
     * 生成到xml格式
     * @param type $data
     */
    private function _parse2addxml(&$data, $file) {
        $_xmldatakey = $this->_xmldatakey();
        $xml = '';
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

        file_put_contents($file, $xml, FILE_APPEND | LOCK_EX);

//        echo $file . " finish\n";
    }

    /**
     * exhibit attr
     * @return array
     */
    private function _getExhibitMeta() {
        $meta = array(
//            array('txt' => '展览名称：', 'code' => 'name', 'first' => TRUE, 'parse' => '', 'multi' => FALSE),
            //index store 
            array('txt' => '展览时间：', 'code' => 'times', 'first' => TRUE, 'parse' => '_parseTimes', 'multi' => FALSE),
            array('txt' => '展览城市：', 'code' => 'area', 'first' => TRUE, 'parse' => '_parseArea', 'multi' => FALSE),
            //
            array('txt' => '开幕时间：', 'code' => 'open_time', 'first' => TRUE, 'parse' => '', 'multi' => FALSE),
            //index store string
            array('txt' => '展览地址：', 'code' => 'address', 'first' => FALSE, 'parse' => '', 'multi' => FALSE),
            array('txt' => '展览咨询：', 'code' => 'consultation', 'first' => FALSE, 'parse' => '', 'multi' => FALSE),
            array('txt' => '展览备注：', 'code' => 'msg', 'first' => FALSE, 'parse' => '', 'multi' => FALSE),
            //index store arr
            array('txt' => '展览机构：', 'code' => 'org', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '主办单位：', 'code' => 'org_main', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '协办单位：', 'code' => 'org_slave', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '承办单位：', 'code' => 'org_manager', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '策 展 人：', 'code' => 'plan', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '艺术总监：', 'code' => 'art_chief', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '学术主持：', 'code' => 'art_academic', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '参展人员：', 'code' => 'artists', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
            array('txt' => '参展艺术家：', 'code' => 'artists', 'first' => FALSE, 'parse' => '', 'multi' => TRUE),
        );
        $return = array();
        foreach ($meta as $row) {
            $return[$row['txt']] = array(
                'code' => $row['code'],
                'first' => $row['first'],
                'parse' => $row['parse'],
                'multi' => $row['multi'],
            );
        }
        return $return;
    }

    /**
     * gallery attr
     * @return array
     */
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

    /**
     * 过滤attr
     * @param type $val
     * @return type
     */
    private function _filterAttr($val) {
        $return = array();
        foreach ($val as $value) {
            $value = trim($value);
            if (!empty($value)) {
                $return[] = htmlspecialchars($value);
            }
        }
        return $return;
    }

    /**
     * 解析 gallery attr (spider时已处理好key)
     * @param type $_temp
     * @param type $row
     */
    private function _parseGalleryAttr(&$_temp, &$row) {
        if (!empty($row['attr'])) {
            $spider_attr = json_decode($row['attr'], TRUE);
            if (json_last_error() == JSON_ERROR_NONE) {
                foreach ($spider_attr as $_key => $_val) {
                    if ($_key == 'area') {//area string
                        if (isset($_val[0])) {
                            $_temp['area_province'] = trim($_val[0]);
                        } else {
                            $_temp['area_province'] = '';
                        }
                        if (isset($_val[1])) {
                            $_temp['area_city'] = trim($_val[1]);
                        } else {
                            $_temp['area_city'] = '';
                        }
                        if ($_temp['area_province'] != $_temp['area_city']) {//北京-北京
                            $_temp['area'] = array($_temp['area_province'], $_temp['area_city']);
                        } else {
                            $_temp['area'] = array($_temp['area_province']);
                        }
                    } else { // 主营项目 多值
                        if (!empty($_val)) {
                            $_temp['attr_m_i_' . $_key] = $_val;
                        }
                    }
                }
            }
        }
    }

    /**
     * 解析 exhibit spider attr
     * @param type $_temp
     * @param type $row
     */
    private function _parseExhibitAttr(&$_temp, &$row) {
        if (!empty($row['spider_attr'])) {
            $spider_attr = json_decode($row['spider_attr'], TRUE);
            if (json_last_error() == JSON_ERROR_NONE) {
                foreach ($spider_attr as $_key => $_val) {
                    //去空格、空元素
                    $_val = $this->_filterAttr($_val);
                    if (empty($_val)) {
                        continue;
                    }
                    $_attr = isset($this->exhibit_meta[$_key]) ? $this->exhibit_meta[$_key] : array();
                    if (empty($_attr)) {
                        continue;
                    }
                    //取需要的元素，转成string或保留array()
                    if ($_attr['first']) {
                        $_val = $_val[0];
                    }
                    if ($_attr['code'] == 'times') {//times string
                        $_arr = explode(' - ', $_val);
                        if (isset($_arr[1])) {
                            $_temp['start_time'] = date("Y-m-d\TH:i:s\Z", strtotime(trim($_arr[0])));
                            $_temp['end_time'] = date("Y-m-d\TH:i:s\Z", strtotime(trim($_arr[1])));
                        }
                    } elseif ($_attr['code'] == 'open_time') {//open_time string
                        $_temp['open_time'] = date("Y-m-d\TH:i:s\Z", strtotime($_val));
                    } elseif ($_attr['code'] == 'area') {//area string
                        $_arr = explode('-', $_val);
                        $_temp['area_province'] = trim($_arr[0]);
                        if (isset($_arr[1])) {
                            $_temp['area_city'] = trim($_arr[1]);
                        } else {
                            $_temp['area_city'] = '';
                        }
                        if ($_temp['area_province'] != $_temp['area_city']) {//北京-北京
                            $_temp['area'] = array($_temp['area_province'], $_temp['area_city']);
                        } else {
                            $_temp['area'] = array($_temp['area_province']);
                        }
                    } else {
                        if (!$_attr['multi']) {//msg address etc.. string
                            $_temp['attr_s_' . $_attr['code']] = join('', $_val);
                        } else { // org artist etc..
                            $_temp['attr_m_i_' . $_attr['code']] = $_val;
                        }
                    }
                }
            }
        }
    }

}

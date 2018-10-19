<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Lib_curl
 *
 * @author chl
 */
class Lib_curl {

    //超时时间，秒级，
    private static $_timeout = 30;
    //超时时间，毫秒级
    private static $_ms_timeout = 0;
    //参数
    private static $_curl_setopt = array(
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_RETURNTRANSFER => TRUE, //结果返回给变量
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
//        CURLOPT_HEADER => FALSE, //要HTTP头不?
//        CURLOPT_NOBODY => FALSE, //不要内容?
//        CURLOPT_FOLLOWLOCATION => FALSE, //不自动跟踪
//        CURLOPT_RETURNTRANSFER => TRUE,
    );

    /**
     * CURLOPT_TIMEOUT
     * 设置超时时间
     * @param int $seconds
     */
    public static function setTimeOut($seconds = 10) {
        if ($seconds) {
            self::$_timeout = $seconds;
        }
    }

    /* CURLOPT_TIMEOUT_MS
     * 设置超时时间
     * @param int $seconds
     */

    public static function setTimeOutMS($ms_seconds = 500) {
        if ($ms_seconds) {
            self::$_ms_timeout = $ms_seconds;
        }
    }

    /**
     * http请求
     * @param string $url 请求的url
     * @param array | string $param 请求参数
     * @param bool $ispost 是否post请求，默认为false 
     * @param array $headers header
     * @param bool $followlocation 返回redirect url;
     * @return mixed
     */
    public static function httpRequest($url, $param, $ispost = FALSE, $headers = array()) {
        if (is_array($param)) {
            $strParam = http_build_query($param);
        } else {
            $strParam = $param;
        }
        $ch = curl_init();
        $curOpt = self::_setOPt($url, $strParam, $ispost, $headers);
        curl_setopt_array($ch, $curOpt);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * 返回一个302地址
     * @param $url
     * @param $vars
     * @return bool
     */
    public static function curl_post_302($url, $vars) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 302 redirect
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $data = curl_exec($ch);
        $Headers = curl_getinfo($ch);
        curl_close($ch);
        if ($data != $Headers)
            return $Headers["url"];
        else
            return false;
    }

    /**
     * 支持带302跳转的url网页内容抓取
     * @param $url
     * @return mixed
     */
    public static function curl_get_302($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        //curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); //设为1，则会自动301，302跳转
        $ret = curl_exec($ch);
        $info = curl_getinfo($ch);
        //log_message('info_curl_get_302', var_export($info, TRUE));
        curl_close($ch);
        return $ret;
    }

    private static function _setOPt($url, $strParam, $ispost, $headers) {
        $curOpt = self::$_curl_setopt;
        if (self::$_ms_timeout) {
            $curOpt[CURLOPT_TIMEOUT_MS] = self::$_ms_timeout;
        } elseif (self::$_timeout) {
            $curOpt[CURLOPT_TIMEOUT] = self::$_timeout;
        }
        if (!empty($headers)) {
            $curOpt[CURLOPT_HTTPHEADER] = $headers;
        } else {
            $curOpt[CURLOPT_HEADER] = FALSE;
        }
        if (parse_url($url, PHP_URL_SCHEME) == 'https') {
            $curOpt[CURLOPT_SSL_VERIFYHOST] = FALSE;
            $curOpt[CURLOPT_SSL_VERIFYPEER] = FALSE;
            $curOpt[CURLOPT_SSLVERSION] = 6;
        }
        if ($ispost) {
            $curOpt[CURLOPT_POST] = TRUE;
            if (!empty($strParam)) {
                $curOpt[CURLOPT_POSTFIELDS] = $strParam;
            }
        } elseif (strpos($url, "?") === FALSE) {
            if (!empty($strParam)) {
                $url .= "?" . $strParam;
            }
        } else {
            if (!empty($strParam)) {
                $url .= "&" . $strParam;
            }
        }
        $curOpt[CURLOPT_URL] = $url;
        return $curOpt;
    }

    /**
     * 
     * @param type $urls array(array('url'=>'http://....','query'=>array|string),array('url'=>'http://....','param'=>array|string))
     * @param type $param
     * @param type $ispost
     * @param type $headers
     */
    public static function multiHttp($urls, $ispost = FALSE) {
//        print_r($urls);
        $m_ch = curl_multi_init();
        $map = array();
        foreach ($urls as $key => $row) {
            $ch = curl_init();
            if (is_array($row['query'])) {
                $strParam = http_build_query($row['query']);
            } else {
                $strParam = $row['query'];
            }
            !$headers = $row['header'] && $headers = array();
            $curOpt = self::_setOPt($row['url'], $strParam, $ispost, $headers);
            curl_setopt_array($ch, $curOpt);
            curl_multi_add_handle($m_ch, $ch);
            if ($row['curl_key']) {
                $map[(string) $ch] = $row['curl_key'];
            } else {
                $map[(string) $ch] = md5($strParam);
            }
        }
        $responses = array();
        $active = NULL;
        do {
            while (($code = curl_multi_exec($m_ch, $active)) == CURLM_CALL_MULTI_PERFORM);
            if ($code != CURLM_OK) {
                break;
            }
            while ($done = curl_multi_info_read($m_ch)) {
                $error = curl_error($done['handle']);
                $results = curl_multi_getcontent($done['handle']);
                $responses[$map[(string) $done['handle']]] = compact('error', 'results');
                curl_multi_remove_handle($m_ch, $done['handle']);
                curl_close($done['handle']);
            }
            if ($active > 0) {
                curl_multi_select($m_ch, 0.5);
            }
        } while ($active);
        curl_multi_close($m_ch);
        return $responses;
    }

    public function curl($urls, $post) {
        $queue = curl_multi_init();
        $map = array();
        foreach ($urls as $key => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post[$key]);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);
            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $url;
        }
        $responses = array();
        $active = NULL;
        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM);
            if ($code != CURLM_OK) {
                break;
            }
            while ($done = curl_multi_info_read($queue)) {
                $error = curl_error($done['handle']);
                $results = curl_multi_getcontent($done['handle']);
                $responses[$map[(string) $done['handle']]] = compact('error', 'results');
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }
        } while ($active);
        curl_multi_close($queue);
        return $responses;
    }

}

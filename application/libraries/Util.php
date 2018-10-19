<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of util
 *
 * @author Administrator
 */
class Util {

    public static function toJson($data = array()) {
        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    public static function _json_encode($data, $option = null) {
//        return htmlspecialchars(urlencode(json_encode($data, $option)));
        if (!empty($option)) {
            json_encode($data, $option);
        } else {
            return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public static function _json_decode($json_str, $assoc = true) {
        return json_decode($json_str, $assoc);
//        return json_decode(urldecode(htmlspecialchars_decode($json_str)), $assoc);
    }

}

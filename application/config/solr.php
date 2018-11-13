<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$config['solr_conf'] = array(
//    'url' => $_SERVER['SOLR_URL'],
    'url'=>'http://localhost:8983/solr/test/select',
);

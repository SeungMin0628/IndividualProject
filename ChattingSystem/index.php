<?php
/**
 * Created by PhpStorm.
 * User: Seungmin Lee
 * Date: 2017-12-05
 * Time: 오후 2:36
 */

// 01. 접속 경로 확인
$access_root    = explode('/', $_SERVER['PHP_SELF']);

// 02. 웹 페이지 접속 경로에 따른 기능 실행
if(sizeof($access_root) < 5) {
    include_once('./view/login.php');
} else {
    include_once('./control/ChattingSystem_c.php');

    $control = new ChattingSystem_c();

    if(isset($access_root[5])) {
        $control->$access_root[4]($access_root[5]);
    } else {
        $control->$access_root[4]();
    }
}
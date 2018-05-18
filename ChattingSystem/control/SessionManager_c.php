<?php
    // index.php가 아닌 다른 경로로 접근했을 시
    if(!preg_match("#/web/chat/index.php#", $_SERVER['PHP_SELF'])) {
        echo "<script>
                    alert('잘못된 접근!');
                    history.back();
                  </script>";
        return;
    }
/**
 * Created by PhpStorm.
 * User: Seungmin Lee
 * Date: 2017-12-05
 * Time: 오후 3:35
 */

include_once "./model/UserInfo.php";

class SessionManager_c {
    // 01. 기능 정의
    // 01-01. 유저 정보를 등록하고 세션 시작
    function start(UserInfo $argData) {
        // 01. if 세션이 실행 중이면 then 세션 제거
        if(session_status() == PHP_SESSION_ACTIVE) {
            $this->destroy();
        }

        session_start();
        $_SESSION['id']     = $argData->id;
        $_SESSION['name']   = $argData->name;
    }

    // 01-02. 세션 제거
    function destroy() {
        session_reset();
        session_destroy();
    }

    // 01-03. 세션 체크 -> 세션이 시작중이고 유저 정보가 있는지 검사
    function check() {
        if(session_status() == PHP_SESSION_ACTIVE) {
            // 유저 정보 등록 여부 체크
            if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
                return true;
            } else {
                $this->destroy();
            }
        }

        return false;
    }

    // 01-04. 세션에서 사용자 정보 불러오기
    function getUserInfo() {
        if(session_status() == PHP_SESSION_ACTIVE) {
            return $_SESSION;
        }

        return false;
    }

    //
}
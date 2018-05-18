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
 * Time: 오후 3:02
 */

class DBInfo {
    // 01. DB 정보
    const URL   = 'localhost';
    const ID    = 'root';
    const PW    = 'Lsm950628';
    const DB    = 'web';

    // 02. 유저 정보 테이블
    const USER_INFO = array (
        "t_name"    => "user_info",
        "id"        => "id",
        "pw"        => "password",
        "name"      => "name",
        "s_id"      => "sess_id",
        "n_alias"   => "user_name"
    );

    // 03. 채팅방 정보
    const CHAT_ROOM = array(
        "t_name"    => "chat_room",
        "id"        => "id",
        "name"      => "name",
        "manager"   => "manager",
        "reg"       => "reg_date",
        "n_alias"   => "room_name"
    );

    // 04. 채팅방 참가자 목록
    const CHAT_PARTICIPANT = array(
        "t_name"    => "chat_participant",
        "id"        => "id",
        "r_id"      => "room_id",
        "u_id"      => "user_id",
        "s_chat"    => "start_chat",
        "p_alias"   => "participant"
    );

    // 05. 채팅 메시지 내역
    const CHAT_MESSAGE = array(
        "t_name"    => "chat_message",
        "id"        => "id",
        "r_id"      => "room_id",
        "u_id"      => "user_id",
        "con"       => "contents",
        "w_flag"    => "isWriter"
    );

    // 06. 한 페이지 당 글 개수 지정
    const ROOM_PER_PAGE = 5;
}
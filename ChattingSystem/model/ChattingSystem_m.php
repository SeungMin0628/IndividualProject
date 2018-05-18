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
 * Time: 오후 2:40
 */

include_once "./model/DBInfo.php";
include_once "./model/UserInfo.php";

class ChattingSystem_m {
    // 01. 멤버 변수 정의
    private $con;

    // 02. 생성자 정의
    function __construct() {
        $this->con = new mysqli(DBInfo::URL, DBinfo::ID, DBInfo::PW, DBInfo::DB);

        try {
            if ($this->con->connect_errno) {
                throw new Exception($this->con->connect_error);
            }
        } catch(Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    // 03. 소멸자 정의
    function __destruct() {
        $this->con->close();
    }

    // 04. 기능 정의
    // 04-01-01. 조회 - 회원 정보 가져오기
    function select_userInfo($argId) {
        try {
            // 01. sql 구문 작성
            $tableInfo = DBInfo::USER_INFO;
            $sql = "SELECT * 
                    FROM {$tableInfo['t_name']}
                    WHERE {$tableInfo['id']} = '{$argId}'";

            // 02. 질의 실행
            if (($query = $this->con->query($sql)) === false) {
                throw new Exception("query false");
            }
        } catch(Exception $e) {
            return null;
        }

        // 03. 결과 반환
        if($query->num_rows <= 0) {
            // if 조회 결과가 없으면 then false 반환
            return false;
        }

        return $query->fetch_object("UserInfo");
    }

    // 04-01-02. 조회 - 채팅방 목록 가져오기
    function select_roomList($argPage) {
        try {
            // 01. SQL 구문 작성
            $uTable = DBInfo::USER_INFO;
            $pTable = DBInfo::CHAT_PARTICIPANT;
            $rTable = DBInfo::CHAT_ROOM;
            $rpp    = DBInfo::ROOM_PER_PAGE;
            $start  = $argPage * $rpp;

            $sql = "SELECT r.{$rTable['id']}, r.{$rTable['name']} AS '{$rTable['n_alias']}', u.{$uTable['name']} AS {$uTable['n_alias']}, p.{$pTable['p_alias']}, r.{$rTable['reg']}
                    FROM {$rTable['t_name']} r
                    JOIN   (SELECT {$pTable['r_id']}, COUNT({$pTable['id']}) AS '{$pTable['p_alias']}'
                            FROM {$pTable['t_name']}
                            GROUP BY {$pTable['r_id']}) p
                    ON r.{$rTable['id']} = p.{$pTable['r_id']}
                    JOIN {$uTable['t_name']} u
                    ON r.{$rTable['manager']} = u.{$uTable['id']}
                    ORDER BY r.{$rTable['id']} DESC LIMIT {$start}, {$rpp}";

            // 02. 질의
            if (($query = $this->con->query($sql)) == false) {
                throw new Exception('select list of rooms is failed.');
            }
        } catch(Exception $e) {
            return false;
        }

        // 03. 결과 반환
        // if 결과 행이 없다면 then false 반환
        if($query->num_rows <= 0) {
            return false;
        }

        $result = array();
        while($row = $query->fetch_assoc()) {
            array_push($result, $row);
        }

        return $result;
    }

    // 04-01-03. 조회 - 최대 페이지
    function select_maxPage() {
        // 01. SQL 구문 작성
        $tableInfo  = DBInfo::CHAT_ROOM;
        $rpp        = DBInfo::ROOM_PER_PAGE;

        $sql = "SELECT CEIL(COUNT(*) / {$rpp})
                FROM {$tableInfo['t_name']}";

        // 02. 질의
        try {
            if (($query = $this->con->query($sql)) == false) {
                throw new Exception('select count of page is failed.');
            }
        } catch(Exception $e) {
            return false;
        }

        // 03. 결과 반환
        return $query->fetch_row()[0];
    }

    // 04-01-04. 조회 - 채팅방 정보
    function select_roomInfo($argRoomId) {
        try {
            // 01. SQL 구문 작성
            $rInfo      = DBInfo::CHAT_ROOM;
            $uInfo      = DBinfo::USER_INFO;

            $sql = "SELECT r.{$rInfo['id']}, r.{$rInfo['name']} AS '{$rInfo['n_alias']}', u.{$uInfo['name']} AS '{$uInfo['n_alias']}', r.{$rInfo['reg']}
                    FROM {$rInfo['t_name']} r
                    JOIN {$uInfo['t_name']} u
                    ON (u.{$uInfo['id']} = r.{$rInfo['manager']})
                    WHERE r.id = {$argRoomId}";

            // 02. 질의
            if(($query = $this->con->query($sql)) == false) {
                throw new Exception("select room info is failed.");
            }

            // 03. 질의 결과 반환
            if($query->num_rows <= 0) {
                throw new Exception("room is not exist");
            }

            return $query->fetch_assoc();
        } catch(Exception $e) {
            return false;
        }
    }

    // 04-01-05. 조회 - 채팅 메시지
    function select_message($argUserId, $argRoomId, $argRecentMessage) {
        // 01. SQL 구문 설정
        $mInfo  = DBInfo::CHAT_MESSAGE;
        $uInfo  = DBInfo::USER_INFO;
        $pInfo  = DBInfo::CHAT_PARTICIPANT;

        if($argRecentMessage < 0) {
            // if 최근 메시지 번호가 0보다 작으면(최초 조회) then chat_participant 테이블의 사용자 시작 채팅 번호로 조회
            $sql = "SELECT m.{$mInfo['id']}, u.{$uInfo['name']}, m.{$mInfo['con']},
                    CASE m.{$mInfo['u_id']} WHEN '{$argUserId}' THEN 1 ELSE 0 END AS '{$mInfo['w_flag']}'
                    FROM {$mInfo['t_name']} m
                    JOIN {$uInfo['t_name']} u
                    ON (m.{$mInfo['u_id']} = u.{$uInfo['id']})
                    WHERE m.{$mInfo['r_id']} = {$argRoomId}
                    AND m.{$mInfo['id']} >= (SELECT {$pInfo['s_chat']}
                                             FROM {$pInfo['t_name']}
                                             WHERE {$pInfo['r_id']} = {$argRoomId}
                                             AND {$pInfo['u_id']} = '{$argUserId}')
                    ORDER BY m.{$mInfo['id']}";
        } else {
            $sql = "SELECT m.{$mInfo['id']}, u.{$uInfo['name']}, m.{$mInfo['con']},
                    CASE m.{$mInfo['u_id']} WHEN '{$argUserId}' THEN 1 ELSE 0 END AS '{$mInfo['w_flag']}'
                    FROM {$mInfo['t_name']} m
                    JOIN {$uInfo['t_name']} u
                    ON (m.{$mInfo['u_id']} = u.{$uInfo['id']})
                    WHERE m.{$mInfo['r_id']} = {$argRoomId}
                    AND m.{$mInfo['id']} > {$argRecentMessage}
                    ORDER BY m.{$mInfo['id']}";
        }

        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) == false) {
                throw new Exception("select message list is failed");
            }

            // 03. 결과 반환
            $result = array();
            while($row = $query->fetch_assoc()) {
                array_push($result, $row);
            }

            return $result;
        } catch(Exception $e) {
            return false;
        }
    }

    // 04-01-06. 조회 - 사용자 참가 여부 조회
    function select_isParticipate($argRoomId, $argUserId) {
        // 01. 쿼리문 설정
        $tableInfo = DBInfo::CHAT_PARTICIPANT;
        $sql = "SELECT *
                FROM {$tableInfo['t_name']}
                WHERE {$tableInfo['r_id']} = {$argRoomId}
                AND {$tableInfo['u_id']} = '{$argUserId}'";

        try {
            // 02. 질의 실행
            if(($query = $this->con->query($sql)) == false) {
                throw new Exception('select query is failed.');
            }

            // 03. 결과 반환
            if($query->num_rows > 0) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return null;
        }
    }

    // 04-01-07. 조회 - 사용자 방장 여부
    function select_isManager($argRoomId, $argUserId) {
        // 01. 쿼리문 설정
        $tableInfo = DBInfo::CHAT_ROOM;
        $sql = "SELECT CASE {$tableInfo['manager']} WHEN '{$argUserId}' THEN 1 ELSE 0 END AS 'isManager'
                FROM {$tableInfo['t_name']}
                WHERE {$tableInfo['id']} = {$argRoomId}";

        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception("select manager is failed.");
            } else if($query->num_rows <= 0) {
                throw new Exception("no row is selected.");
            }

            // 03. 결과 반환
            if($query->fetch_row()[0] == 1) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return null;
        }
    }

    // 04-01-08. 조회 - 방장 제외 가장 먼저 참가한 인원
    function select_newManager($argRoomId) {
        // 01. SQL 구문 작성
        $rInfo = DBInfo::CHAT_ROOM;
        $pInfo = DBInfo::CHAT_PARTICIPANT;
        $uInfo = DBInfo::USER_INFO;
        $sql = "SELECT {$uInfo['name']}, {$uInfo['id']}
                FROM {$uInfo['t_name']}
                WHERE {$uInfo['id']} = (SELECT {$pInfo['u_id']}
                                        FROM {$pInfo['t_name']}
                                        WHERE {$pInfo['s_chat']} = (SELECT MIN({$pInfo['s_chat']})
                                                                    FROM {$pInfo['t_name']}
                                                                    WHERE {$pInfo['r_id']} = {$argRoomId}
                                                                    AND {$pInfo['u_id']} <> (SELECT {$rInfo['manager']}
                                                                                             FROM {$rInfo['t_name']}
                                                                                             WHERE {$rInfo['id']} = {$argRoomId})))";
        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception("select participant is failed");
            }

            // if 조회된 행이 없을 경우 then 종료
            if($query->num_rows <= 0) {
                return false;
            } else {
                return $query->fetch_assoc();
            }
        } catch(Exception $e) {
            return null;
        }
    }

    // 04-01-09. 조회 - 현재 DB에 등록된 세션 id 조회
    function select_sessionId($argUserId) {
        // 01. SQL 질의문
        $uInfo = DBInfo::USER_INFO;
        $sql = "SELECT {$uInfo['s_id']}
                FROM {$uInfo['t_name']}
                WHERE {$uInfo['id']} = '{$argUserId}'";

        try {
            // 02. 질의
            if (($query = $this->con->query($sql)) === false) {
                throw new Exception("select session id is failed.", 204010901);
            }

            // if 조회된 행이 없으면 then 질의 실패
            if($query->num_rows <= 0) {
                return false;
            }

            // 03. 질의 결과 반환
            return $query->fetch_row()[0];
        } catch(Exception $e) {
            return null;
        }
    }

    // 04-02-01. 삽입 - 방 정보 입력
    function insert_room($argName, $argUserId) {
        // 01. SQL 구문 작성 - 방 등록
        $tableInfo  = DBInfo::CHAT_ROOM;

        $sql = "INSERT INTO {$tableInfo['t_name']}({$tableInfo['name']}, {$tableInfo['manager']})
                VALUES('{$argName}', '{$argUserId}')";

        try {
            // 02. 질의
            if (($query = $this->con->query($sql)) == false) {
                throw new Exception("insert room is failed.");
            }

            // 생성된 방 번호
            $room_id = $this->con->insert_id;

            // 03. 해당 방의 방장을 채팅방 참가 인원으로 추가
            if($this->insert_participant($argUserId, $this->con->insert_id) == false) {
                throw new Exception('add participant is failed.');
            }

            return $room_id;
        } catch(Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    // 04-02-02. 삽입 - 참가자 정보 입력
    function insert_participant($argUserId, $argRoomId) {
        try {
            // 01. 입장 채팅 입력
            if($this->insert_message($argUserId, $argRoomId, "SYSTEM: 입장하셨습니다.") == false) {
                throw new Exception("input participate message is failed.");
            }

            // 02. SQL 구문 작성
            $tableInfo  = DBInfo::CHAT_PARTICIPANT;

            $sql = "INSERT INTO {$tableInfo['t_name']}({$tableInfo['r_id']}, {$tableInfo['u_id']}, {$tableInfo['s_chat']})
                    VALUES($argRoomId, '{$argUserId}', {$this->con->insert_id})";

            // 03. 질의
            if (($query = $this->con->query($sql)) == false) {
                throw new Exception($sql);
            }

            return true;
        } catch(Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    // 04-02-03. 삽입 - 채팅 메시지 입력
    function insert_message($argUserId, $argRoomId, $argMessage) {
        // 01. sql 구문 작성
        $tableInfo  = DBInfo::CHAT_MESSAGE;

        $sql = "INSERT	INTO {$tableInfo['t_name']}({$tableInfo['u_id']}, {$tableInfo['r_id']}, {$tableInfo['con']})
                VALUES('{$argUserId}', {$argRoomId}, '{$argMessage}')";

        // 02. 질의
        try {
            if(($query = $this->con->query($sql)) == false) {
                throw new Exception("input message is failed.");
            }

            return true;
        } catch(Exception $e) {
            echo "<script>alert('{$e->getMessage()}')</script>";
            return false;
        }
    }

    // 04-02-04. 삽입 - 회원가입
    function insert_join($argUserId, $argPassword, $argName) {
        // 01. SQL 구문 작성
        $tableInfo = DBInfo::USER_INFO;
        $sql = "INSERT INTO {$tableInfo['t_name']}
                VALUES('{$argUserId}', '{$argPassword}', '$argName')";

        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception('insert user info is failed.');
            }

            // 03. 결과 반환
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    // 04-03-01. 갱신 - 방장 교체
    function update_newManager($argRoomId, $argNewManagerId) {
        // 01. SQL 구문 설정
        $rInfo = DBInfo::CHAT_ROOM;
        $sql = "UPDATE {$rInfo['t_name']}
                SET {$rInfo['manager']} = '{$argNewManagerId}'
                WHERE {$rInfo['id']} = {$argRoomId}";

        try {
            // 02. 질의 실행
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception("update manager is failed.");
            }

            if($this->con->affected_rows <= 0) {
                return false;
            }

            return true;
        } catch(Exception $e) {
            return null;
        }
    }

    // 04-03-02. 갱신 - 현재 접속 유저의 SESSION ID 입력
    function update_sessionId($argUserId, $argSessId) {
        // 01. SQL 구문 설정
        $uInfo = DBInfo::USER_INFO;
        $sql = "UPDATE {$uInfo['t_name']}
                SET {$uInfo['s_id']} = '{$argSessId}'
                WHERE {$uInfo['id']} = '{$argUserId}'";

        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception("update session id is failed.", 204030201);
            }

            // if 갱신이 이루어진 행이 없다면 then 질의 실패
            if($this->con->affected_rows <= 0) {
                throw new Exception("updated data is not exists.", 204030202);
            }

            // 03. 결과 반환
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    // 04-04-01. 삭제 - 방 삭제
    function delete_room($argRoomId) {
        // 01. SQL 구문 작성
        $tableInfo = DBInfo::CHAT_ROOM;
        $sql = "DELETE FROM {$tableInfo['t_name']}
                WHERE {$tableInfo['id']} = {$argRoomId}";

        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception("query is failed.", 0204040101);
            }

            // 03. 삭제 행의 유무 검사
            if($this->con->affected_rows <= 0) {
                throw new Exception("delete room is failed.", 0204040102);
            }

            return true;
        } catch(Exception $e) {
            return null;
        }
    }

    // 04-04-02. 삭제 - 참가자 목록 삭제
    function delete_participant($argRoomId, $argUserId) {
        // 01. SQL 구문 작성
        $tableInfo = DBInfo::CHAT_PARTICIPANT;
        $sql = "DELETE FROM {$tableInfo['t_name']}
                WHERE {$tableInfo['r_id']} = {$argRoomId}
                AND {$tableInfo['u_id']} = '{$argUserId}'";

        try {
            // 02. 질의
            if(($query = $this->con->query($sql)) === false) {
                throw new Exception("query is failed.", 0204040201);
            }

            // 03. 삭제 행의 유무 검사
            if($this->con->affected_rows <= 0) {
                throw new Exception("delete participant is failed.", 0204040202);
            }

            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}
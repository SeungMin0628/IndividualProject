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

include_once("./model/ChattingSystem_m.php");
include_once('./control/SessionManager_c.php');

class ChattingSystem_c {
    // 01. 멤버 변수 선언
    private $model;
    private $sessionManager;

    // 02. 생성자 정의
    function __construct() {
        $this->model            = new ChattingSystem_m();
        $this->sessionManager   = new SessionManager_c();

        // 세션을 검사하여 중복 로그인 여부를 조회
        try {
            if($this->sessionManager->check()) {
                $id = $this->sessionManager->getUserInfo()['id'];

                // if 세션 ID 조회에 실패했을 경우 then 오류
                if(($sessId = $this->model->select_sessionId($id)) == false) {
                    throw new Exception("내부 오류!", 101);
                } else if ($sessId != session_id()) {
                    // if DB에 등록된 session id와 현재 session id가 다를 경우 then 중복 로그인 감지 -> 기존 사용자 추방
                    $this->sessionManager->destroy();
                    throw new Exception('이 계정의 새로운 사용자가 감지되었습니다. 로그아웃 처리하겠습니다.');
                }
            }
        } catch(Exception $e) {
            $message = "메시지: ".$e->getMessage();
            if($e->getCode() != 0) $message .= ", 코드: {$e->getCode()}";
            echo "<script>
                      alert('{$message}');
                      location.replace('/web/chat/index.php');
                  </script>";
            exit();
        }
    }

    // 03. 기능 정의
    // 03-01-01. 회원관리 - 로그인 기능
    function login() {
        if(sizeof($_POST) > 0) {
            // if POST 데이터의 길이가 0보다 길면(수신 받은 정보가 있으면) then 로그인 기능 실행
            try {
                // 01. 데이터 수신
                $id = isset($_POST['id']) ? $_POST['id'] : null;
                $pw = isset($_POST['password']) ? $_POST['password'] : null;

                // 02. 수신된 데이터를 통한 DB 검색
                if (($user_info = $this->model->select_userInfo($id)) === false) {
                    throw new Exception("ID 혹은 비밀번호를 다시 확인하세요.");
                }

                if ($pw != $user_info->password) {
                    throw new Exception("ID 혹은 비밀번호를 다시 확인하세요.");
                }

                // 03. 세션에 유저 정보 추가
                $this->sessionManager->start($user_info);

                // 04. DB에 생성된 세션의 ID 등록
                $this->model->update_sessionId($id, session_id());

                // 05. 채팅방 목록 출력
                echo "<script>location.replace('../index.php/showList/0')</script>";
            } catch (Exception $e) {
                echo "<script>
                    alert('{$e->getMessage()}');
                    location.replace('../index.php');
                  </script>";
            }
        } else {
            // 수신받은 데이터 없으면 로그아웃 기능 실행
            try {
                // 01. 세션에 유저 정보를 제거하고 세션을 종료
                $this->sessionManager->destroy();

                // 02. 세션 상태가 정상적으로 종료된 상태면 로그인 페이지로 돌아가기
                if($this->sessionManager->check()) {
                    throw new Exception("session is not destroyed");
                }

                echo "<script>
                        alert('로그아웃 되었습니다.');
                        location.replace('../index.php');
                      </script>";
            } catch(Exception $e) {
                echo $e->getMessage();
                exit();
            }
        }
    }

    // 03-01-02. 회원가입
    function join() {
        if(sizeof($_POST) > 0) {
            try {
                // 01. 변수 설정
                $id     = isset($_POST['id']) ? $_POST['id'] : null;
                $pw     = isset($_POST['password']) ? $_POST['password'] : null;
                $name   = isset($_POST['name']) ? $_POST['name'] : null;

                // 02. 입력값 형식 검사
                $regex = "/^[a-zA-Z0-9]{1,}/";
                if(!preg_match($regex, $id)) {
                    throw new Exception("잘못된 형식 입력!");
                }

                if(!preg_match($regex, $pw)) {
                    throw new Exception("잘못된 형식 입력!");
                }

                $regex  = "/^[a-zA-Z0-9가-힣]{1,}/";
                if(!preg_match($regex, $name)) {
                    throw new Exception("잘못된 형식 입력!");
                }

                // 03. 해당 아이디가 등록되었는지 확인
                if(($check = $this->model->select_userInfo($id)) !== false) {
                    throw new Exception("이미 존재하는 ID입니다!");
                } else if (is_null($check)) {
                    throw new Exception('오류: 내부 오류!', 1030102);
                }

                // 04. 아이디를 등록하고, 메인 페이지로 돌아가기
                if(($this->model->insert_join($id, $pw, $name)) == false) {
                    throw new Exception("오류 : 회원가입 실패!", 1030103);
                }

                echo "<script>
                        alert('회원가입 성공!');
                        location.replace('../index.php');
                      </script>";
            } catch(Exception $e) {
                $message = $e->getMessage();
                if($e->getCode() != 0) $message .= ", 코드: {$e->getCode()}";
                echo "<script>
                        alert('{$message}');
                        location.replace('../index.php/join');
                      </script>";
            }
        } else {
            include_once('./view/join.php');
        }
    }

    // 03-02. 채팅방 목록 출력
    function showList($argPage) {
        // if 전달받은 매개인자 없을 경우 then 0번 목록 출력으로 재접속
        if(func_num_args() <= 0) {
            echo "<script>location.replace('./showList/0')</script>";
        }

        try {
            // 01. 변수 설정
            $userInfo   = $this->sessionManager->getUserInfo();
            $nowPage    = sizeof($argPage) > 0 ? intval($argPage) : 0;

            if($userInfo === false) {
                throw new Exception("search user info is failed!");
            }

            // 02. DB에서 현재 개설된 채팅방 정보 가져오기
            if(($roomList = $this->model->select_roomList($nowPage)) == false) {
                throw new Exception("잘못된 접근!");
            }

            // 02-01. 방 정보 목록의 방 제목 string 디코딩하기
            foreach($roomList as $value) {
                $value[DBInfo::CHAT_ROOM['n_alias']] = $this->decodeString($value[DBInfo::CHAT_ROOM['n_alias']]);
            }

            // 03. 최대 페이지 개수 가져오기
            $maxPage = $this->model->select_maxPage();

            include_once('./view/list.php');
        } catch(Exception $e) {
            echo "<script>
                    alert('{$e->getMessage()}');
                    location.replace('.');
                  </script>";
        }
    }

    // 03-03. 채팅방 개설
    function createRoom() {
        if(sizeof($_POST) > 0) {
            // if 전송받은 데이터가 있다면 then DB 접속을 통한 채팅방 생성
            try {
                // 01. 데이터 수신
                $name = isset($_POST['name']) ? $this->encodeString($_POST['name']) : null;

                if(is_null($name)) {
                    throw new Exception("name is not exists");
                }

                // 02. 유저 정보 가져오기
                $userInfo = $this->sessionManager->getUserInfo();

                // 03. 채팅방을 등록하기
                $roomId = $this->model->insert_room($name, $userInfo['id']);

                echo "<script>alert('채팅방 생성 성공!');location.replace('./accessRoom/{$roomId}')</script>";
            } catch(Exception $e) {
                exit();
            }
        } else {
            // else 채팅방 생성 페이지에 접속
            include_once("./view/create_room.php");
        }
    }

    // 03-04. 채팅방 접속
    function accessRoom($argRoomId) {
        try {
            // 01. 변수 설정
            $userId = $this->sessionManager->getUserInfo()['id'];

            // 02. 방 정보를 조회
            if((@$roomInfo = $this->model->select_roomInfo($argRoomId)) == false) {
                throw new Exception("존재하지 않는 채팅방입니다");
            }

            // 03. if 현재 사용자가 해당 채팅방에 참가하고 있지 않다면 then 참가 구문 실행
            if(($isParticipate = $this->model->select_isParticipate($argRoomId, $userId)) === false) {
                $this->model->insert_participant($this->sessionManager->getUserInfo()['id'], $argRoomId);
            } else if(is_null($isParticipate)) {
                throw new Exception('내부 오류!', 10304);
            }

            include_once("./view/chatting_room.php");
        } catch(Exception $e) {
            $message = $e->getMessage();
            $e->getCode() != 0 ? $message .= ", 코드: {$e->getCode()}" : null;
            echo "<script>
                    alert('메시지: {$message}');
                    location.replace('../../index.php');
                  </script>";

        }
    }

    // 03-05. 채팅 메시지 받기
    function getMessage() {
        try {
            // 01. 변수 설정
            $recentMessage  = isset($_POST['recent']) ? $_POST['recent'] : null;
            $roomId         = isset($_POST['id']) ? $_POST['id'] : null;

            // 02. 채팅방이 존재하는지 조회
            if(($this->model->select_roomInfo($roomId)) === false) {
                throw new Exception("no room");
            }

            // 03. 채팅 메시지 목록 조회
            $messageList = $this->model->select_message($this->sessionManager->getUserInfo()['id'], $roomId, $recentMessage);

            // if 최근 메시지가 없다면 then 종료
            if(sizeof($messageList) <= 0) {
                return;
            }

            // 03-01. 채팅 메시지 내용 string 디코딩하기
            foreach($messageList as $value) {
                $value[DBInfo::CHAT_MESSAGE['con']] = $this->decodeString($value[DBInfo::CHAT_MESSAGE['con']]);
            }

            // final. 채팅방 목록 반환
            echo json_encode($messageList);
        } catch(Exception $e) {
            echo 'false';
        }
    }

    // 03-06. 채팅 메시지 등록
    function sendMessage() {
        try {
            // 01. 변수 설정
            $roomId     = isset($_POST['id']) ? $_POST['id'] : null;
            $message    = isset($_POST['message']) ? $this->encodeString($_POST['message']) : null;
            $userId     = $this->sessionManager->getUserInfo()['id'];

            // 02. 채팅 메시지 등록
            if(($this->model->insert_message($userId, $roomId, $message)) == false) {
                throw new Exception("insert chatting message is failed.");
            }

            // final. 결과 반환
            echo 1;
        } catch (Exception $e) {
            echo "<script>alert('{$e->getMessage()}')</script>";
        }
    }

    // 03-07. 채팅방 나가기
    function exitRoom($argRoomId) {
        try {
            // 01. 변수 설정
            $userId = $this->sessionManager->getUserInfo()['id'];

            // 02. 현재 참가자가 해당 채팅방의 참가자인지 확인
            if(($isParticipant = $this->model->select_isParticipate($argRoomId, $userId)) === false) {
                // if 현재 채팅방 참가자가 아니라면 then 목록으로 튕겨내기
                throw new Exception('올바르지 못한 접근! 목록으로 돌아갑니다.');
            } else if (is_null($isParticipant)) {
                throw new Exception('내부 오류!', 1030701);
            }

            // 03. 참가자 방장 여부를 조회
            if(($isManager = $this->model->select_isManager($argRoomId, $userId)) === true) {
                // if 현재 사용자가 방장이라면 then 현재 방 사용 인원 검사
                if(is_array($newManager = $this->model->select_newManager($argRoomId))) {
                    // if 현재 나가는 방장 제외 인원이 있다면 then 방장 교체
                    $this->model->update_newManager($argRoomId, $newManager[DBInfo::USER_INFO['id']]);
                    $this->model->insert_message($userId, $argRoomId, "SYSTEM: 방장을 {$newManager[DBInfo::USER_INFO['name']]}님으로 바꿉니다.");
                } else if($newManager === false) {
                    // else if 현재 나가는 방장이 마지막 인원이라면 then 방 삭제
                    if($this->model->delete_room($argRoomId) == false) {
                        throw new Exception("삭제 실패!", 01030704);
                    }
                } else if(is_null($newManager)) {
                    // else 갱신 질의문 실패...
                    throw new Exception('내부 오류!', 1030702);
                }
            } else if (is_null($isManager)) {
                throw new Exception('내부 오류!', 1030703);
            }

            // if newManager 변수가 존재하고(방장이 나갈 때) 해당 변수가 false라면(방이 삭제되었음) then 함수 종료
            if(isset($newManager)) {
                if($newManager === false) {
                    return;
                }
            }

            // 04. 사용자를 참가자 목록에서 삭제
            if (($this->model->delete_participant($argRoomId, $userId)) === false) {
                throw new Exception("나가기 실패!", 1030704);
            }

            // 05. 작별 메시지 삽입
            $this->model->insert_message($userId, $argRoomId, "SYSTEM: 퇴장하셨습니다.");
        } catch(Exception $e) {
            echo "<script>
                    alert('메시지: {$e->getMessage()}, 코드: {$e->getCode()}');
                  </script>";
        } finally {
            // 메인 페이지로 돌아가기
            echo "<script>location.replace('../../index.php');</script>";
        }
    }

    // 03-08. 삭제 권한 확인
    function checkDeletionAuthority() {
        try {
            // 01. 변수 설정
            $roomId = isset($_POST['id']) ? $_POST['id'] : null;
            $userId = $this->sessionManager->getUserInfo()['id'];

            // 02. 방장 여부 조회
            if(($authority = $this->model->select_isManager($roomId, $userId)) === true) {
                echo 1;
            } else if(is_null($authority)) {
                throw new Exception("내부 오류!", 1030801);
            }
        } catch(Exception $e) {
        }
    }

    // 03-09. 방 삭제
    function deleteRoom($argRoomId) {
        try {
            // 01. 변수 설정
            $userId = $this->sessionManager->getUserInfo()['id'];

            // 02. 권한 확인
            if(($authority = $this->model->select_isManager($argRoomId, $userId)) === false) {
                throw new Exception("삭제 권한이 없습니다!");
            } else if(is_null($authority)) {
                throw new Exception("올바르지 못한 접근!");
            }

            // 03. 방 삭제
            if(($this->model->delete_room($argRoomId)) === true) {
                echo "<script>alert('채팅방을 삭제하였습니다.')</script>";
                return;
            } else {
                throw new Exception("삭제 실패!");
            }
        } catch(Exception $e) {
            echo "<script>alert('{$e->getMessage()}');</script>";
        } finally {
            echo "<script>location.replace('../../index.php');</script>";
        }
    }

    // 04. 보조 기능
    // 04-01. 문자열 인코딩
    function encodeString($argString) {
        return nl2br(str_replace(' ', '&nbsp;', htmlentities($argString, ENT_QUOTES)));
    }

    // 04-02. 문자열 디코딩
    function decodeString($argString) {
        return html_entity_decode(str_replace('&nbsp;', ' ', $argString), ENT_QUOTES);
    }
}
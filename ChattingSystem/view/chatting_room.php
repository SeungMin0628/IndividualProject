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
 * Time: 오후 9:07
 */

    include_once "./model/DBInfo.php";
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title><?php echo $argRoomId ?>번 채팅방</title>
        </head>
        <style>
            .chat_main {
                margin-top:                 2em;
                margin-right:               auto;
                margin-left:                auto;
                width:                      500px;
            }

            .chat_title {
                border:                     none;
                background:                 #3F51B5;
                height:                     50px;
                line-height:                inherit;
                font-size:                  large;
                color:                      #E8EAF6;
                text-align:                 left;
                padding-left:               1em;
            }

            .chat_control_line {
                height:                     40px;
                padding:                    0em 1em;
                line-height:                inherit;
                border-style:               double;
                border-width:               3px 0px;
                border-color:               #3F51B5;
            }

            .chat_input_message_text {
                border:                     none;
                width:                      400px;
                height:                     30px;
                padding-left:               0.5em;
                background-color:           #E8EAF6;
                color:                      black;
                font-size:                  large;
            }

            .chat_input_message_text:focus {
                background-color:           #5C6BC0;
                color:                      white;
            }

            .chat_input_message_button {
                border:                     none;
                width:                      70px;
                height:                     30px;
                background-color:           #7986CB;
                color:                      white;
                font-weight:                bold;
            }

            .chat_input_message_button:hover {
                background-color:           #C5CAE9;
                color:                      black;
                cursor:                     pointer;
            }

            .chat_input_message_button:active {
                background-color:           #3949AB;
                color:                      white;
            }

            #message_list {
                height:                     500px;
                max-height:                 500px;
                vertical-align:             top;
                overflow-y:                 auto;
            }

            .chat_message_body {
                min-width:                  300px;
                margin:                     0.2em 0em;
                display:                    block;
                font-size:                  small;
            }

            .chat_message_writer {
                float:                      right;
            }

            .chat_message_other {
                float:                      left;
            }

            .chat_message_name {
                font-weight:                bold;
            }

            .chat_message_content {
                width:                      fit-content;
                max-width:                  350px;
                padding:                    0.4em 1em;
                border-radius:              1em;
                word-wrap:                  break-word;
            }

            .chat_message_content_writer {
                float:                      right;
                background-color:           #9FA8DA;
            }

            .chat_message_content_other {
                float:                      left;
                background-color:           #EEEEEE;
            }

            #list {
                float:                      left;
            }

            #list:hover {
                background-color:           #7986CB;
            }

            #exit {
                float:                      right;
            }

            #manager {
                float:                      left;
                color:                      #3F51B5;
                font-weight:                bold;
            }

            .aButton {
                border-radius:              0.5em;
                padding:                    0.2em 0.5em;
                margin:                     0em 0.5em;
                background-color:           #303F9F;
                color:                      white;
                text-decoration:            none;
            }

            .aButton:hover {
                background-color:           #F44336;
            }

            .aButton:active {
                background-color:           #C5CAE9;
            }
        </style>
    <body>
        <table class="chat_main">
            <thead>
                <tr>
                    <td class="chat_title"><?php echo "{$roomInfo[DBInfo::CHAT_ROOM['n_alias']]}"; ?></td>
                </tr>
                <tr>
                    <td id='manager_controlLine' class="chat_control_line">
                        <div id="manager"> 방장 : <?php echo "{$roomInfo[DBInfo::USER_INFO['n_alias']]}"; ?></div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div id="message_list">
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align:center;">
                        <input type="text" id="user_input" class="chat_input_message_text">
                        <input type="button" id="send_message" value="전송" class="chat_input_message_button">
                    </td>
                </tr>
                <tr>
                    <td class="chat_control_line">
                        <a id="list" class="aButton" href="../..">목록보기</a>
                        <a id="exit" class="aButton" href="../../index.php/exitRoom/<?php echo $argRoomId ?>">채팅방 나가기</a>
                    </td>
                </tr>
            </tfoot>
        </table>
        <script language="JavaScript">
            // 01. 채팅 메시지 수신 기능
            function receive_chat() {
                // 01. 통신 준비
                var ajax = new XMLHttpRequest();                                // 데이터 송수신 객체
                var url = "../getMessage";                                      // 통신 URL
                var send = 'id=<?php echo $argRoomId ?>&recent=-1';             // 송신 데이터

                // 02. 통신 구문
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4 && ajax.status == 200) {
                        if(ajax.responseText == 'false') {
                            alert('채팅방이 삭제되었습니다!');
                            location.replace('../..');
                        } else if (ajax.responseText != '') {
                            // 01. 변수 설정
                            var chatMessage     = JSON.parse(ajax.responseText);
                            var contentsBody    = document.getElementById('message_list');

                            // 02. 메시지 말풍선 추가
                            for(var value of chatMessage) {
                                contentsBody.appendChild(makeMessage(value['name'], value['contents'], value['isWriter']));
                            }

                            // 03. 최근 채팅 번호 갱신
                            var recent = chatMessage[chatMessage.length - 1]['id'];
                            send = 'id=<?php echo $argRoomId ?>&recent=' + recent;

                            // 04. 스크롤 당기기
                            contentsBody.scrollTop = contentsBody.scrollHeight;
                        }
                    }
                };

                // 03. 통신 실행
                return function () {
                    ajax.open("POST", url, true);
                    ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    ajax.send(send);
                };
            }

            // 02. 메시지 송신 기능
            function sendMessage() {
                // 01. 변수 설정
                var message = document.getElementById('user_input');
                if(message.value.trim() == '') {
                    alert('메시지를 입력하세요.');
                    return;
                }

                // 01. 통신 준비
                var ajax = new XMLHttpRequest();                                                        // 데이터 송수신 객체
                var url = "../sendMessage";                                                             // 통신 URL
                var send = 'id=<?php echo $argRoomId ?>&message=' + encodeURIComponent(message.value);  // 송신 데이터

                // 02. 통신 구문
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4 && ajax.status == 200) {
                        if (ajax.responseText == '1') {
                            receive();
                            message.value = '';
                        } else {
                            alert('전송 실패!');
                        }
                    }
                };

                // 03. 통신 실행
                ajax.open("POST", url, true);
                ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                ajax.send(send);
            }

            // 03. 말풍선 만들기
            function makeMessage(argUserName, argMessage, isWriter) {
                // 01. 변수 설정
                var messageDiv  = document.createElement('div');    // 메시지 전체 div
                var contentsDiv = document.createElement('div');    // 메시지 내용 div

                // 02. 내용 설정
                contentsDiv.innerHTML = argMessage;
                messageDiv.classList.add('chat_message_body');
                contentsDiv.classList.add('chat_message_content');

                if(isWriter == '1') {
                    messageDiv.classList.add('chat_message_writer');
                    contentsDiv.classList.add('chat_message_content_writer');
                } else {
                    // 메시지 전송한 사람의 이름 붙이기
                    var nameDiv     = document.createElement('div');    // 메시지를 전송한 사람 이름 div
                    nameDiv.innerHTML = argUserName;
                    nameDiv.classList.add('chat_message_name');
                    messageDiv.appendChild(nameDiv);
                    messageDiv.classList.add('chat_message_other');
                    contentsDiv.classList.add('chat_message_content_other');
                }

                messageDiv.appendChild(contentsDiv);

                // final. 말풍선 div 반환
                return messageDiv;
            }

            // 04. 삭제 권한 확인
            (function() {
                // 01. 통신 준비
                var ajax = new XMLHttpRequest();                                                        // 데이터 송수신 객체
                var url = "../checkDeletionAuthority";                                                  // 통신 URL
                var send = 'id=<?php echo $argRoomId ?>';                                              // 송신 데이터

                // 02. 통신 구문
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4 && ajax.status == 200) {
                        // 삭제 버튼 추가
                        if (ajax.responseText == '1') {
                            var deleteButton = document.createElement('a');
                            deleteButton.setAttribute('href', '../../index.php/deleteRoom/<?php echo $argRoomId ?>');
                            deleteButton.innerHTML = '방 삭제';
                            deleteButton.classList.add('aButton');
                            document.getElementById('manager_controlLine').appendChild(deleteButton);
                        }
                    }
                };

                // 03. 통신 실행
                ajax.open("POST", url, true);
                ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                ajax.send(send);
            })();

            // 채팅 수신 기능 할당 & interval 실행
            var receive = receive_chat();
            receive();
            setInterval(function () {
                receive();
            }, 1000);

            // 채팅 수신 이벤트 추가 (전송 버튼 클릭 & 메시지 입력창에서 엔터 입력)
            document.getElementById('send_message').addEventListener('click', sendMessage);

            document.getElementById('user_input').addEventListener('keypress', function(event) {
                if(event.keyCode == 0x0D) {
                    sendMessage();
                }
            });
        </script>
    </body>
</html>

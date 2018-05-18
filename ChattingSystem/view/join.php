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
 * Date: 2017-12-12
 * Time: 오전 10:56
 */
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>회원가입</title>
        <style>
            /* CSS Style sheet*/
            .join_form {
                margin:                     1em auto auto;
            }

            .title_line {
                border-width:               3px 0px;
                border-style:               double;
                border-color:               #3F51B5;
                padding:                    0.3em 1em;
                font-size:                  x-large;
                color:                      #3F51B5;
            }

            .column_name {
                font-size:                  large;
                padding:                    0.3em;
                text-align:                 center;
                color:                      #212121;
            }

            .input_text {
                border:                     1px solid #3F51B5;
                border-radius:              0.2em;
                padding:                    0.5em 0.2em;
                margin:                     0.3em 0.5em;
                font-size:                  large;
                color:                      #212121;
            }

            .input_text:focus {
                background-color:           #C5CAE9;
            }

            .notification {
                border-top:                 1px solid #3F51B5;
                padding:                    0.5em 1em;
                font-size:                  small;
                color:                      #212121;
            }

            .button {
                border:                     none;
                border-radius:              0.5em;
                background-color:           #3949AB;
                padding:                    0.2em 0.5em;
                float:                      left;
                font-size:                  large;
                text-decoration:            none;
                color:                      white;
                user-select:                none;
            }

            .button:hover {
                cursor:                     pointer;
                background-color:           #8C9EFF;
                color:                      #212121;
            }

            .button:active {
                background-color:           #283593;
                color:                      white;
            }

            .back_button {
                float:                      left;
            }

            .submit_button {
                float:                      right;
            }

            .correct_input {
                background-color:           #8C9EFF;
            }

            .wrong_input {
                background-color:           #FF8A80;
            }
        </style>
    </head>
    <body>
        <form action="./join" method="post" name="joinForm">
            <table class="join_form">
                <tr>
                    <th colspan="2" class="title_line">회원가입</th>
                </tr>
                <tr>
                    <td class="column_name">*아이디</td>
                    <td><input type="text" class="input_text" id='id_input' name="id" size="35" maxlength="30"
                               placeholder="영어 대소문자, 숫자만 사용 가능" required></td>
                </tr>
                <tr>
                    <td class="column_name">*비밀번호</td>
                    <td><input type="password" class="input_text" id='pw_input' name="password" size="35" maxlength="30"
                               placeholder="영어 대소문자, 숫자만 사용 가능" required></td>
                </tr>
                <tr>
                    <td class="column_name">*비밀번호 확인</td>
                    <td><input type="password" class="input_text" id='check_pw' name="check_password" size="35" maxlength="30"
                               placeholder="위의 비밀번호를 한 번 더 입력" required></td>
                </tr>
                <tr>
                    <td class="column_name">이름</td>
                    <td><input type="text" class="input_text" id='name_input' name="name" size="35" maxlength="30"
                               placeholder="영어 대소문자, 숫자, 자모음 제외 한글"></td>
                </tr>
                <tr>
                    <td colspan="2" class="notification">* 항목은 필수 입력칸입니다.</td>
                </tr>
                <tr>
                    <td colspan="2" class="title_line">
                        <a class="back_button button" href="../index.php">뒤로</a>
                        <input class="submit_button button" type="submit" value="회원가입">
                    </td>
                </tr>
            </table>
        </form>
        <script language="JavaScript">
            // 01. 정규 표현식을 이용한 검사
            function checkString(argString, argRegex) {
                var regex = new RegExp(argRegex);
                return regex.test(argString);
            }

            // submit 이벤트 수정
            document.joinForm.onsubmit = function(event) {
                // 01. 변수 목록
                var id          = document.getElementById('id_input');
                var pw          = document.getElementById('pw_input');
                var check_pw    = document.getElementById('check_pw');
                var name        = document.getElementById('name_input');

                try {
                    // 02. 입력 양식 검사
                    if (!checkString(id.value, "^([a-zA-Z0-9]{1,})$")) {
                        throw "";
                    }

                    if (!checkString(pw.value, "^([a-zA-Z0-9]{1,})$")) {
                        throw "";
                    }

                    if (!checkString(check_pw.value, "^([a-zA-Z0-9]{1,})$") || check_pw.value != pw.value) {
                        throw "";
                    }

                    if (name.value.trim() == '') {
                        name.value = id.value;
                    } else if(!checkString(event.target.value, "^([a-zA-Z0-9가-힣]{1,})$")) {
                        throw "";
                    }

                    // 03. 암호 확인칸 비활성화
                    check_pw.setAttribute('disabled', 'disabled');
                } catch(e) {
                    alert('회원가입 양식에 맞게 입력하세요.');
                    event.preventDefault();
                    return false;
                }
            }

            // id 검사
            document.getElementById('id_input').addEventListener('blur', function(event) {
                if(checkString(event.target.value, "^([a-zA-Z0-9]{1,})$")) {
                    event.target.classList.remove('wrong_input');
                    event.target.classList.add('correct_input');
                } else {
                    event.target.classList.add('wrong_input');
                    event.target.classList.remove('correct_input');
                }
            });

            // pw 검사
            document.getElementById('pw_input').addEventListener('blur', function(event) {
                if(checkString(event.target.value, "^([a-zA-Z0-9]{1,})$")) {
                    event.target.classList.remove('wrong_input');
                    event.target.classList.add('correct_input');
                } else {
                    event.target.classList.add('wrong_input');
                    event.target.classList.remove('correct_input');
                }
            });

            // pw 재확인
            document.getElementById('check_pw').addEventListener('blur', function(event) {
                if(event.target.value == document.getElementById('pw_input').value && checkString(event.target.value, "^([a-zA-Z0-9]{1,})$")) {
                    event.target.classList.remove('wrong_input');
                    event.target.classList.add('correct_input');
                } else {
                    event.target.classList.add('wrong_input');
                    event.target.classList.remove('correct_input');
                }
            });

            // 이름 검사
            document.getElementById('name_input').addEventListener('blur', function(event) {
                if(checkString(event.target.value, "^([a-zA-Z0-9가-힣]{1,})$") || event.target.value.trim() == '') {
                    event.target.classList.remove('wrong_input');
                    event.target.classList.add('correct_input');
                } else {
                    event.target.classList.add('wrong_input');
                    event.target.classList.remove('correct_input');
                }
            });
        </script>
    </body>
</html>
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
     * Time: 오후 9:05
     */
    include_once('./control/SessionManager_c.php');

    $sessionManager = new SessionManager_c();

    if($sessionManager->check()) {
        echo "<script>location.replace('./index.php/showList/0')</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>채팅 시스템</title>
        <style>
            /* CSS style sheet */
            .login_form {
                margin-top:             2em;
                margin-left:            auto;
                margin-right:           auto;
                text-align:             center;
            }

            .control_line {
                border-width:           3px 0px;
                border-style:           double;
                border-color:           #3F51B5;
                padding:                0.5em 1em;
                color:                  #3F51B5;
                font-size:              x-large;
            }

            .login_input_text {
                border:                 1px solid #3F51B5;
                border-radius:          0.2em;
                padding:                0.2em 0.5em;
                margin:                 0.5em 0em;
                font-size:              large;
            }

            .input_title {
                font-size:              large;
                color:                  #3F51B5;
            }

            .button {
                border:                 none;
                border-radius:          0.5em;
                margin:                 0em 1em;
                padding:                0.2em 0.5em;
                background-color:       #3F51B5;
                font-size:              large;
                color:                  white;
                text-decoration:        none;
                user-select:            none;
            }

            .button:hover {
                cursor:                 pointer;
                background-color:       #C5CAE9;
                color:                  black;
            }

            .button:active {
                background-color:       #3949AB;
                color:                  white;
            }
        </style>
    </head>
    <body>
        <form action="./index.php/login" method="post" name="loginForm">
            <table class="login_form">
                <tr>
                    <th class="control_line" colspan="2">2-WDJ 1401213 이승민 - 채팅 시스템</th>
                </tr>
                <tr>
                    <td class="input_title">아이디</td>
                    <td><input class="login_input_text" type="text" name="id" size="30" maxlength="30"></td>
                </tr>
                <tr>
                    <td class="input_title">비밀번호</td>
                    <td><input class="login_input_text" type="password" name="password" size="30" maxlength="30"></td>
                </tr>
                <tr>
                    <td colspan="2" class="control_line">
                        <input class="button" type="submit" value="로그인">
                        <a class="button" href="index.php/join">회원가입</a>
                    </td>
                </tr>
            </table>
        </form>

        <script language="JavaScript">
            document.loginForm.onsubmit = function(event) {
                var idInput = document.getElementsByName('id')[0];
                var pwInput = document.getElementsByName('password')[0];

                if(idInput.value == '' || pwInput.value == '') {
                    alert('아이디와 비밀번호를 입력하세요.');
                    event.preventDefault();
                    return false;
                }
            }
        </script>
    </body>
</html>
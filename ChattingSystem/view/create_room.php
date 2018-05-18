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
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>채팅방 개설</title>
    </head>
    <style>
        /* CSS Style Sheet */
        .form_body {
            margin:                     1em auto;
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

        .button {
            border:                 none;
            border-radius:          0.5em;
            margin:                 0em 0.2em;
            padding:                0.5em;
            background-color:       #3F51B5;
            font-size:              large;
            color:                  white;
            text-decoration:        none;
            user-select:            none;
        }

        .submit:hover {
            cursor:                 pointer;
            background-color:       #C5CAE9;
            color:                  black;
        }

        .submit:active {
            background-color:       #3949AB;
            color:                  white;
        }

        .cancel:hover {
            background-color:       #F44336;
            color:                  white;
        }

        .cancel:active {
            background-color:       #D32F2F;
            color:                  white;
        }

        .form_title {
            font-size:              x-large;
            color:                  #3F51B5;
        }
    </style>
    <body>
        <table class="form_body">
            <tr><th class="form_title">채팅방 만들기</th></tr>
            <tr>
                <td>
                    <form action="../index.php/CreateRoom" method="post" name="createRoomForm">
                        <input class="input_text" type="text" name="name" maxlength="20">&nbsp;
                        <input class="button submit" type="submit" value="개설">
                        <a class="button cancel" href="../index.php">취소</a>
                    </form>
                </td>
            </tr>
        </table>
        <script language="JavaScript">
            document.createRoomForm.onsubmit = function(event) {
                var nameInput = document.getElementsByName('name')[0];

                try {
                    if (nameInput.value == '') {
                        throw "방 이름을 입력하세요.";
                    } else if(nameInput.value.length > 20) {
                        throw "방 이름이 너무 깁니다."
                    }
                } catch(e) {
                    alert(e);
                    event.preventDefault();
                    return false;
                }
            }
        </script>
    </body>
</html>
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

    include_once('./control/SessionManager_c.php');

    $sessionManager = new SessionManager_c();

    try {
        if (!$sessionManager->check()) {
            // if 로그인 안 한 상태에서 접근할 시 then 예외
            throw new Exception("비로그인 상태의 접근");
        }
    } catch(Exception $e) {
        echo "<script>
                alert('잘못된 접근입니다!');
                location.replace('../../index.php');
              </script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>채팅방 목록</title>
        <style>
            /* CSS Style list */
            .table_body {
                margin-top:                     0.5em;
                margin-left:                    auto;
                margin-right:                   auto;
                border-spacing:                 1em;
                text-align:                     center;
            }

            .list {
                border:                         3px dashed #3949AB;
                width:                          350px;
                padding:                        0em 0.5em;
                height:                         200px;
                max-height:                     200px;
                overflow-y:                     auto;
            }

            .user_info {
                border:                         3px solid #3949AB;
                border-radius:                  1em;
                width:                          150px;
                height:                         200px;
                font-size:                      small;
                text-align:                     center;
            }

            #profile {
                border:                         none;
                border-radius:                  100%;
                margin:                         1rem auto;
                width:                          100px;
                height:                         100px;
                background:                     #E8EAF6 url('../../view/source/default_profile.png');
                background-size:                contain;
            }

            .button {
                border:                         none;
                border-radius:                  0.5em;
                background-color:               #3949AB;
                padding:                        0.2em 0.5em;
                font-size:                      large;
                text-decoration:                none;
                color:                          white;
                user-select:                    none;
            }

            .button:hover {
                cursor:                         pointer;
                background-color:               #8C9EFF;
                color:                          #212121;
            }

            .button:active {
                background-color:               #283593;
                color:                          white;
            }

            .logout {
                margin:                         0.5rem auto;
            }

            .pagination {
                margin:                         0.2em 0.3em;
            }

            .now_page {
                border:                         none;
                border-radius:                  0.5em;
                background-color:               #8C9EFF;
                padding:                        0.2em 0.5em;
                font-size:                      large;
                text-decoration:                none;
                color:                          #212121;
                user-select:                    none;
            }

            .subtitle {
                display:                        table-cell;
                text-align:                     center;
            }

            .spacing {
                border-spacing:                 0px;
            }

            .list_title_line {
                border-width:                   3px 0px;
                border-style:                   double;
                border-color:                   #3949AB;
                padding:                        0.6em 1em;
            }

            .number {
                width:                          40px;
            }

            .room_title {
                width:                          300px;
            }

            .room_manager {
                width:                          200px;
            }

            .room_content {
                border-width:                   1px 0px;
                border-style:                   solid;
                border-color:                   #E0E0E0;
                height:                         2em;
                color:                          #212121;
            }

            .access_room {
                text-decoration:                none;
                font-weight:                    bold;
                color:                          #304FFE;
            }

            .access_room:hover {
                color:                          #8C9EFF;
            }

            .access_room:active {
                color:                          #1A237E;
            }
        </style>
    </head>
    <body>
        <table class="table_body">
            <tr>
                <td>
                    <div class="user_info">
                            <div id="profile"></div>
                            <?php echo $userInfo['name']?>님<br><br>
                            <a class="button logout" href="../login">로그아웃</a>
                    </div>
                </td>
                <td>
                    <!-- 현재 내가 참가한 방 -->
                    <div class="list"></div>
                </td>
                <td>
                    <!-- 현재 내가 방장인 방 -->
                    <div class="list"></div>
                </td>
            </tr>
        </table>
        <table class="table_body spacing">
            <thead>
                <tr>
                    <td class="list_title_line number">번호</td>
                    <td class="list_title_line room_title">채팅방 이름</td>
                    <td class="list_title_line room_manager">방장</td>
                    <td class="list_title_line number">인원</td>
                    <td class="list_title_line room_manager">개설일자</td>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($roomList != false) {
                        foreach($roomList as $value) {
                            echo "<tr>";
                            foreach($value as $deepKey => $deepValue) {
                                // if 현재 데이터가 채팅방 제목이라면 then 링크 추가
                                if($deepKey == DBInfo::CHAT_ROOM['n_alias']) {
                                    $roomId = DBInfo::CHAT_ROOM['id'];
                                    echo "<td class='room_content'><a class='access_room' href='../accessRoom/{$value[$roomId]}'>{$deepValue}</a></td>";
                                } else {
                                    echo "<td class='room_content'>{$deepValue}</td>";
                                }
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>현재 채팅방 정보가 없습니다.</td></tr>";
                    }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="list_title_line">
                        <?php
                            $startPage  = floor($nowPage / 5) * 5;
                            $endPage    = $startPage + 5 >= $maxPage ? $maxPage : $startPage + 5;

                            if($startPage > 0) {
                                $previousPage = $startPage - 1;
                                echo "<a class='button pagination' href='./{$previousPage}'>이전</a>";
                            }

                            for($iCount = $startPage; $iCount < $endPage; $iCount++) {
                                $page = $iCount + 1;
                                if($iCount == $nowPage) {
                                    echo "<b class='pagination now_page'>{$page}</b>";
                                } else {
                                    echo "<a class='button pagination' href='./{$iCount}'>{$page}</a>";
                                }
                            }

                            if($endPage < $maxPage) {
                                echo "<a class='button pagination' href='./{$endPage}'>다음</a>";
                            }
                        ?>
                    </td>
                    <td class="list_title_line"><a class='button' href="../createRoom">채팅방 개설</a></td>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
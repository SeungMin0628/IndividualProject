 ## 채팅 시스템 ##

만든이 : 2-WDJ 1401213 이승민
만든날 : 2017년 12월 5일

 ** 수정이 필요한 부분.
 
 - index.php
 $_SERVER['PHP_SELF']를 분석하여 URI 라우팅 기능을 제공합니다. 따라서 public_html 폴더에서의 index.php의 위치에 따라 수치를 조정할 필요가 있습니다.
 
 	@@@ source code

 => α = index.php에 접근하기 위해 필요한 디렉토리의 깊이입니다.
if(sizeof($access_root) < ★{α + 2}) {
    include_once('./view/login.php');
} else {
    include_once('./control/ChattingSystem_c.php');

    $control = new ChattingSystem_c();

    if(isset($access_root[★{α + 2}])) {
        $control->$access_root[★{α + 1}]($access_root[α + 2]);
    } else {
        $control->$access_root[★{α + 1}]();
    }
}

예) index.php의 위치가 public_html/directory01/directory02/index.php일 시 α = 3이고, 따라서 소스코드는 

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

가 되어야 합니다.

 - model/DBInfo.php
 데이터베이스 접속에 필요한 정보가 저장되는 파일입니다. DB접속에 필요한 url, id, password, database를 수정해야 합니다.
 
 - index.php를 제외한 모든 php 파일
 URL을 이용한 시스템 구성요소에 대한 접근을 막기 위해 모든 PHP파일의 최상단에서 $_SERVER['PHP_SELF']를 정규표현식을 이용해 검사하고 있습니다. URL에 index.php를 비롯한 하위 폴더들이 기록되어 있는지 검사하여 포함되지 않았을 경우 잘못된 접근으로 인식하고 접속을 차단합니다.

 		@@ source code
    if(!preg_match("★{URL of index.php}", $_SERVER['PHP_SELF'])) {
        echo "<script>
                    alert('잘못된 접근!');
                    history.back();
                  </script>";
        return;
    }

 URL of index.php는 index.php에 접근하기 위해 필요한 하위 디렉토리를 포함한 URL을 의미합니다.

 예) index.php의 위치가 public_html/directory01/directory02/index.php일 시

 if(!preg_match("#/directory01/directory02/index.php#", $_SERVER['PHP_SELF'])) {
        echo "<script>
                    alert('잘못된 접근!');
                    history.back();
                  </script>";
        return;
 }
    
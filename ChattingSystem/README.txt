 ## ä�� �ý��� ##

������ : 2-WDJ 1401213 �̽¹�
���糯 : 2017�� 12�� 5��

 ** ������ �ʿ��� �κ�.
 
 - index.php
 $_SERVER['PHP_SELF']�� �м��Ͽ� URI ����� ����� �����մϴ�. ���� public_html ���������� index.php�� ��ġ�� ���� ��ġ�� ������ �ʿ䰡 �ֽ��ϴ�.
 
 	@@@ source code

 => �� = index.php�� �����ϱ� ���� �ʿ��� ���丮�� �����Դϴ�.
if(sizeof($access_root) < ��{�� + 2}) {
    include_once('./view/login.php');
} else {
    include_once('./control/ChattingSystem_c.php');

    $control = new ChattingSystem_c();

    if(isset($access_root[��{�� + 2}])) {
        $control->$access_root[��{�� + 1}]($access_root[�� + 2]);
    } else {
        $control->$access_root[��{�� + 1}]();
    }
}

��) index.php�� ��ġ�� public_html/directory01/directory02/index.php�� �� �� = 3�̰�, ���� �ҽ��ڵ�� 

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

�� �Ǿ�� �մϴ�.

 - model/DBInfo.php
 �����ͺ��̽� ���ӿ� �ʿ��� ������ ����Ǵ� �����Դϴ�. DB���ӿ� �ʿ��� url, id, password, database�� �����ؾ� �մϴ�.
 
 - index.php�� ������ ��� php ����
 URL�� �̿��� �ý��� ������ҿ� ���� ������ ���� ���� ��� PHP������ �ֻ�ܿ��� $_SERVER['PHP_SELF']�� ����ǥ������ �̿��� �˻��ϰ� �ֽ��ϴ�. URL�� index.php�� ����� ���� �������� ��ϵǾ� �ִ��� �˻��Ͽ� ���Ե��� �ʾ��� ��� �߸��� �������� �ν��ϰ� ������ �����մϴ�.

 		@@ source code
    if(!preg_match("��{URL of index.php}", $_SERVER['PHP_SELF'])) {
        echo "<script>
                    alert('�߸��� ����!');
                    history.back();
                  </script>";
        return;
    }

 URL of index.php�� index.php�� �����ϱ� ���� �ʿ��� ���� ���丮�� ������ URL�� �ǹ��մϴ�.

 ��) index.php�� ��ġ�� public_html/directory01/directory02/index.php�� ��

 if(!preg_match("#/directory01/directory02/index.php#", $_SERVER['PHP_SELF'])) {
        echo "<script>
                    alert('�߸��� ����!');
                    history.back();
                  </script>";
        return;
 }
    
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php 

    //로그인 이후 저장되는 세션 형식에 따라 변경할 부분
    session_start();
    $headers = apache_request_headers();
    $user_id = $headers['Authorization'];
    //echo $user_id;


    //디비 연결
    $db_host = "localhost";
    $db_user = "freepath";
    $db_password = "free12!!";
    $db_name = "freepath";
    
    $con = mysqli_connect($db_host, $db_name, $db_password, $db_name);
    mysqli_query($con,'SET NAMES utf8');
    
    //check connection
    if (mysqli_connect_error($con)){
        echo "mysql fail!";
        exit();
    }
    //echo "mysql connect!";
    
    if(!$_POST['purposes']) {
        echo '{"status": 401, "messages": "no post data"}';
    }

    $sql = "INSERT INTO history (u_id, hashtags) VALUES(".$user_id.", ".$_POST['purposes'].")";
    //echo $sql;

    if(!mysqli_query($con,$sql)){
        die('Error: '.mysqli_error($con));
    }else{
       // encode php array to json string
        $arr = array(
            'status' => '200',
            'message' => '조건 선택 완료'
        );
        
        // encode php array to json string
        $RESULT = json_encode($arr, JSON_UNESCAPED_UNICODE);
        echo $RESULT;
    
        if (json_last_error() > 0) {
            echo json_last_error_msg() . PHP_EOL;
        }
    }

?>


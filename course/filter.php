
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
    
    $_POST = json_decode(file_get_contents('php://input'), true);
    if(!$_POST['purposes']) {
        echo '{"status": 401, "messages": "no post data"}';
    }

    $sql = "INSERT INTO history (u_id, hashtags) VALUES(".$user_id.", \"".$_POST['purposes']."\")";
    //echo $sql;


    if(!mysqli_query($con,$sql)){
        die('Error: '.mysqli_error($con));
    }else{
       // encode php array to json string
        $arr = array(
            'status' => '200',
            'message' => '조건 선택 완료'
        );
        
        header('Content-type: application/json');
        // encode php array to json string
        $RESULT = json_encode($arr, JSON_UNESCAPED_UNICODE);   
        //echo $RESULT;
    
        if ($RESULT === false) {
            // Avoid echo of empty string (which is invalid JSON), and
            // JSONify the error message instead:
            $RESULT = json_encode(array("jsonError", json_last_error_msg()));
            if ($RESULT === false) {
                // This should not happen, but we go all the way now:
                $RESULT = '{"jsonError": "unknown"}';
            }
            // Set HTTP response status code to: 500 - Internal Server Error
            http_response_code(500);
        }
        echo $RESULT;

    
        if (json_last_error() > 0) {
            echo json_last_error_msg() . PHP_EOL;
        }
    }

?>


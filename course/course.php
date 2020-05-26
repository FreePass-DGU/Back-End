<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php 

    //로그인 이후 저장되는 세션 형식에 따라 변경할 부분
    session_start();
    $headers = apache_request_headers();
    if($headers['Authorization']){
        $user_id = $headers['Authorization'];
    }


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


    //전체 코스 목록 출력
    if (!empty($_GET['id'])) { 
        echo "where r u?";
        
        $sql = "select * from course";
        $result = mysqli_query($con, $sql);
    
        $Return_FinalArray = array();
        if ($result->num_rows > 0) {

            while( $row = mysqli_fetch_array($result) ) {
                $my_array = array (
                    'id' => $row["cs_id"],
                    'name' => $row["cs_name"],
                    'purpose' => $row["cs_purpose"],
                );
                array_push($Return_FinalArray,$my_array);
            }
        }else {
            echo '{}';
        }


    } elseif (empty($_GET['id'])) {
        //유저가 고른 조건에 따른 코스 목록 출력
        if($_GET['id'] == 0){
            
            //가장 최근 유저의 필터링 조건 기록
            $sql = "select hashtags from history where h_id = (
                select max(h_id) from history where u_id = ".$user_id.")";
            $hashtags = mysqli_query($con, $sql);
            $hashtags = mysqli_fetch_array($hashtags)["hashtags"];
            $hashtags = explode(';', $hashtags);

            $sql = "select * from course";
            $courses = mysqli_query($con, $sql);

            //필터링 조건에 맞는 코스 고르기
            $Return_FinalArray = array();
            if ($courses->num_rows > 0){
                while($row = mysqli_fetch_array($courses)){
                    foreach($hashtags as $hashtag){
                        $flag = 0;
                        if($flag == 0 and preg_match('/'.$hashtag.'/', $row["cs_purpose"])){
                            $my_array = array (
                                'id' => $row["cs_id"],
                                'name' => $row["cs_name"],
                                'purpose' => $row["cs_purpose"],
                            );
                            array_push($Return_FinalArray,$my_array);
                            $flag = 1;
                        }
                    }
                }
            } else {
                echo '{}';
            }

        //특정 코스에 대한 정보 출력    
        }else{
            
            
        }

    } else {
        
        echo '{"status": 400, "messages": "wrong access"}';
        exit();
    }   


    
    $arr = array(
        'status' => '200',            
        'message' => '코스 출력 완료'
    );
    $arr['data'] = $Return_FinalArray;  
    
    
    // encode php array to json string
    $RESULT = json_encode($arr, JSON_UNESCAPED_UNICODE);     
    echo $RESULT;
    
    if (json_last_error() > 0) {
        echo json_last_error_msg() . PHP_EOL;
    }

?>


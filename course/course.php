<meta http-equiv="Content-Type" "application/json; charset=utf-8" />
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

    $Return_FinalArray = array();
    //전체 코스 목록 출력
    if (!isset($_GET['id'])) { 

        $sql = "select * from course";
        $result = mysqli_query($con, $sql);
    
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

     //유저가 고른 조건에 따른 코스 목록 출력
    } elseif (isset($_GET['id']) and $_GET['id'] == 0) {
    
            //가장 최근 유저의 필터링 조건 기록
            $sql = "select hashtags from history where h_id = (
                select max(h_id) from history where u_id = ".$user_id.")";
            $hashtags = mysqli_query($con, $sql);
            $hashtags = mysqli_fetch_array($hashtags)["hashtags"];
            $hashtags = explode(';', $hashtags);

            $sql = "select * from course";
            $courses = mysqli_query($con, $sql);

            //필터링 조건에 맞는 코스 고르기
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

    // 특정 코스에 대한 정보 출력
    } elseif (isset($_GET['id']) and $_GET['id'] != 0){
        
        //해당 코스 받아오기
        $sql = "select * from course where cs_id = ".$_GET['id'];
        $course =  mysqli_query($con, $sql);
        $row = mysqli_fetch_array($course);
        $course = array (
            'id' => $row["cs_id"],
            'name' => $row["cs_name"],
            'purpose' => $row["cs_purpose"],
            'spt' => $row['Spt_id'],
            'ept' => $row['Ept_id']
        );
        
        
        //코스 시작 지점, 끝 지점 받아오기
        $area_ids = array();
        $sql = "select * from point where pt_id = '".$course['spt']."'";
        $result =  mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result);
        $course['spt'] = $row["pt_name"];
        array_push($area_ids, $row["area_id"]);

        $sql = "select * from point where pt_id = '".$course['ept']."'";
        $row =  mysqli_query($con, $sql);
        $row = mysqli_fetch_array($row);
        $course['ept'] = $row["pt_name"];
        array_push($area_ids, $row["area_id"]);

        if($area_ids[1] == "A" or $area_ids[1] == "B"){
            array_push($area_ids, "X");
        }else{
            array_push($area_ids, "Y");
        }

        if($area_ids[0] == $area_ids[1]){
            unset($area_ids[1]);
            $area_ids = array_values($area_ids);
        }

        //해시태그 가져오기
        $purposes = explode(';',$course['purpose']);

        //해당 디비 다 들고오기
        $foods = array();
        $places = array();
        $cafes = array();
        
        foreach($area_ids as $area_id){
            $sql = "select * from food where area_id = \"".$area_id."\"";
            $result =  mysqli_query($con, $sql);

            if ($result->num_rows > 0) {
                while( $row = mysqli_fetch_array($result) ) {
                    $flag = 0;
                    foreach($purposes as $purpose){
                        if(preg_match('/'.$purpose.'/', $row["f_purpose"]))
                            $flag = 1;
                    }
                    if(!$flag) continue;

                    $my_array = array (
                        'id' => $row["f_id"],
                        'name' => $row["f_name"],
                        'purpose' => $row["f_purpose"],
                        'info' => $row["f_info"],
                        'url' => $row["f_url"]
                    );
                    array_push($foods,$my_array);
                }
            }else {
                //echo '{}';
            }


            $sql = "select * from cafe where area_id = \"".$area_id."\"";
            $result =  mysqli_query($con, $sql);

            if ($result->num_rows > 0) {
                while( $row = mysqli_fetch_array($result) ) {
                    $flag = 0;
                    foreach($purposes as $purpose){
                        if(preg_match('/'.$purpose.'/', $row["c_purpose"]))
                            $flag = 1;
                    }
                    if(!$flag) continue;

                    $my_array = array (
                        'id' => $row["c_id"],
                        'name' => $row["c_name"],
                        'purpose' => $row["c_purpose"],
                        'info' => $row["c_info"],
                        'url' => $row["c_url"]
                    );
                    array_push($cafes,$my_array);
                }
            }else {
                //echo '{}';
            }

            $sql = "select * from place where area_id = \"".$area_id."\"";
            $result =  mysqli_query($con, $sql);

            if ($result->num_rows > 0) {
                while( $row = mysqli_fetch_array($result) ) {
                    $flag = 0;
                    foreach($purposes as $purpose){
                        if(preg_match('/'.$purpose.'/', $row["p_purpose"]))
                            $flag = 1;
                    }
                    if(!$flag) continue;
                    $my_array = array (
                        'id' => $row["p_id"],
                        'name' => $row["p_name"],
                        'purpose' => $row["p_purpose"],
                        'info' => $row["p_info"],
                        'url' => $row["p_url"]
                    );
                    array_push($places,$my_array);

                }
            }else {
                //echo '{}';
            }
        }

        $course['food'] = $foods;
        $course['cafe'] = $cafes;
        $course['place'] = $places;
        $Return_FinalArray = $course;

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


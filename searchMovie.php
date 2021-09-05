<?php
require "./handleCORS.php";
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS, HEAD');
// header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token X-Requested-With, Accept, Authorization, append,delete,entries,foreach,get,has,keys,set,values');
set_error_handler("handleError");
try {
    include("./db-settings.php");
    // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    //     header('Access-Control-Allow-Origin: *');
    //     header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    //     header('Access-Control-Allow-Headers: token, Content-Type');
    //     header('Access-Control-Max-Age: 1728000');
    //     header('Content-Type: application/json');
    //     header('x-requested-with: XMLHttpRequest');
        
    //     http_response_code(200);
    //     die();
    // }
    // else {
    //     header('Access-Control-Allow-Origin: *');
    // }
    $json = file_get_contents('php://input');

    $data = json_decode($json); 

    //start transaction
    $mysqli->autocommit(FALSE);
    //Calculate the Request Signature
    // $encodedStr = generateHashcode($_POST['first'], $_POST['last'], $_POST['email'], $_POST['pass']);
    
    //Check if the signature matches with the generated signature
    // if ($encodedStr == $_POST['sig']) {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($data != null) {
            //Read post parameters
            $searchby = $data->searchby;
            if ($searchby == "title") {
                $title = $data->title; // title of the movie (string)
                $query = "SELECT * FROM `movies` WHERE `title` LIKE '%$title%'";
            }
            elseif ($searchby == "description") {
                $description = $data->description; // title of the movie (string)
                $query = "SELECT * FROM `movies` WHERE `description` LIKE '%$description%'";
            }
            elseif ($searchby == "year") {
                $year = $data->year; // year (number)
                $query = "SELECT * FROM `movies` WHERE `year` = '$year'";
            }
            elseif ($searchby == "language") {
                $language = $data->language; // languageId (number)
                $query = "SELECT * FROM `movies` WHERE `languageId` = '$language'";
            }
            elseif ($searchby == "location") {
                $location = $data->location; // locationId (number)
                $query = "SELECT * FROM `movies` WHERE `locationId` = '$location'";
            }
            elseif ($searchby == "cast") {
                $cast = $data->cast; // cast (string)
                $query = "SELECT * FROM movies WHERE `cast` LIKE '%$cast%'";
            }
            elseif ($searchby == "watched") {
                $watched = $data->watched; // watched (boolean)
                $query = "SELECT * FROM movies WHERE `watched` = '$watched'";
            }
            elseif ($searchby == "advanced") {
                $title = null;
                $description = null;
                $language = null;
                $location = null;
                $cast = null;
                $year = null;
                $wathced = null;
                $query = "SELECT * FROM movies WHERE";
                if(!is_null($data->title)) {
                    $title = $data->title;
                    $query .= " `title` LIKE '%$title%' ";
                }
                if(!is_null($data->year)) {
                    $year = $data->year;
                    if(!is_null($title)) {
                        $query .= " AND ";
                    }
                    $query .= " `year` = '$year' ";
                }
                if(!is_null($data->description)) {
                    $description = $data->description;
                    if(!is_null($title) || !is_null($year)) {
                        $query .= " AND ";
                    }
                    $query .= " `description` LIKE '%$description%' ";
                }
                if(!is_null($data->language)) {
                    $language = $data->language;
                    if(!is_null($description) || !is_null($title) || !is_null($year)) {
                        $query .= " AND ";
                    }
                    $query .= " `languageId` = '$language' ";
                }
                if(!is_null($data->location)) {
                    $location = $data->location;
                    if(!is_null($description) || !is_null($title) || !is_null($year) || !is_null($language)) {
                        $query .= " AND ";
                    }
                    $query .= " `locationId` = '$location' ";
                }
                if(!is_null($data->cast)) {
                    $cast = $data->cast;
                    if(!is_null($description) || !is_null($title) || !is_null($year) || !is_null($language) || !is_null($location)) {
                        $query .= " AND ";
                    }
                    $query .= " `cast` LIKE '$cast' ";
                }
                if(!is_null($data->watched)) {
                    $watched = $data->watched;
                    if (!is_null($watched)) {
                        if(!is_null($description) || !is_null($title) || !is_null($year) || !is_null($language) || !is_null($location) || !is_null($cast)) {
                            $query .= " AND ";
                        }
                        $query .= " `watched` = '$watched' ";
                    }
                }
            }
            else {                
                deliver_response(400, "Required parameters are not defined.", "");
                http_response_code(400);
            }

            $result = $mysqli->prepare($query);            
            $result->execute();
            $result->bind_result($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8, $col9, $col10);
            
            $out = array();
            while($result->fetch()){
                $out[] = array('id' => $col1, 'title' => $col2, 'description' => $col3, 'year' => $col4, 'cast' => $col5,
                'languageId' => $col6, 'locationId' => $col7, 'locationPath' => $col8, 'imagePath' => $col9, 'watched' => $col10);
            }	
            $result->close();
            deliver_response(200, $query, $out);
            http_response_code(200);
        } else {
            deliver_response(401, "Invalid Parameters.", "");
            http_response_code(401);
        }
    } else {
        deliver_response(405, "Request method is not accepted.", "");
        http_response_code(405);
    }
    
    $mysqli->commit();
    $mysqli->autocommit(TRUE);
}
//catch exception
catch(Exception $e) {
    $mysqli->rollback(); 
    $mysqli->autocommit(TRUE);
    
    error_log("Exception");
    error_log($e);
    deliver_response(400, "Exception Occured.-> " . $e, "");
    http_response_code(400);
}
@mysqli_close($mysqli);

function deliver_response($status, $status_message, $data) {
    $json = array("status" => $status, "msg" => $status_message, "info" => $data);
    header('Content-type: application/json');
    echo json_encode($json);
}

function handleError($errno, $errstr, $error_file, $error_line) {
    error_log("[ERROR] SEARCHMOVIE.PHP |  $errno, $errstr, $error_file, $error_line");
    deliver_response(400, "", "" . $errno . $errstr . $error_file . $error_line);
    http_response_code(400);
    die();
}

?>


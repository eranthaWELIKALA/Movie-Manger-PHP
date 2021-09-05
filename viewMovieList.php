<?php
require "./handleCORS.php";
set_error_handler("handleError");
try {
    include("./db-settings.php");

    //start transaction
    $mysqli->autocommit(FALSE);
    
    //Calculate the Request Signature
    // $encodedStr = generateHashcode($_POST['first'], $_POST['last'], $_POST['email'], $_POST['pass']);
    
    //Check if the signature matches with the generated signature
    // if ($encodedStr == $_POST['sig']) {
    if ($_SERVER['REQUEST_METHOD'] == "GET") {
        $query = "SELECT movies.id, title, movies.description, year, cast, language, locations.name AS location, imagePath, watched FROM movies LEFT JOIN languages ON movies.languageId = languages.id LEFT JOIN locations ON movies.locationId = locations.id ORDER BY title";

        $result = $mysqli->prepare($query);            
        $result->execute();
        $result->bind_result($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8, $col9);
        
        $out = array();
        while($result->fetch()){
            $out[] = array('id' => $col1, 'title' => $col2, 'description' => $col3, 'year' => $col4, 'cast' => $col5,
            'language' => $col6, 'location' => $col7, 'imagePath' => $col8, 'watched' => $col9);
        }	
        $result->close();
        deliver_response(200, "", $out);
        http_response_code(200);
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
    error_log("[ERROR] VIEWMOVIELIST.PHP |  $errno, $errstr, $error_file, $error_line");
    deliver_response(400, $query, "" . $errno . $errstr . $error_file . $error_line);
    http_response_code(400);
    die();
}

?>


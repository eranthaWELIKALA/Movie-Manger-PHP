<?php
require "./handleCORS.php";
set_error_handler("handleError");
try {
    include("./db-settings.php");
    // include("./generateHash.php");

    $json = file_get_contents('php://input');

    $data = json_decode($json); 

    //start transaction
    $mysqli->autocommit(FALSE);
    
    //Calculate the Request Signature
    // $encodedStr = generateHashcode($_POST['first'], $_POST['last'], $_POST['email'], $_POST['pass']);
    
    //Check if the signature matches with the generated signature
    // if ($encodedStr == $_POST['sig']) {
    if ($data != null) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            //Read post parameters
            $languageId = $data->id;
            $language = $data->language;

            if (!is_null($language)) {
                if (is_null($languageId)) {
                    $languageInsertQuery = "INSERT INTO `languages` (`id`, `language`) VALUES (NULL, '$language')";
                    $languageInsertResult = $mysqli->prepare($languageInsertQuery);
                    if(!$languageInsertResult) {
                        error_log("Error occurred while adding the language".$mysqli->error);
                        throw new Exception($mysqli->error);
                    } else {
                        $languageInsertResult->execute();
                    }
                    $languageInsertResult->close();
                }
                else {
                    $languageUpdateQuery = "UPDATE `languages` SET `language` = '$language' WHERE `languages`.`id` = $languageId";
                    $languageUpdateResult = $mysqli->prepare($languageUpdateQuery);
                    if(!$languageUpdateResult) {
                        error_log("Error occurred while updating the language".$mysqli->error);
                        throw new Exception($mysqli->error);
                    } else {
                        $languageUpdateResult->execute();
                    }
                    $languageUpdateResult->close();
                }
                deliver_response(201, "Language added/ edited successfully.", "" . $languageId);
                http_response_code(201);
            } else {
                deliver_response(400, "Required parameters are not defined.", "");
                http_response_code(400);
            }
        }
        else {            
            deliver_response(405, "Request method is not accepted.", "");
            http_response_code(405);
        }
    } else {
        deliver_response(401, "Invalid Parameters.", "");
        http_response_code(401);
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
    error_log("[ERROR] ADDEDITLANGUAGE.PHP |  $errno, $errstr, $error_file, $error_line");
    deliver_response(400, "Error Occured.", "" . $errno . $errstr . $error_file . $error_line);
    http_response_code(400);
    die();
}

?>
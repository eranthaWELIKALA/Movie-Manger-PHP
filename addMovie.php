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
            $title = isset($data->title)? $data->title: null;
            $description = isset($data->description)? $data->description: null;
            $year = isset($data->year)? $data->year: null;
            $cast = isset($data->cast)? $data->cast: null;
            $imagePath = isset($data->imagePath)? $data->imagePath: null;
            $watched = isset($data->watched)? $data->watched: null;
            $language = isset($data->languageId)? $data->languageId: null; // Id
            $location = isset($data->locationId)? $data->locationId: null; // Id
            $locationPath = isset($data->locationPath)? $data->locationPath: null; // Id
            if (isset($data->newLanguageId)) {
                $newLanguage = $data->newLanguageId; // New Language
            }
            if (isset($data->newLocationName)) {
                $newLocationName = $data->newLocationName; // New Location Name
            }
            if (isset($data->newLocationDesc)) {
                $newLocationDesc = $data->newLocationDesc; // New Location Description
            }

            $languageQueryBefore = "SELECT `id` FROM `languages` WHERE `id` = '$language'";
            $languageQueryBeforeResult = $mysqli->prepare($languageQueryBefore);
            $languageQueryBeforeResult->execute();
            $languageQueryBeforeResult->bind_result($languageId);
            $languageQueryBeforeResult->fetch();
            $languageQueryBeforeResult->close();

            if(is_null($languageId) || (!is_null($languageId) && $languageId == 0)) {
                $languageInsertQuery = "INSERT INTO `languages` (`id`, `language`) VALUES (NULL, '$newLanguage')";
                $languageInsertResult = $mysqli->prepare($languageInsertQuery);
                if(!$languageInsertResult) {
                    error_log("Error occurred while adding the language".$mysqli->error);
                    throw new Exception($mysqli->error);
                } else {
                    $languageInsertResult->execute();
                }
                $languageInsertResult->close();
            
                $languageQueryAfter = "SELECT `id` FROM `languages` WHERE `language` = '$newLanguage'";
                $languageQueryAfterResult = $mysqli->prepare($languageQueryAfter);
                $languageQueryAfterResult->execute();
                $languageQueryAfterResult->bind_result($languageId);
                $languageQueryAfterResult->fetch();
                $languageQueryAfterResult->close();
            }

            $locationQueryBefore = "SELECT `id` FROM `locations` WHERE `id` = '$location'";
            $locationQueryBeforeResult = $mysqli->prepare($locationQueryBefore);
            $locationQueryBeforeResult->execute();
            $locationQueryBeforeResult->bind_result($locationId);
            $locationQueryBeforeResult->fetch();
            $locationQueryBeforeResult->close();

            if(is_null($locationId) || (!is_null($locationId) && $locationId == 0)) {
                $locationInsertQuery = "INSERT INTO `locations` (`id`, `name`, `description`) VALUES (NULL, '$newLocationName', '$newLocationDesc')";
                $locationInsertResult = $mysqli->prepare($locationInsertQuery);
                if(!$locationInsertResult) {
                    error_log("Error occurred while adding the location".$mysqli->error);
                    throw new Exception($mysqli->error);
                } else {
                    $locationInsertResult->execute();
                }
                $locationInsertResult->close();

                $locationQueryAfter = "SELECT `id` FROM `locations` WHERE `name` = '$newLocationName'";
                $locationQueryAfterResult = $mysqli->prepare($locationQueryAfter);
                $locationQueryAfterResult->execute();
                $locationQueryAfterResult->bind_result($locationId);
                $locationQueryAfterResult->fetch();
                $locationQueryAfterResult->close();
            }
            
            //check post paramaters are empty
            if(!empty($title) && (!is_null($locationId)) && (!is_null($languageId))){
                $insertQuery = "INSERT INTO `movies` (`id`, `title`, `description`, `year`, `cast`, `languageId`, `locationId`, `locationPath`, `imagePath`, `watched`) VALUES (NULL, '$title', '$description', '$year', '$cast', '$languageId', '$locationId', '$locationPath', '$imagePath', '$watched')";
                $result = $mysqli->prepare($insertQuery);
                if(!$result) {
                    error_log("Error occurred while adding the movie".$mysqli->error);
                    throw new Exception($mysqli->error);
                } else {
                    $result->execute();
                    error_log("Movie added successfully.");
                    deliver_response(201, "Movie added successfully.", "" . $locationId);
                    http_response_code(201);
                }
                $result->close();
            } else {
                deliver_response(400, "Required parameters are not defined.", "");
                http_response_code(400);
            }
        } else {
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
    error_log("[ERROR] ADDMOVIE.PHP |  $errno, $errstr, $error_file, $error_line");
    deliver_response(400, "Error Occured.", "" . $errno . $errstr . $error_file . $error_line);
    http_response_code(400);
    die();
}

?>
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
            $id = $data->id;
            if (is_null($id)) {
                deliver_response(400, "Required parameters are not defined.", "");
                http_response_code(400);
                return;
            }
            $title = $data->title;
            $description = $data->description;
            $year = $data->year;
            $cast = $data->cast;
            $imagePath = $data->imagePath;
            $language = $data->language; // Id
            $location = $data->location; // Id
            $newLanguage = $data->newLanguage; // New Language
            $newLocationName = $data->newLocationName; // New Location Name
            $newLocationDesc = $data->newLocationDesc; // New Location Description

            $languageQueryBefore = "SELECT `id` FROM `languages` WHERE `id` = '$language'";
            $languageQueryBeforeResult = $mysqli->prepare($languageQueryBefore);
            $languageQueryBeforeResult->execute();
            $languageQueryBeforeResult->bind_result($languageId);
            $languageQueryBeforeResult->fetch();
            $languageQueryBeforeResult->close();

            if(is_null($languageId) || (!is_null($languageId) && $languageId == 0)) {
                $languageupdateQuery = "INSERT INTO `languages` (`id`, `language`) VALUES (NULL, '$newLanguage')";
                $languageInsertResult = $mysqli->prepare($languageupdateQuery);
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
                $locationupdateQuery = "INSERT INTO `locations` (`id`, `name`, `description`) VALUES (NULL, '$newLocationName', '$newLocationDesc')";
                $locationInsertResult = $mysqli->prepare($locationupdateQuery);
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
            if(!is_null($title) && (!is_null($locationId)) && (!is_null($languageId)) && (!is_null($description)) && (!is_null($year)) && (!is_null($cast)) && (!is_null($imagePath))){
                $updateQuery = "UPDATE `movies` SET `title` = '$title', `description` = '$description', `year` = '$year', `cast` = '$cast', `languageId` = '$languageId', `locationId` = '$locationId', `imagePath` = '$imagePath' WHERE `id` = $id";
                $result = $mysqli->prepare($updateQuery);
                if(!$result) {
                    error_log("Error occurred while adding the movie".$mysqli->error);
                    throw new Exception($mysqli->error);
                } else {
                    $result->execute();
                    error_log("Movie added successfully.");
                    deliver_response(201, "Movie updated successfully.", "" . $locationId);
                    http_response_code(201);
                }
                $result->close();
            } else {
                $updateQuery = "UPDATE `movies` SET";
                $no_of_updates = 0;
                if (!is_null($title)) {
                    $updateQuery .= " `title` = '$title' ";
                    $no_of_updates++;
                }
                if (!is_null($description)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `description` = '$description' ";
                    $no_of_updates++;
                }
                if (!is_null($year)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `year` = '$year' ";
                    $no_of_updates++;
                }
                if (!is_null($title)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `cast` = '$cast' ";
                    $no_of_updates++;
                }
                if (!is_null($title)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `languageId` = '$languageId' ";
                    $no_of_updates++;
                }
                if (!is_null($title)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `locationId` = '$locationId' ";
                    $no_of_updates++;
                }
                if (!is_null($title)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `title` = '$title' ";
                    $no_of_updates++;
                }
                if (!is_null($title)) {
                    if ($no_of_updates > 0) {
                        $updateQuery .= " , ";
                    }
                    $updateQuery .= " `title` = '$title' ";
                    $no_of_updates++;
                }
                if ($no_of_updates > 0) {
                    $updateQuery .= " , ";
                    $updateQuery .= " WHERE `id` = $id";
                    $result = $mysqli->prepare($updateQuery);
                    if(!$result) {
                        error_log("Error occurred while adding the movie".$mysqli->error);
                        throw new Exception($mysqli->error);
                    } else {
                        $result->execute();
                        error_log("Movie added successfully.");
                        deliver_response(201, "Movie updated successfully.", "" . $locationId);
                        http_response_code(201);
                    }
                    $result->close();
                }
                else {                    
                    deliver_response(400, "Required parameters are not defined.", "");
                    http_response_code(400);
                }
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
    error_log("[ERROR] CREATE.PHP |  $errno, $errstr, $error_file, $error_line");
    deliver_response(400, "Error Occured.", "" . $errno . $errstr . $error_file . $error_line);
    http_response_code(400);
    die();
}

?>
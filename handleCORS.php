<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS, HEAD');
header('Access-Control-Allow-Headers: Access-Control-Allow-Origin, Origin, Content-Type, X-Auth-Token, X-Requested-With, Accept, Authorization, append,delete,entries,foreach,get,has,keys,set,values');
header('Content-Type: application/json, charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") { http_response_code(200); die(); }
?>
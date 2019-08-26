<?php

require_once "common.php";

if (isset($_GET['token']) && $_GET['token'] == getenv("BOTTOKEN")) {
    $result = $db->select("SELECT * FROM bugs LIMIT 1");
    if (count($result) > 0) {
        $bug_id = $result[0]['id'];
        $url = $result[0]['url'];
        $result = $db->delete("DELETE FROM bugs WHERE id=:id", [":id" => $bug_id]);
        echo json_encode([ "id" => $bug_id, "url" => $url, "delete" => count($result) > 0 , "code" => 0]);
    } else {
        echo json_encode([ "id" => "", "url" => "", "delete" => "", "msg" => "Nothing", "code" => 1 ]);
    }
} else {
    header('HTTP/1.1 403 Forbidden');
    die("you are not admin!");
}

<?php
header("Content-Type: application/json; charset=utf-8");
$uid = preg_replace("/[^0-9]/", "", $_GET["uid"] ?? "");
if ($uid === "") {
    echo json_encode(["success" => false, "message" => "UID required."]);
    exit;
}
echo json_encode(["success" => true, "name" => "Player " . substr($uid, -4)], JSON_UNESCAPED_UNICODE);

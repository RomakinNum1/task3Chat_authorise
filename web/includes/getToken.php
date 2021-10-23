<?php

use Firebase\JWT\JWT;
use Roman\Func\ConnectToDB;
use Roman\Func\dataBaseEditor;

const SELECT_USER = "SELECT * FROM `users` WHERE login = :login";
$dataBaseConnect = ConnectToDB::connect();

try {
    $res = dataBaseEditor::SelectForToken($dataBaseConnect, $_POST['token']);
    if ($res) {
        $response = [
            "status" => true,
            "fullName" => $res["fullName"]
        ];

    } else {
        $response = [
            "status" => false
        ];
    }
    echo json_encode($response);
}
catch(Exception $exception)
{
    $response = [
        "status" => false
    ];
    echo json_encode($response);
}
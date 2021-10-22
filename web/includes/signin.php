<?php

use Firebase\JWT\JWT;
use Roman\Func\ConnectToDB;
use Roman\Func\dataBaseEditor;
const ERROR_ON_INPUTS = 1;

$dataBaseConnect = ConnectToDB::connect();

$error_fields = [];

if ($_POST['login'] === '') {
    $error_fields[] = 'login';
}
if ($_POST['password'] === '') {
    $error_fields[] = 'password';
}
if (!empty($error_fields)) {
    $response = [
        "status" => false,
        "type" => ERROR_ON_INPUTS,
        "message" => "Проверьте правильность полей",
        "fields" => $error_fields
    ];

    echo json_encode($response);

    die();
}

$res = dataBaseEditor::Select($dataBaseConnect, $_POST);
if ($res) {
    //$user = $check->fetch(PDO::FETCH_ASSOC);
    /*$_SESSION['user'] = [
        'id' => $_POST['id'],
        'fullName' => $_POST['fullName'],
        'email' => $_POST['email']
    ];*/
    $response = [
        "status" => true,
        "fullName" => $res['fullName']
    ];

} else {
    $response = [
        "status" => false,
        "message" => "Неверный логин или пароль"
    ];
}
echo json_encode($response);
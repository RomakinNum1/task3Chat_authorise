<?php

use Firebase\JWT\JWT;
use Roman\Func\ConnectToDB;
use Roman\Func\DataBaseEditor;

const ERROR_ON_INPUTS = 1;

$dataBaseConnect = ConnectToDB::connect();                      //подключение к базе данных

$error_fields = [];                                             //массив названий ошибочных полей

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

$res = DataBaseEditor::Select($dataBaseConnect, $_POST);        //получение записи из базы данных
if ($res) {
    $response = [
        "status" => true,
        "Id" => JWT::encode($res['id'], $_ENV['JWT_KEY'])
    ];

} else {
    $response = [
        "status" => false,
        "message" => "Неверный логин или пароль"
    ];
}
echo json_encode($response);
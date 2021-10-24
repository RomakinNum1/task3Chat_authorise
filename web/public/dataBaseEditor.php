<?php

namespace Roman\Func;

use Firebase\JWT\JWT;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;

class dataBaseEditor
{
    static function getUsers($dataBaseConnect)
    {
        $userList = [];
        $resultDB = $dataBaseConnect->prepare("select * from users");
        $resultDB->execute();

        while ($res = $resultDB->fetch(PDO::FETCH_ASSOC)) {
            $userList[] = $res;
        }

        self::echoResults($userList, 200);
    }

    static function getUser($dataBaseConnect, $id)
    {
        $resultDB = $dataBaseConnect->prepare("select * from users where id = :id");
        $resultDB->execute(['id' => $id]);
        $res = $resultDB->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResults('User not found', 404);
            die();
        }
        self::echoResults($res, 200);
    }

    static function addUser($dataBaseConnect, $data)
    {
        if ($data['fullName'] != '' && $data['email'] != '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && $data['login'] != '' && $data['password'] != '') {
            $resultDB = $dataBaseConnect->prepare("insert into users values (null, :fullName, :email, false, :login, :password)");
            $resultDB->execute(array('fullName' => $data['fullName'], 'email' => $data['email'], 'login' => $data['login'], 'password' => md5($data['password'])));

            $data1 = [
                'time' => time() + 30,
                'id' => $dataBaseConnect->lastInsertId()
            ];

            $jwt = JWT::encode($data1, $_ENV['JWT_KEY']);

            self::sendMessage($jwt, $data);

            /*$res = $dataBaseConnect->lastInsertId();

            self::echoResults($res, 201);*/
        } else {
            self::echoResults('The username or email or password or login is incorrect', 400);
        }
    }

    static function updateUser($dataBaseConnect, $id, $data)
    {
        $resultDB = $dataBaseConnect->prepare("select * from users where id = :id");
        $resultDB->execute(['id' => $id]);
        $res = $resultDB->fetch(PDO::FETCH_ASSOC);

        if ($data['firstName'] != '' && $data['lastName'] != '' && $res && $data['email'] != '') {
            $resultDB = $dataBaseConnect->prepare("update users set firstName = :firstName, lastName = :lastName, email = :email where id = $id");
            $resultDB->execute(array(':firstName' => $data['firstName'], ':lastName' => $data['lastName'], ':email' => $data['email']));

            $res = 'User is updated';

            self::echoResults($res, 202);
        } else self::echoResults('The username or password is incorrect', 400);
    }

    static function deleteUser($dataBaseConnect, $id)
    {
        $resultDB = $dataBaseConnect->prepare("delete from users where id = :id");
        $resultDB->execute(['id' => $id]);

        self::echoResults('', 204);
    }

    static function deleteInactiveUsers($dataBaseConnect, $id)
    {
        $resultDB = $dataBaseConnect->prepare("delete from users where id = :id AND status = 0");
        $resultDB->execute(['id' => $id]);
    }

    static function echoResults($res, $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    static function confirmEmail($dataBaseConnect, $token)
    {
        $resultDB = $dataBaseConnect->prepare("select * from users where id = :id AND status = 0");
        $resultDB->execute(['id' => $token]);
        $res = $resultDB->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            $resultDB = $dataBaseConnect->prepare("update users set status = true where id = :id");
            $resultDB->execute(['id' => $token]);
            return true;
        } else {
            return false;
        }
    }

    static function sendMessage($token, $data)
    {
        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                // Отправка через SMTP
        $mail->Host = $_ENV['MYEMAIL_HOST'];            // Адрес SMTP сервера
        $mail->SMTPAuth = true;                         // Enable SMTP authentication
        $mail->Username = $_ENV['MYEMAIL'];             // ваше имя пользователя (без домена и @)
        $mail->Password = $_ENV['MYEMAIL_PASSWORD'];    // ваш пароль
        $mail->SMTPSecure = 'ssl';                      // шифрование ssl
        $mail->Port = 465;                              // порт подключения

        $mail->setFrom($_ENV['MYEMAIL']);               // от кого
        $mail->addAddress($data['email']);              // кому

        $mail->Subject = 'Подтверждение email';
        $mail->msgHTML("<html><body>
                <h1>Здравствуйте!</h1>
                <p>Подтвердите свою почту по ссылке: <a href='http://users.api.loc/confirm/$token'>ссылка</a></p>
                </html></body>");
        $mail->send();
        // Отправляем
        /*if ($mail->send()) {
            echo 'Письмо отправлено!';
        } else {
            echo 'Ошибка: ' . $mail->ErrorInfo;
        }*/
    }

    static function Select($connect, $data)
    {
        $data['password'] = md5($data['password']);

        $check = $connect->prepare('SELECT * FROM users WHERE login = :login AND password = :password');
        $check->execute($data);
        $res = $check->fetch(PDO::FETCH_ASSOC);

        return $res;
    }

    static function SelectForToken($connect, $data)
    {
        $decoded = JWT::decode($data, $_ENV['JWT_KEY'], array('HS256'));
        $checkLogin = $connect->prepare('SELECT * FROM users WHERE id = :id');
        $checkLogin->execute(['id' => $decoded->id]);

        return $checkLogin->fetch(PDO::FETCH_ASSOC);
    }

    static function SelectLogin($connect, $data, $sql)
    {
        $checkLogin = $connect->prepare($sql);
        $checkLogin->execute(['login' => $data['login']]);

        return $checkLogin->fetch(PDO::FETCH_ASSOC);
    }

    /*private static function getColumnNames($dataBaseConnect)
    {
        $sth = $dataBaseConnect->prepare("SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='test' AND `TABLE_NAME`='users'");
        $sth->execute();
        $output = [];
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row['COLUMN_NAME'];
        }
        return $output;
    }*/

    static function addMessage($dataBaseConnect, $data)
    {
        $resultDB = $dataBaseConnect->prepare("insert into messages values (null, :name, :message)");
        $resultDB->execute($data);
    }

    static function getMessage($dataBaseConnect): array
    {
        $resultDB = $dataBaseConnect->prepare("SELECT * FROM messages ");
        $resultDB->execute();

        $output = [];
        while ($row = $resultDB->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row;
        }
        return $output;
    }
}
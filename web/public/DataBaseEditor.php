<?php

namespace Roman\Func;

use Firebase\JWT\JWT;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;

class DataBaseEditor
{
    //функция добавления нового пользователя
    static function addUser($dataBaseConnect, $data)
    {
        if ($data['fullName'] != '' && $data['email'] != '' && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && $data['login'] != '' && $data['password'] != '') {
            $resultDB = $dataBaseConnect->prepare("insert into users values (null, :fullName, :email, false, :login, :password)");
            $resultDB->execute(array('fullName' => $data['fullName'], 'email' => $data['email'], 'login' => $data['login'], 'password' => md5($data['password'])));

            $dataForToken = [
                'time' => time() + 30,
                'id' => $dataBaseConnect->lastInsertId()
            ];

            $jwt = JWT::encode($dataForToken, $_ENV['JWT_KEY']);

            self::sendMessage($jwt, $data);
        } else {
            self::echoResults('The username or email or password or login is incorrect', 400);
        }
    }

    //удаление неактивных пользователей
    static function deleteInactiveUsers($dataBaseConnect, $id)
    {
        $resultDB = $dataBaseConnect->prepare("delete from users where id = :id AND status = 0");
        $resultDB->execute(['id' => $id]);
    }

    //вывод результатов
    static function echoResults($res, $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    //функция подтверждения email
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

    //отправка сообщения email
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

    //функция выборки записи из базы данных
    static function Select($connect, $data)
    {
        $data['password'] = md5($data['password']);

        $check = $connect->prepare('SELECT * FROM users WHERE login = :login AND password = :password');
        $check->execute($data);
        $res = $check->fetch(PDO::FETCH_ASSOC);

        return $res;
    }

    //функция выборки записи из базы данных
    static function SelectLogin($connect, $data, $sql)
    {
        $checkLogin = $connect->prepare($sql);
        $checkLogin->execute(['login' => $data['login']]);

        return $checkLogin->fetch(PDO::FETCH_ASSOC);
    }
}
<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 19.06.2018
 * Time: 23:23
 */

namespace Avir\Login\Controller;


use Avir\Login\Settings\Config;

class Login extends Config
{
    public function home()
    {

        include __DIR__.'/../../storage/example-register.html';
    }

    public function register()
    {

        $this->db->dbCall('create_table_users');

        $email = $_POST['email'];

        $password = $_POST['newPassword'];

        $password2 = $_POST['newPassword2'];

        $token = $_POST['token'];

        $checkbox = $_POST['checkbox'];

        if ($email && $password && $password2) {

            if (!$token){

                $code = 1;

                echo json_encode([
                    'response' => $this->langData[$code],
                    'result' => false,
                    'code' => $code
                ]);

                exit();
            }
            else {

                $row = $this->db->dbCall('search_email', [$email]);

                if ($row) {

                    $code = 2;

                    echo json_encode([
                        'response' => $this->langData[$code],
                        'result' => false,
                        'code' => $code
                    ]);

                    exit();
                }
                else {

                    if ($password === $password2) {

                        if ($checkbox !== 'on'){

                            $code = 5;

                            echo json_encode([
                                'response' => $this->langData[$code],
                                'result' => false,
                                'code' => $code
                            ]);

                            exit();
                        }
                        else {

                            $password = password_hash($password2, PASSWORD_DEFAULT, ['cost' => 12]);

                            $this->db->dbCall('insert_into_users', [$email, $password, $token]);

                            $code = 4;

                            echo json_encode([
                                'response' => $this->langData[$code],
                                'result' => true,
                                'code' => $code
                            ]);
                        }
                    }
                    else {

                        $code = 3;

                        echo json_encode([
                            'response' => $this->langData[$code],
                            'result' => false,
                            'code' => $code
                        ]);

                        exit();
                    }
                }
            }
        }
        else {

            $this->errorData();
        }
    }
    public function login()
    {
        $email = $_POST['email'];
        $password = $_POST['currentPassword'];
        if ($email && $password) {
            $row = $this->db->dbCall('select_from_users', [$email]);
            if(!$row){
                echo json_encode([
                    'exception' => "Адрес $email не зарегистрирован",
                ]);
                exit();
            }
            else {
                if (password_verify($password, $row->password)) {
                    echo json_encode([
                        'email' => $email,
                        'exception' => 'Пароль верен',
                    ]);
                } else {
                    echo json_encode([
                        'email' => $email,
                        'exception' => 'Неверный пароль',
                    ]);
                }
            }
        }
        else {
            $this->errorData();
        }
    }
    public function mainToken()
    {
        $email = $_POST['email'];
        $tokenRaw = $this->db->dbCall('get_token', [$email]);
        $token = $tokenRaw->token;
        echo json_encode([
            'token' => $token
        ]);
    }

    public function getToken()
    {
        if ($_POST['please'] === 'token') {
            $token = hash('md5', uniqid('token', true));
            echo json_encode([
                'token' => $token
            ]);
        }
    }
    public function verifyEmail()
    {
        $email = $_POST['email'];
        $row = $this->db->dbCall('email_is_active', [$email]);
        $isActive = $row->isactive;
        if ($isActive === null){
            if($_POST['please'] === 'active'){
                echo json_encode([
                    'response' => false
                ]);
            }
            else {
                $userCodeVerify = base64_encode($email);
                $emailStr = base64_encode('email=');
                $tokenStr = base64_encode('secret=');
                $idStr = base64_encode('code=');
                $tokenRaw = $this->db->dbCall('get_token', [$email]);
                $token = $tokenRaw->token;
                $id = $tokenRaw->id;
                $meta = '<meta charset="UTF-8">';
                $verifyUrl = "<a href= " . $this->protocol . '://' . $this->server . '/verify-send-to?' . $emailStr . $userCodeVerify . '!'
                    . $tokenStr . $token . '$' . $idStr . $id . ">Подтвердить</a>";
                $message = $meta . '<h2>Если вы активировали процерудуру регистрации на сайте, перейдите по ссылке ниже.</h2>' . $verifyUrl;
                $name = ($tokenRaw->name)? $tokenRaw->name : $email;
                $r = $this->sendEmail('verify','Подтверждение регистрации пользователя', $name, $message, $email);
                echo json_encode([
                    'email' => $token
                ]);
            }
        }
        else {
            echo json_encode([
                'response' => 'Email уже был активирован'
            ]);
        }
    }
    public function verifySendEmail()
    {
        $error = 'Неожиданный запрос';
        $str = preg_match('%\?.*%',$_SERVER['REQUEST_URI'], $m);
        if ($str){
            $fullStr = str_replace('?', '',$m[0]);
            $str1 = preg_match('%.*!%', $fullStr, $m);
            if($str1){
                $emailStr = str_replace('!', '', $m[0]);
                $str2 = preg_match('%!.*%', $fullStr, $m);
                if ($str2) {
                    $email = str_replace('email=', '', base64_decode($emailStr));
                    $tokenStr = str_replace('!', '', $m[0]);
                    preg_match('%.*\$%', str_replace(base64_encode('secret='), '', $tokenStr), $m);
                    $token = str_replace('$', '', $m[0]);
                    $str3 = preg_match('%\$.*%', $fullStr, $m);
                    if($str3){
                        $id = str_replace(['$', base64_encode('code=')], '', $m[0]);
                        $raw = $this->db->dbCall('get_token', [$email]);
                        if ($id == $raw->id && $token === $raw->token){
                            echo 'Email подтвержден';
                            $this->db->dbCall('insert_isactive', [1, $email]);
                        }
                        else {
                            echo $error;
                            exit();
                        }
                    }
                    else {
                        echo $error;
                        exit();
                    }

                }
                else {
                    echo $error;
                    exit();
                }
            }
            else {
                echo $error;
                exit();
            }
        }
        else {
            echo $error;
            exit();
        }
    }

    public function error()
    {
        echo json_encode([
            'exception' => 'Что-то пошло не так',
        ]);
    }
    public function errorData()
    {
        echo json_encode([
            'exception' => 'Заполните все обязательные поля'
        ]);
    }
}
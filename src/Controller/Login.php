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

        include __DIR__.'/../../storage/example-forgot.html';
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

               $this->sendResponse($email, false, 1);
            }
            else {

                $row = $this->db->dbCall('search_email', [$email]);

                if ($row) {

                    $this->sendResponse($email, false, 2);
                }
                else {

                    if ($password === $password2) {

                        if ($checkbox !== 'on'){

                            $this->sendResponse($email, false, 5);
                        }
                        else {

                            $password = password_hash($password2, PASSWORD_DEFAULT, ['cost' => 12]);

                            $this->db->dbCall('insert_into_users', [$email, $password, $token]);

                            $this->sendResponse($email, true, 4);
                        }
                    }
                    else {

                        $this->sendResponse($email, false, 3);
                    }
                }
            }
        }
        else {

            $this->sendResponse($email, false, 9);
        }
    }

    public function login()
    {

        $email = $_POST['email'];

        $password = $_POST['currentPassword'];

        if ($email && $password) {

            $row = $this->db->dbCall('select_from_users', [$email]);

            if(!$row){

                $code = 8;

                echo json_encode([
                    'email' => $email,
                    'response' => $email.' ' .$this->langData[$code],
                    'code' => $code
                ]);

                exit();
            }
            else {

                if (password_verify($password, $row['password'])) {

                    $this->sendResponse($email, true, 7);

                }
                else {

                    $this->sendResponse($email, false, 8);
                }
            }
        }
        else {

            $this->sendResponse($email, false, 9);
        }
    }

    public function verifyEmail()
    {
        $email = $_POST['email'];

        $row = $this->db->dbCall('email_is_active', [$email]);

        $isActive = $row->isactive;

        if ($isActive === null){

            $userCodeVerify = base64_encode($email);

            $emailStr = base64_encode('email=');

            $tokenStr = base64_encode('secret=');

            $idStr = base64_encode('code=');

            $tokenRaw = $this->db->dbCall('get_token', [$email]);

            $token = $tokenRaw['token'];

            $id = $tokenRaw['id'];

            $meta = '<meta charset="UTF-8">';

            $verifyUrl = "<a href= " . $this->protocol . $this->server . '/verify-send-to?' . $emailStr . $userCodeVerify . '!'
                . $tokenStr . $token . '$' . $idStr . $id . ">".$this->langData[10]."</a>";

            $message = $meta . $this->langData[11] . $verifyUrl;

            $name = ($tokenRaw['name'])? $tokenRaw['name'] : $email;

            $this->sendEmail('verify',$this->langData[12], $name, $message, $email);

            $this->sendResponse($email, true, 14);
        }
        else {

            $this->sendResponse($email, false, 13);
        }
    }

    public function verifySendEmail()
    {

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

                        if ($id == $raw['id'] && $token === $raw['token']){

                            if ($this->db->dbCall('insert_isactive', [1, $email])){

                                $this->sendResponse($email, true, 15);
                            }
                            else {

                                $this->sendResponse($email, false, 16);
                            }
                        }
                        else {

                            $this->sendResponse($email, false, 16);
                        }
                    }
                    else {

                        $this->sendResponse($email, false, 16);
                    }

                }
                else {

                    $this->sendResponse(null, false, 16);
                }
            }
            else {

                $this->sendResponse(null, false, 16);
            }
        }
        else {

            $this->sendResponse(null, false, 16);
        }
    }

    public function sendResponse($email, $bool, $code)
    {

        echo json_encode([
            'email' => $email,
            'response' => $this->langData[$code],
            'result' => $bool,
            'code' => $code
        ]);

        exit();
    }

    public function forgotPass()
    {

        $email = $_POST['email'];

        $raw = $this->db->dbCall('get_token', [$email]);

        if ($raw){

            $url = $this->protocol.$this->server.'/'.base64_encode($raw['token']);

            $this->sendEmail('forgot', $this->langData[17], 'uyem.ru', 's', '');
        }
        else {

            $code = 6;

            echo json_encode([
                'email' => $email,
                'response' => $email .' '.$this->langData[$code],
                'result' => false,
                'code' => $code
            ]);
        }
    }
}
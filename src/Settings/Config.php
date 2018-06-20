<?php
/**
 * Created by kolserdav
 * User: Sergey Kol'miller
 * Date: 19.06.2018
 * Time: 23:06
 */

namespace Avir\Login\Settings;

use Avir\Database\Modules\DB;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Yaml\Yaml;

class Config
{

    public $db;

    public $root;

    public $emailSettings;

    public $server;

    public $protocol;

    public $lang;

    public $langData;

    public function __construct()
    {

        $this->server = $_SERVER['SERVER_NAME'];

        if (empty($this->db)){

            $this->db = new DB();
        }
        if (empty($this->emailSettigs)){

            $this->emailSettings = Yaml::parseFile($this->getRoot().'/config/login/email-settings.yaml');
        }
        if (empty($this->protocol)){

            $this->protocol = Yaml::parseFile($this->getRoot().'/config/env.yaml')['protocol'];
        }
        if (empty($this->lang)){

            $this->lang = Yaml::parseFile($this->getRoot().'/config/env.yaml')['lang'];
        }
        if (empty($this->langData)){

            $this->langData = Yaml::parseFile($this->getRoot().'/config/login/langs/'.$this->lang.'.yaml');
        }
    }

    public function sendEmail($section, $themeMessage, $name, $bodyMessage, $address)
    {
        $mail = new PHPMailer;

        $mail->isSMTP();
        $mail->SMTPDebug = 2;

        $mail->setFrom($this->emailSettings[$section]['set-from']['address'], $this->emailSettings[$section]['set-from']['name']);

        $mail->addAddress($address, $name);

        $mail->Username = $this->emailSettings[$section]['username'];

        $mail->Password = $this->emailSettings[$section]['password'];

        $mail->CharSet = $this->emailSettings[$section]['charset'];

        $mail->Host = $this->emailSettings[$section]['host'];

        $mail->Subject = $themeMessage;

        $mail->Body = $bodyMessage;

        $mail->SMTPAuth = true;

        $mail->SMTPSecure = $this->emailSettings[$section]['smtpsecure'];

        $mail->Port = $this->emailSettings[$section]['port'];

        $mail->isHTML(true);

        if(!$mail->send()) {

            return "Email not sent. " . $mail->ErrorInfo . PHP_EOL;
        }
        else {

            return "Email sent!" . PHP_EOL;
        }
    }

    public function getRoot()
    {
        if (empty($this->root)) {

            preg_match("%.*src%", dirname(__DIR__), $m);

            return $this->root = preg_filter('%.{1}src%', '', $m[0]);
        }
        else {

            return $this->root;
        }
    }
}
<?php
namespace Hcode;

use PHPMailer;
use Rain\Tpl;
use SMTP;

class Mailer {

    const USERNAME = "cursophp7hcode@gmail.com";
    const PASSWORD = "<?password?>";
    const NAME_FROM = "Hcode Store";
    private $mail;

    public function __construct($toAdress, $toName, $subject, $html, $tplName, $data = array()) {

        $config = array(
            "tpl_dir"   => $_SERVER['DOCUMENT_ROOT']."/views/email/",
            "cache_dir" => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
            "debug"     => false
        );

        Tpl::configure( $config );

        $tpl = new Tpl();

        foreach ($data as $key => $val){
            $tpl->assign( $key, $val);
        }

        $html = $tpl->draw($tplName, true);
        
        $this->mail = new \PHPMailer();

        $this->mail->isSMTP();

        $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;

        $this->mail->Host = 'smtp.gmail.com';

        $this->mail->Port = 587;

        // $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $this->mail->SMTPAuth = true;

        $this->mail->Username = Mailer::USERNAME;

        $this->mail->Password = Mailer::PASSWORD;

        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

        $this->mail->addReplyTo('replyto@example.com', 'First Last');

        $this->mail->addAddress($toAdress, $toName);

        $this->mail->Subject = $subject;

        $this->mail->msgHTML($html, __DIR__);

        $this->mail->AltBody = 'This is a plain-text message body';

        $this->mail->addAttachment('images/phpmailer_mini.png');
    }

    public function send(){
        return $this->mail->send();
    }
}
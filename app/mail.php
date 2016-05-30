<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/6
 * Time: 15:38
 */
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

function send_mail($to,$title,$content){
    $mail = new Message;
    $mail->setFrom( '一个放羊娃 <lllsssvvv@163.com>')
        ->addTo($to)
        ->setSubject($title)
        ->setBody($content);

    $mailer = new SmtpMailer(array(
        'host' => 'smtp.163.com',
        'username' => 'lllsssvvv@163.com',
        'password' => 'a137993132',
        'secure' => 'ssl',
    ));
    $rs = $mailer->send($mail);
    if($rs){
        return true;
    }else{
        return false;
    }
}
?>


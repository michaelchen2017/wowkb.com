<?php
require 'PHPMailerAutoload.php';

$mail = new PHPMailer;

// $mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'wowjob2017@gmail.com';                 // SMTP username
$mail->Password = 'Abc1234567';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->setFrom('michael@wowkb.com', 'michael chen');
// $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
$mail->addAddress('chenzixing2009@gmail.com');               // Name is optional
$mail->addAddress('michaeljobhunting@gmail.com');
// $mail->addReplyTo('info@example.com', 'Information');
// $mail->addCC('cc@example.com');
// $mail->addBCC('bcc@example.com');

// $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

// $mail->SMTPOptions = array(
// 		'ssl' => array(
// 				'verify_peer' => false,
// 				'verify_peer_name' => false,
// 				'allow_self_signed' => true
// 		)
// );

$mail->Subject = 'Here is the subject: wowkb.com02';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}

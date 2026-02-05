<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1️⃣ CRIA O OBJETO PRIMEIRO
$mail = new PHPMailer(true);

// 2️⃣ AGORA SIM CONFIGURA
$mail->SMTPDebug = 2; // mostra logs
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'sandrell560@gmail.com';
$mail->Password = 'gideeccvrzsrwwso';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// 3️⃣ EMAIL
$mail->setFrom('SEU_EMAIL@gmail.com', 'Teste SMTP');
$mail->addAddress('SEU_EMAIL@gmail.com');

$mail->Subject = 'Teste SMTP';
$mail->Body    = 'Funcionou!';

$mail->send();

echo '✅ Email enviado com sucesso!';
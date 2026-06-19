<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function enviarEmail($para, $assunto, $mensagemHTML) {

    $mail = new PHPMailer(true);

    try {
        // =========================
        // CONFIG SMTP GMAIL
        // =========================
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // 🔴 SUA CONTA DO SISTEMA (NÃO DO USUÁRIO)
        $mail->Username = 'cristhianferreiraleide@gmail.com';
        $mail->Password = 'SUA_SENHA_DE_APP_DO_GMAIL';

        // Segurança correta
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        // =========================
        // REMETENTE (TEM QUE SER IGUAL AO LOGIN)
        // =========================
        $mail->setFrom('cristhianferreiraleide@gmail.com', 'Sistema');

        // Destinatário (usuário que digitou email)
        $mail->addAddress($para);

        // =========================
        // CONTEÚDO
        // =========================
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagemHTML;

        // =========================
        // ENVIO
        // =========================
        $mail->send();
        return true;

    } catch (Exception $e) {
        // 🔥 MOSTRA O ERRO REAL (IMPORTANTE PARA DEBUG)
        echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
        return false;
    }
}
?>
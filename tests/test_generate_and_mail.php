<?php
// Test script: generate PDF and prepare email MIME without sending
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config-email.php';

mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

// Sample data with accents
$data = [
    'proposal_id' => 'TEST' . date('YmdHis'),
    'generated_at' => date('Y-m-d H:i'),
    'name' => 'José da Silva',
    'enterprise' => 'Empresa Ágil',
    'project_name' => 'Edifício São João',
    'tel' => '+55 11 98888-8888',
    'email' => 'test@example.com',
    'initial' => '2026-02-10',
    'finale' => '2026-12-10',
    'address' => 'Av. Paulista, 1000',
    'services' => ['PCE', 'Ensaios de Desempenho'],
    'description' => "Descrição com acentuação: ação, São, í ú â",
    'company_contact' => $config['to_email'] ?? 'comercial@seudominio.com',
];

// Render HTML from template
ob_start();
include __DIR__ . '/../template-pdf.php';
$html = ob_get_clean();

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$output = $dompdf->output();
$tmpDir = sys_get_temp_dir();
$tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'proposta_' . $data['proposal_id'] . '.pdf';
file_put_contents($tmpFile, $output);

echo "PDF gerado: $tmpFile\n";

// Prepare PHPMailer but do not send; write MIME to file
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);
    $mail->Subject = 'Teste de proposta: ' . $data['proposal_id'];
    $mail->Body = "Teste com acentuação: ação, São Paulo, João";
    $mail->AltBody = strip_tags($mail->Body);
    $mail->addAttachment($tmpFile, 'Proposta_test.pdf');

    if ($mail->preSend()) {
        $mime = $mail->getSentMIMEMessage();
        if (!is_dir(__DIR__)) mkdir(__DIR__, 0777, true);
        $out = __DIR__ . '/test_email.eml';
        file_put_contents($out, $mime);
        echo "MIME salvo: $out\n";
    } else {
        echo "preSend falhou: " . $mail->ErrorInfo . "\n";
    }

    echo "Corpo do e-mail: " . $mail->Body . "\n";

} catch (Exception $e) {
    echo "Erro ao preparar e‑mail: " . $e->getMessage() . "\n";
}

// Nota: o PDF permanece em tmp, apague manualmente se quiser


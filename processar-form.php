<?php
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

// Garantir codificação UTF-8
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');
header('Content-Type: text/html; charset=utf-8');

$config = require __DIR__ . '/config-email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método não permitido';
    exit;
}

// Helper sanitizers and encoding helpers
function to_utf8_normalized(string $s): string {
    // Convert from common encodings to UTF-8 and normalize (NFC) if available
    $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
    if (class_exists('Normalizer')) {
        $s = Normalizer::normalize($s, Normalizer::FORM_C);
    }
    // Remove control characters that could break output (keep newline/tab)
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s);
    return $s;
}

function clean_text($k): string {
    $v = $_POST[$k] ?? '';
    $v = trim((string)$v);
    return to_utf8_normalized($v);
}

function clean_optional($k): string {
    return isset($_POST[$k]) ? clean_text($k) : '';
}

// Collect and sanitize
$client_name = clean_text('client_name');
$enterprise = clean_text('enterprise');
$tel = clean_text('tel');
$project_name = clean_optional('project_name');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$initial = clean_optional('initial');
$finale = clean_optional('finale');
$address = clean_optional('address');
$description = isset($_POST['esp_servico']) ? trim($_POST['esp_servico']) : '';

$services = [];
if (!empty($_POST['servico'])) {
    if (is_array($_POST['servico'])) {
        foreach ($_POST['servico'] as $s) {
            $sClean = trim(to_utf8_normalized((string)$s));
            if ($sClean !== '') $services[] = $sClean;
        }
    } else {
        $sClean = trim(to_utf8_normalized((string)$_POST['servico']));
        if ($sClean !== '') $services[] = $sClean;
    }
}

$errors = [];
if ($client_name === '') $errors[] = 'Por favor, informe seu nome.';
if ($enterprise === '') $errors[] = 'Informe o nome da sua empresa.';
// Validação de tamanho para o nome da empresa (máx 100 caracteres)
if ($enterprise !== '' && mb_strlen($enterprise) > 100) $errors[] = 'O nome da empresa deve ter no máximo 100 caracteres.';

$digitsTel = preg_replace('/\D+/', '', $tel);
if ($digitsTel === '') $errors[] = 'Informe um número de celular para contato.';
elseif (mb_strlen($digitsTel) < 10) $errors[] = 'O telefone parece incompleto.';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail válido.';

if (empty($services)) $errors[] = 'Selecione pelo menos um serviço.';

if ($initial !== '' && $finale !== '') {
    $d1 = strtotime($initial);
    $d2 = strtotime($finale);
    if ($d1 === false || $d2 === false) $errors[] = 'Datas inválidas.';
    elseif ($d1 > $d2) $errors[] = 'A data de início não pode ser posterior à data de término.';
}

if (!empty($errors)) {
    // Retorna erros simples (poderia redirecionar com sessão)
    http_response_code(422);
    echo '<h2>Erros no envio</h2><ul>' . implode('', array_map(fn($e) => '<li>' . htmlspecialchars($e) . '</li>', $errors)) . '</ul>';
    echo '<p><a href="index.html">Voltar ao formulário</a></p>';
    exit;
}

// Monta dados para o PDF
$data = [
    'proposal_id' => date('YmdHis') . rand(100,999),
    'generated_at' => date('Y-m-d H:i'),
    'name' => $client_name,
    'enterprise' => $enterprise,
    'project_name' => $project_name,
    'tel' => $tel,
    'email' => $email,
    'initial' => $initial,
    'finale' => $finale,
    'address' => $address,
    'services' => $services,
    'description' => $description,
    'company_contact' => $config['to_email'] ?? ($config['from_email'] ?? 'comercial@seudominio.com'),
];

// Normalize all strings in $data to UTF-8 NFC to ensure accents render correctly
function normalize_data_array(array $arr): array {
    array_walk_recursive($arr, function(&$v) {
        if (is_string($v)) $v = to_utf8_normalized($v);
    });
    return $arr;
}

$data = normalize_data_array($data);

// Gera HTML a partir do template
ob_start();
include __DIR__ . '/template-pdf.php';
$html = ob_get_clean();

// Gerar PDF com DOMPDF
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

// Envia e-mail com PHPMailer
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';
try {
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['port'];

    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);

    $mail->Subject = 'Nova proposta: ' . ($data['proposal_id'] ?? 'Proposta');
    $mail->Body = "Nova solicitação de proposta recebida.";

    $mail->AltBody = strip_tags($mail->Body);
    $mail->addAttachment($tmpFile, 'Proposta.pdf');

    // Em modo de teste (`define('TEST_MODE', true);`) não enviamos, apenas preparamos o MIME e salvamos para inspeção
    if (defined('TEST_MODE') && TEST_MODE) {
        if ($mail->preSend()) {
            $mime = $mail->getSentMIMEMessage();
            $out = __DIR__ . '/tests/test_email_from_post.eml';
            file_put_contents($out, $mime);
        } else {
            throw new Exception('preSend falhou: ' . $mail->ErrorInfo);
        }
    } else {
        $mail->send();
        // Remove o arquivo temporário após envio
        if (file_exists($tmpFile)) @unlink($tmpFile);
    }

    // Mensagem de sucesso
  ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Proposta enviada</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #2a1e0f, #171002);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
        }

        .card {
            background: #ffffff;
            max-width: 420px;
            width: 100%;
            padding: 40px 32px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,.25);
            text-align: center;
            animation: fadeIn .4s ease;
        }

        .icon {
            width: 72px;
            height: 72px;
            background: #22c55e;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 1.6rem;
            margin-bottom: 10px;
        }

        p {
            color: #475569;
            font-size: .95rem;
            margin-bottom: 28px;
        }

        a.button {
            display: inline-block;
            padding: 12px 28px;
            background: #fe8e05;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background .2s ease, transform .1s ease;
        }

        a.button:hover {
            background: #ce7100;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">✓</div>
    <h1>Proposta enviada com sucesso</h1>
    <p>
        Obrigado! Sua solicitação foi encaminhada ao setor comercial.<br>
        Em breve entraremos em contato.
    </p>
    <a href="index.html" class="button">Voltar ao formulário</a>
</div>

</body>
</html>
<?php
exit;

} catch (Exception $e) {
    // Em caso de erro, manter o PDF por segurança em tmp e mostrar erro
    $err = htmlspecialchars($e->getMessage());
    http_response_code(500);

    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro no envio</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #7f1d1d, #450a0a);
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #ffffff;
            max-width: 420px;
            width: 100%;
            padding: 40px 32px;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,.25);
            text-align: center;
            animation: fadeIn .4s ease;
        }

        .icon {
            width: 72px;
            height: 72px;
            background: #dc2626;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 1.6rem;
            margin-bottom: 10px;
            color: #7f1d1d;
        }

        p {
            color: #475569;
            font-size: .95rem;
            margin-bottom: 28px;
        }

        a.button {
            display: inline-block;
            padding: 12px 28px;
            background: #dc2626;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }

        a.button:hover {
            background: #991b1b;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="icon">!</div>
    <h1>Erro ao enviar proposta</h1>
    <p>
        Ocorreu um problema ao enviar sua solicitação.<br>
        Nossa equipe já foi notificada.
    </p>
    <a href="index.html" class="button">Voltar ao formulário</a>
</div>

</body>
</html>
<?php
exit;

}




<?php
// template-pdf.php
// Recebe um array $data e produz HTML pronto para DOMPDF.
if (!isset($data) || !is_array($data)) {
    echo '<p>Template requires $data array.</p>';
    return;
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$servicesList = '';
if (!empty($data['services']) && is_array($data['services'])) {
    $servicesList = '<ul>' . implode('', array_map(function($s){ return '<li>' . e($s) . '</li>'; }, $data['services'])) . '</ul>';
} else {
    $servicesList = '<p>—</p>';
}

// Preparar logo como data URI (prioriza arquivos comuns e fallback)
$logoDataUri = '';
$logoFileCandidates = [
    __DIR__ . '/assets/Logo-tecnol-principal.gif',
    __DIR__ . '/assets/Logo-tecnol-principal.png',
    __DIR__ . '/assets/Logo-tecnol-principal.jpg',
    __DIR__ . '/assets/Logo-tecnol-neutra.gif', // fallback existente
];
foreach ($logoFileCandidates as $lf) {
    if (file_exists($lf) && is_readable($lf)) {
        $mime = function_exists('mime_content_type') ? mime_content_type($lf) : 'image/gif';
        $logoData = base64_encode(file_get_contents($lf));
        $logoDataUri = "data:$mime;base64,$logoData";
        break;
    }
}

?><!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Proposta</title>
  <style>
    body{font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#222;}
    .header{display:flex;align-items:center;justify-content:space-between;border-bottom:4px solid #f28c2c;padding-bottom:12px;margin-bottom:18px}
    .company{font-size:18px;font-weight:700;color:#f28c2c}
    .meta{font-size:12px;color:#666}
    .section{margin-bottom:12px}
    .section h3{background:#f28c2c;color:#fff;padding:6px 8px;margin:0 0 8px;border-radius:4px;font-size:14px}
    table{width:100%;border-collapse:collapse}
    td{vertical-align:top;padding:6px;border:1px solid #eee}
    .two-col td{width:50%}
    ul{margin:0;padding-left:1.2em}
    .foot{font-size:11px;color:#666;margin-top:18px;border-top:1px solid #eee;padding-top:8px}
  </style>
</head>
<body>
  <div class="header">
    <div>
      <div class="company">
        <?php if ($logoDataUri): ?>
          <img src="<?= htmlspecialchars($logoDataUri, ENT_QUOTES) ?>" alt="Logo da Empresa" style="max-height:60px;display:inline-block;vertical-align:middle;margin-right:8px;" />
        <?php endif; ?>
      </div>
    </div>
    <div style="text-align:right">
      <div style="font-size:12px;color:#444">Solicitação</div>
      <div style="font-weight:700;font-size:16px">#<?php echo e($data['proposal_id'] ?? 'N/A'); ?></div>
    </div>
  </div>

  <div class="section">
    <h3>Dados do Cliente</h3>
    <table class="two-col">
      <tr>
        <td><strong>Nome:</strong> <?php echo e($data['name'] ?? ''); ?></td>
        <td><strong>Empresa:</strong> <?php echo e($data['enterprise'] ?? ''); ?></td>
      </tr>
      <tr>
        <td><strong>Telefone:</strong> <?php echo e($data['tel'] ?? ''); ?></td>
        <td><strong>E-mail:</strong> <?php echo e($data['email'] ?? ''); ?></td>
      </tr>
    </table>
  </div>

  <div class="section">
    <h3>Dados do Empreendimento</h3>
    <table>
      <tr><td><strong>Endereço:</strong></td><td><?php echo e($data['address'] ?? ''); ?></td></tr>
      <tr><td><strong>Nome do empreendimento:</strong></td><td><?php echo e($data['project_name'] ?? ''); ?></td></tr>
    </table>
  </div>

  <div class="section">
    <h3>Período</h3>
    <table>
      <tr><td><strong>Início:</strong></td><td><?php echo e($data['initial'] ?? ''); ?></td></tr>
      <tr><td><strong>Término:</strong></td><td><?php echo e($data['finale'] ?? ''); ?></td></tr>
    </table>
  </div>

  <div class="section">
    <h3>Serviços Solicitados</h3>
    <?php echo $servicesList; ?>
  </div>

  <div class="section">
    <h3>Descrição da Necessidade</h3>
    <div style="border:1px solid #eee;padding:10px;border-radius:4px;background:#fafafa;min-height:60px"><?php echo nl2br(e($data['description'] ?? '')); ?></div>
  </div>

  <div class="foot">
    <div><strong>Contato Comercial:</strong> <?php echo e($data['company_contact'] ?? 'c.edu31@hotmail.com'); ?></div>
    <div>Este documento foi gerado automaticamente a partir do formulário de propostas.</div>
  </div>
</body>
</html>

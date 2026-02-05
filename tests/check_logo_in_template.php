<?php
// Check if template embeds the logo as data URI
require __DIR__ . '/../vendor/autoload.php';

$data = [
    'proposal_id' => 'CHECK' . date('YmdHis'),
    'generated_at' => date('Y-m-d H:i'),
    'name' => 'Teste Logo',
    'enterprise' => 'Empresa Teste',
    'project_name' => 'Teste',
    'tel' => '0000',
    'email' => 't@t.com',
    'initial' => '2026-01-01',
    'finale' => '2026-12-31',
    'address' => 'Rua Teste',
    'services' => ['PCE'],
    'description' => 'Descrição',
    'company_contact' => 'comercial@teste.local',
    'company_name' => 'Minha Empresa'
];

ob_start();
include __DIR__ . '/../template-pdf.php';
$html = ob_get_clean();

$found = false;
if (strpos($html, 'src="data:') !== false || strpos($html, "src='data:") !== false) {
    $found = true;
}

echo $found ? "Logo data URI encontrada no HTML.\n" : "Logo NÃO encontrada no HTML.\n";
// Optionally print a short snippet
if ($found) {
    $pos = strpos($html, 'data:');
    echo substr($html, $pos, 200) . "\n";
}

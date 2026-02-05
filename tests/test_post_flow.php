<?php
// Simula um POST ao script principal em modo de teste (não envia e-mail, gera .eml)
define('TEST_MODE', true);

// Simula ambiente de requisição
$_SERVER['REQUEST_METHOD'] = 'POST';

// Campos mínimos necessários para o envio
$_POST = [
    'client_name' => 'María Núñez',
    'enterprise' => 'Construções São José',
    'tel' => '+55 (11) 91234-5678',
    'project_name' => 'Residencial Colinas',
    'email' => 'maria@example.com',
    'initial' => '2026-03-01',
    'finale' => '2026-09-30',
    'address' => 'Rua das Flores, 123',
    'esp_servico' => "Detalhes com acentuação: ação, São, João",
    'servico' => ['PCE', 'Ensaios de Desempenho']
];

// Incluir o processador (ele fará a validação, gerará o PDF e salvará o .eml em tests/)
// Note: o processar-form.php usa exit() após imprimir HTML; isso é esperado.
include __DIR__ . '/../processar-form.php';

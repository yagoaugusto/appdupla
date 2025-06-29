<?php
require_once '#_global.php';

header('Content-Type: application/json');

$quadra_id = $_GET['quadra_id'] ?? null;
$data = $_GET['data'] ?? null;
$hora = $_GET['hora'] ?? null;

if (!$quadra_id || !$data || !$hora) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros ausentes.']);
    exit;
}

// Busca valor base da quadra
$quadra = Quadras::getQuadraById($quadra_id);
if (!$quadra) {
    echo json_encode(['success' => false, 'message' => 'Quadra não encontrada.']);
    exit;
}

$valor_base = (float)$quadra['valor_base'];

// Converte dia da semana
$dia_semana = strtolower(date('l', strtotime($data)));
$dias_map = [
    'monday' => 'segunda',
    'tuesday' => 'terca',
    'wednesday' => 'quarta',
    'thursday' => 'quinta',
    'friday' => 'sexta',
    'saturday' => 'sabado',
    'sunday' => 'domingo'
];
$dia_pt = $dias_map[$dia_semana] ?? null;

if (!$dia_pt) {
    echo json_encode(['success' => false, 'message' => 'Dia da semana inválido.']);
    exit;
}

// Busca valor adicional
$valor_adicional = Quadras::getValorAdicionalPorSlot($quadra_id, $dia_pt, $hora);

// Total
$total = $valor_base + $valor_adicional;

echo json_encode([
    'success' => true,
    'valor_base' => $valor_base,
    'valor_adicional' => $valor_adicional,
    'total' => $total
]);
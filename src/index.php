<?php
/**
 * Back-end Challenge.
 *
 * PHP version 7.4
 *
 * Este será o arquivo chamado na execução dos testes automatizados.
 *
 * @category Challenge
 * @package  Back-end
 * @author   Osmany Lima <osmanylima14@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @link     https://github.com/apiki/back-end-challenge
 */
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

const CURRENCY_SYMBOLS = [
    'BRL' => 'R$',
    'USD' => '$',
    'EUR' => '€',
];

const SUPPORTED_CURRENCIES = ['BRL', 'USD', 'EUR'];

/**
 * Valida os parâmetros de entrada, verificando se são numéricos e maiores que zero.
 *
 * @param float $amount Quantidade a ser convertida.
 * @param float $rate   Taxa de conversão.
 *
 * @return void
 */
function validateParameters(float $amount, float $rate): void 
{
    if ($amount <= 0 || $rate <= 0) {
        sendErrorResponse("Parâmetros inválidos.");
    }
}

/**
 * Valida as moedas fornecidas.
 *
 * @param string $from Moeda de origem.
 * @param string $to   Moeda de destino.
 *
 * @return void
 */
function validateCurrencies(string $from, string $to): void 
{
    if (!in_array($from, SUPPORTED_CURRENCIES) || $from === $to) {
        sendErrorResponse("Moeda de origem ou destino inválida.");
    }
}

/**
 * Envia uma resposta de erro JSON com o código HTTP fornecido.
 *
 * @param string $message Mensagem de erro.
 * @param int    $code    Código de resposta HTTP.
 *
 * @return void
 */
function sendErrorResponse(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(["error" => $message]);
    exit;
}

/**
 * Converte o valor da moeda de acordo com os parâmetros especificados.
 *
 * @param float  $amount Valor a ser convertido.
 * @param string $from   Moeda de origem (ex.: "BRL").
 * @param string $to     Moeda de destino (ex.: "USD").
 * @param float  $rate   Taxa de conversão.
 *
 * @return array Valor convertido e símbolo da moeda.
 */
function convertCur(float $amount, string $from, string $to, float $rate): array 
{
    $valorConvertido = ($from === 'BRL' || $to === 'BRL') 
    ? (($from === 'BRL') ? $amount * $rate : $amount / $rate) 
    : 0;

    if ($from === 'BRL' && $to === 'USD') {
        $valorConvertido = $amount * $rate;
    } elseif ($from === 'BRL' && $to === 'EUR') {
        $valorConvertido = $amount * $rate;
    } elseif ($from === 'USD' && $to === 'BRL') {
        $valorConvertido = $amount * $rate; // Correção: deve multiplicar
    } elseif ($from === 'EUR' && $to === 'BRL') {
        $valorConvertido = $amount * $rate; // Se for necessário
    } elseif ($from === 'EUR' && $to === 'USD') {
        $valorConvertido = $amount * $rate; // Se for necessário
    } else {
        sendErrorResponse("Moeda de destino inválida.");
    }

    return [
        "valorConvertido" => round($valorConvertido, 2),
        "simboloMoeda" => CURRENCY_SYMBOLS[$to] ?? ''
    ];
}

// Captura a URI e define o padrão da rota
header('Content-Type: application/json');
$uri = $_SERVER['REQUEST_URI'];
$pattern = "/^\/exchange\/([0-9.]+)\/([A-Z]{3})\/([A-Z]{3})\/([0-9.]+)$/" ;

// Verifica se a URL corresponde ao padrão esperado
if (!preg_match($pattern, $uri)) {
    // Resposta para parâmetros insuficientes
    sendErrorResponse("Parâmetros insuficientes.", 400);
}

if (preg_match($pattern, $uri, $matches)) {
    list(, $amount, $from, $to, $rate) = $matches;

    // Validação dos parâmetros e moedas
    validateParameters((float) $amount, (float) $rate);
    validateCurrencies($from, $to);

    // Realiza a conversão e retorna a resposta JSON
    echo json_encode(convertCur((float) $amount, $from, $to, (float) $rate));
} else {
    // Resposta para endpoint não encontrado
    sendErrorResponse("Endpoint não encontrado ou parâmetros insuficientes.", 404);
}

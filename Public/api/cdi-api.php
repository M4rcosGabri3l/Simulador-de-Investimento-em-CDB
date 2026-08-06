<?php

// Gerenciamento de Erros
require_once "C:/xampp/htdocs/CURSO_PHP/CDB_System/Erro-Handler/Erro_Handler_CDB.php";

header(
    'Content-Type: application/json'
);

/**
 * recebe JSON
 */
$dados = json_decode(
    file_get_contents(
        "php://input"
    ),
    true
);

/**
 * datas
 */
$data_inicial =
$dados['data_inicial'] ?? '';

$data_final =
$dados['data_final'] ?? '';

require_once "C:/xampp/htdocs/CURSO_PHP/CDB_System/Modules/Validate/Validate_Post.php";
$Validate = new Validate_Post();

$Erro_Data = $Validate->validate_data(
    $data_inicial,
    $data_final
);

/**
 * retorna erros
 */
if (!empty($Erro_Data)) {

    http_response_code(400);

    echo json_encode([

        'status' => 'erro',

        'erros' => $Erro_Data
    ]);

    exit;
}

/**
 * data atual
 */
$hoje = new DateTime();

/**
 * valida formato
 */
$inicio =
DateTime::createFromFormat(
    'Y-m-d',
    $data_inicial
);

$fim =
DateTime::createFromFormat(
    'Y-m-d',
    $data_final
);

/**
 * tipo da simulação
 */
if ($fim <= $hoje) {

    $tipo = 'historico';

    /**
     * período informado
     */
    $dataInicial =
    $inicio->format('d/m/Y');

    $dataFinal =
    $fim->format('d/m/Y');

    $url =
    "https://api.bcb.gov.br/dados/serie/bcdata.sgs.12/dados?formato=json&dataInicial={$dataInicial}&dataFinal={$dataFinal}";
}
else {

    $tipo = 'projecao';

    /**
     * CDI atual
     */
    $url =
    "https://api.bcb.gov.br/dados/serie/bcdata.sgs.12/dados/ultimos/1?formato=json";
}

/**
 * busca API
 */
$response =
file_get_contents($url);

if ($response === false) {

    echo json_encode([

        'status' => 'erro',

        'erros' => [

            'API_Erro' => [

                'Não foi possível consultar a API do Banco Central.'
            ]
        ]
    ]);

    exit;
}

$dados =
json_decode(
    $response,
    true
);

/**
 * calcula CDI
 */
if ($tipo === 'historico') {

    if (empty($dados)) {

        echo json_encode([

            'status' => 'erro',

            'erros' => [

                'API_Erro' => [

                    'Nenhum dado de CDI encontrado para o período informado.'
                ]
            ]
        ]);

        exit;
    }

    $soma = 0;

    foreach ($dados as $registro) {

        $soma += (float)$registro['valor'];
    }

    $media_diaria =
    $soma / count($dados);

    $cdi_anual =
    (
        pow(
            1 + ($media_diaria / 100),
            252
        ) - 1
    ) * 100;

    $cdi = round(
        $cdi_anual,
        2
    );
    


} else {

    /**
     * CDI atual
     */
    $taxa_diaria =
    (float)$dados[0]['valor'];

    $cdi =
    (
        pow(
            1 + ($taxa_diaria / 100),
            252
        ) - 1
    ) * 100;

    $cdi = round(
        $cdi,
        7
    );
}

/**
 * Retorna o resultado
 */
echo json_encode([

    'status' => 'sucesso',

    'tipo' => $tipo,

    'cdi' => $cdi,

    'registros' => $tipo === 'historico'
        ? count($dados)
        : 1
]);
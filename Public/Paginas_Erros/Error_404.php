<?php

http_response_code(500);

$codigo = $_GET['codigo'] ?? 500;

$mensagens = [

    400 => 'A solicitação não pôde ser processada.',

    404 => 'A página que você procura não foi encontrada.',

    500 => 'Ocorreu um erro interno no sistema.'
];

$mensagem =
    $mensagens[$codigo]
    ?? $mensagens[500];

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Erro | Simulador de CDB
    </title>

    <link
        rel="stylesheet"
        href="/CURSO_PHP/CDB_System/Public/css/erro_page.css"
    >

</head>

<body>

    <main class="pagina-erro">

        <div class="erro">

            <h1>
                <?= htmlspecialchars(
                    $codigo,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h1>

            <h2>
                Ocorreu um problema
            </h2>

            <p>
                <?= htmlspecialchars(
                    $mensagem,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <a
                href="/CURSO_PHP/CDB_System/Public/"
                class="botao-voltar"
            >
                Voltar para o início
            </a>

        </div>

    </main>

</body>

</html>
<?php 

// Gerenciamento de Erros
require_once "../Erro-Handler/Erro_Handler_CDB.php";

// Carregando Module
require_once "../Modules/CDB_Logic.php";

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/cdb.css">

    <link rel="icon" href="icon/banpara-logo.png" type="image/png">

    <title>Calculo CDB V.1</title>
</head>

<body>

    <div class="brand-header">
        <img src="icon/banpara-logo.png" alt="Logo Banpará">
        <h1>Cálculo de CDB</h1>
    </div>

    <div class='form-body'>

        <form action="<?= $_SERVER['REQUEST_URI']; ?>" method="post">

            <p>
                <label for="valor">Valor do Investimento:</label>
                <input type="text" name="valor" id="valor" placeholder="Valor"
                    <?php if (!isset($Erros_Formulario['Valor_Erro']) && isset($Validate)): ?>
                    value="<?= htmlspecialchars($Validate->Valor, ENT_QUOTES, 'UTF-8') ?>"
                    <?php endif; ?>>
            </p>

            <?php if (isset($Erros_Formulario['Valor_Erro'])): ?>
                <?php foreach ($Erros_Formulario['Valor_Erro'] as $Erro_Valor): ?>
                    <p><span><?= htmlspecialchars($Erro_Valor, ENT_QUOTES, 'UTF-8') ?></span></p>
                <?php endforeach; ?>
            <?php endif; ?>


             <p>
                <label for="data_inicial">
                    Data Inicial:
                </label>

                <input
                    type="date"
                    name="data_inicial"
                    id="data_inicial"

                    <?php if (
                        !isset($Erros_Formulario['Data_Erro']) &&
                        isset($Validate)
                    ): ?>

                        value="<?= htmlspecialchars(
                            $Validate->Data_Inicial,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"

                    <?php endif; ?>
                >
            </p>


        <p>
            <label for="data_final">
                Data Final:
            </label>

            <input
                type="date"
                name="data_final"
                id="data_final"

                <?php if (
                    !isset($Erros_Formulario['Data_Erro']) &&
                    isset($Validate)
                ): ?>

                    value="<?= htmlspecialchars(
                        $Validate->Data_Final,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"

                <?php endif; ?>
            >
        </p>


        <select
            name="tipo_cdb"
            id="tipo_cdb"
        >

            <option
                value="posfixado"

                <?php
                if (
                    isset($Validate) &&
                    $Validate->Tipo_CDB === 'posfixado'
                ) {
                    echo 'selected';
                }
                ?>
            >
                Pós-fixado (% CDI)
            </option>

            <option
                value="prefixado"

                <?php
                if (
                    isset($Validate) &&
                    $Validate->Tipo_CDB === 'prefixado'
                ) {
                    echo 'selected';
                }
                ?>
            >
                Prefixado
            </option>

        </select>
      
        <div id="campos_cdi">
            <p>
                <label for="cdi">% do CDI:</label>
                <input type="text"
                name="cdi"
                id="cdi"
                placeholder="Ex: 110"
                    <?php if (!isset($Erros_Formulario['CDI_Erro']) && isset($Validate)): ?>
                    value="<?= htmlspecialchars($Validate->CDI, ENT_QUOTES, 'UTF-8') ?>"
                    <?php endif; ?>>
            </p>

            <?php if (isset($Erros_Formulario['CDI_Erro'])): ?>
                <?php foreach ($Erros_Formulario['CDI_Erro'] as $Erro_CDI): ?>
                    <p><span><?= htmlspecialchars($Erro_CDI, ENT_QUOTES, 'UTF-8') ?></span></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <p>
                <label for="cdi_atual">CDI Atual (%):</label>
                <input type="text"
                    name="cdi_atual"
                    id="cdi_atual"
                    placeholder="Carregando CDI..."
                    <?php if (!isset($Erros_Formulario['CDI_ATUAL_Erro']) && isset($Validate)): ?>
                    value="<?= htmlspecialchars($Validate->CDI_ATUAL, ENT_QUOTES, 'UTF-8') ?>"
                    <?php endif; ?>>
            </p>

            <p id="tipo_simulacao"></p>

            <?php if (isset($Erros_Formulario['CDI_ATUAL_Erro'])): ?>
                <?php foreach ($Erros_Formulario['CDI_ATUAL_Erro'] as $Erro_CDI_Atual): ?>
                    <p><span><?= htmlspecialchars($Erro_CDI_Atual, ENT_QUOTES, 'UTF-8') ?></span></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <div id="erro_data"></div>
        </div>


        <div
            id="campo_prefixado"
            style="display:none;"
        >

            <p>

                <label for="taxa_prefixada">
                    Taxa Anual (%):
                </label>

                <input
                    type="text"
                    name="taxa_prefixada"
                    id="taxa_prefixada"
                    placeholder="Ex: 15"
                    <?php if (!isset($Erros_Formulario['Taxa_Prefixada_Erro']) && isset($Validate)): ?>
                    value="<?= htmlspecialchars($Validate->Taxa_Prefixada, ENT_QUOTES, 'UTF-8') ?>"
                    <?php endif; ?>
                >

            </p>

            <?php if (isset($Erros_Formulario['Taxa_Prefixada_Erro'])): ?>
                <?php foreach ($Erros_Formulario['Taxa_Prefixada_Erro'] as $Erro_Taxa_Prefixada): ?>
                    <p><span><?= htmlspecialchars($Erro_Taxa_Prefixada, ENT_QUOTES, 'UTF-8') ?></span></p>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

            <div class='submit-form'>
                <p>
                    
                    <input type="submit" value="Simular">
                    
                </p>
            </div>

        </form>
    </div>

    <script>

        const dataInicial =
        document.getElementById(
            'data_inicial'
        );

        const dataFinal =
        document.getElementById(
            'data_final'
        );

        async function buscarCDI() {

            try {

                const response =
                await fetch('/CURSO_PHP/CDB_System/Public/api/cdi-api.php', {
                    method: 'POST',

                        headers: {
                            'Content-Type':
                            'application/json'
                        },

                        body: JSON.stringify({

                            data_inicial:
                            dataInicial.value,

                            data_final:
                            dataFinal.value
                        })
                    }
                );

                const dados =
                await response.json();

                console.log(dados);
                
                /**
                 * limpa erros antigos
                 */
                document
                .getElementById('erro_data')
                .innerHTML = '';

                /**
                 * erro
                 */
                if (!response.ok) {

                    const mensagens =
                    dados.erros?.Data_Erro || [
                        'Erro desconhecido.'
                    ];

                    alert(
                        mensagens.join('\n')
                    );

                    document
                    .getElementById('erro_data')
                    .innerHTML =
                    mensagens.join('<br>');

                    return;
                }

                /**
                 * sucesso
                 */
                document
                .getElementById(
                    'cdi_atual'
                )
                .value = dados.cdi;

                /**
                 * tipo simulação
                 */
                document
                .getElementById(
                    'tipo_simulacao'
                )
                .innerHTML =
                dados.tipo;

            }

            catch (erro) {

                console.error(erro);

                alert(
                    'Erro ao buscar CDI.'
                );
            }
        }

        /**
         * observa mudanças
         */
        dataInicial.addEventListener(
            'change',
            buscarCDI
        );

        dataFinal.addEventListener(
            'change',
            buscarCDI
        );

        const campoValor =
        document.getElementById('valor');

        campoValor.addEventListener(
            'input',
            function () {

                let valor =
                this.value.replace(/\D/g, '');

                valor =
                (Number(valor) / 100);

                this.value =
                valor.toLocaleString(
                    'pt-BR',
                    {
                        style: 'currency',
                        currency: 'BRL'
                    }
                );
            }
        );

    </script>

    <script>

        const tipoCDB =
        document.getElementById(
            'tipo_cdb'
        );

        const camposCDI =
        document.getElementById(
            'campos_cdi'
        );

        const campoPrefixado =
        document.getElementById(
            'campo_prefixado'
        );

        const cdi =
        document.getElementById(
            'cdi'
        );

        const cdiAtual =
        document.getElementById(
            'cdi_atual'
        );

        const taxaPrefixada =
        document.getElementById(
            'taxa_prefixada'
        );

        function atualizarCampos() {

            if (tipoCDB.value === 'prefixado') {

                campoPrefixado.style.display = 'block';
                camposCDI.style.display = 'none';

                taxaPrefixada.disabled = false;

                cdi.disabled = true;
                cdiAtual.disabled = true;

            } else {

                campoPrefixado.style.display = 'none';
                camposCDI.style.display = 'block';

                taxaPrefixada.disabled = true;

                cdi.disabled = false;
                cdiAtual.disabled = false;
            }
        }

        atualizarCampos();

        tipoCDB.addEventListener(
            'change',
            atualizarCampos
        );

    </script>


    <?php if (isset($Resultado_Simulacao)) { ?>

        <div class="resultados">

            <h2>
                Resultado da Simulação
            </h2>

            <p>
                <strong>Dias do investimento:</strong>

                <?= htmlspecialchars(
                    $Resultado_Simulacao['dias'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>Rentabilidade anual:</strong>

                <?= htmlspecialchars(
                    $Resultado_Simulacao['rentabilidade'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>%
            </p>

            <p>
                <strong>Valor bruto final:</strong>

                R$
                <?= htmlspecialchars(
                    $Resultado_Simulacao['valor_bruto'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>Imposto de renda:</strong>

                <?= htmlspecialchars(
                    $Resultado_Simulacao['ir'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>%
            </p>

            <p>
                <strong>Valor líquido final:</strong>

                R$
                <?= htmlspecialchars(
                    $Resultado_Simulacao['valor_liquido'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                <strong>Lucro líquido:</strong>

                R$
                <?= htmlspecialchars(
                    $Resultado_Simulacao['lucro_liquido'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <div class="grafico-container">

          <canvas id="graficoEvolucao"></canvas>

        </div>

        <script>

            const evolucao =
            <?= json_encode(
                $Resultado_Simulacao['evolucao']
            ) ?>;

            console.log(evolucao);

                const labels =
            evolucao.map(
                item => `${item.dia} dias`
            );

            const valores =
            evolucao.map(
                item => item.valor
            );

            const ctx =
            document.getElementById(
                'graficoEvolucao'
            );

            new Chart(ctx, {

                type: 'line',

                data: {

                    labels: labels,

                    datasets: [{

                        label: 'Valor Acumulado (R$)',

                        data: valores,

                        fill: true,

                        tension: 0.4,

                        borderWidth: 4,

                        borderColor: '#0f172a',

                        backgroundColor:
                        'rgba(15, 23, 42, 0.15)',

                        pointRadius: 5,

                        pointHoverRadius: 8
                    }]
                },

                options: {

                    responsive: true,

                    plugins: {

                        title: {

                            display: true,

                            text:
                            'Evolução do Investimento',

                            font: {

                                size: 24,

                                weight: 'bold'
                            },

                            padding: {

                                top: 10,

                                bottom: 20
                            }
                        },

                        legend: {

                            labels: {

                                font: {

                                    size: 16
                                }
                            }
                        },

                        tooltip: {

                            enabled: true
                        }
                    }
                }
            });
            

        </script>



    <?php } ?>

</body>
</html>
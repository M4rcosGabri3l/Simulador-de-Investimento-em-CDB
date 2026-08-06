<?php

class Simulador
{
    public ?string $Valor;
    public ?string $Data_Inicial;
    public ?string $Data_Final;
    public ?string $CDI;
    public ?string $CDI_ATUAL;
    public ?string $Taxa_Prefixada;
    public ?string $Tipo_CDB;

    private int $scale = 15;

    public function __construct(
        $valor,
        $data_inicial,
        $data_final,
        $cdi,
        $CDI_ATUAL,
        $Taxa_Prefixada,
        $Tipo_CDB
    ) {

        $this->Valor = (string)$valor;

        $this->Data_Inicial = (string)$data_inicial;

        $this->Data_Final = (string)$data_final;

        $this->CDI = (string)$cdi;

        $this->CDI_ATUAL = (string)$CDI_ATUAL;

        $this->Taxa_Prefixada = (string)$Taxa_Prefixada ?? null;

        $this->Tipo_CDB = (string)$Tipo_CDB ?? null;
    }

    public function calcular(): array
    {

        /**
         * dias totais
         */
        $dias = $this->calcularDias();


        if ($this->Tipo_CDB === 'prefixado') {

            $this->CDI = null;
            $this->CDI_ATUAL = null;

        } else {

            $this->Taxa_Prefixada = null;
        }

    
        if ($this->Taxa_Prefixada !== null && $this->Taxa_Prefixada !== '' && $this->Taxa_Prefixada !== '0') {


            /**
             * Ex: 15
             */
            $rentabilidade =
            $this->Taxa_Prefixada;

        } else {

            /**
             * percentual CDI
             *
             * 110 -> 1.10
             */
            $percentual = bcdiv(
                $this->CDI,
                "100",
                $this->scale
            );

            /**
             * 14.40 * 1.10
             */
            $rentabilidade = bcmul(
                $this->CDI_ATUAL,
                $percentual,
                $this->scale
            );
        }

        /**
         * decimal anual
         *
         * 14.46 -> 0.1446
         */
        $rentabilidade_decimal = bcdiv(
            $rentabilidade,
            "100",
            $this->scale
        );

        /**
         * taxa diária
         *
         * usando pow() apenas aqui
         * porque BCMath não trabalha
         * bem com expoente fracionado
         */
        $taxa_diaria = pow(
            (
                1 +
                (float)$rentabilidade_decimal
            ),
            (1 / 252)
        ) - 1;

        /**
         * BCMath precisa string
         */
        $taxa_diaria = (string)$taxa_diaria;

        /**
         * base
         *
         * 1 + taxa diária
         */
        $base = bcadd(
            "1",
            $taxa_diaria,
            $this->scale
        );

        /**
         * evolução do investimento
         */
        $evolucao = [];

        $valor_atual = $this->Valor;

        for ($i = 1; $i <= $dias; $i++) {

            $valor_atual = bcmul(
                $valor_atual,
                $base,
                $this->scale
            );

            /**
             * salva apenas alguns pontos
             */
            if (
                $i === 1 ||
                $i % 30 === 0 ||
                $i === $dias
            ) {

                $evolucao[] = [

                    'dia' => $i,

                    'valor' => round(
                        (float)$valor_atual,
                        2
                    )
                ];
            }
        }

        /**
         * juros compostos
         */
        $potencia = bcpow(
            $base,
            (string)$dias,
            $this->scale
        );

        /**
         * valor bruto final
         */
        $valor_final = bcmul(
            $this->Valor,
            $potencia,
            $this->scale
        );

        /**
         * lucro bruto
         */
        $lucro = bcsub(
            $valor_final,
            $this->Valor,
            $this->scale
        );

        /**
         * imposto
         */
        $ir = $this->getIR($dias);

        $ir_decimal = bcdiv(
            $ir,
            "100",
            $this->scale
        );

        $imposto = bcmul(
            $lucro,
            $ir_decimal,
            $this->scale
        );

        /**
         * valor líquido
         */
        $valor_liquido = bcsub(
            $valor_final,
            $imposto,
            $this->scale
        );

        /**
         * lucro líquido
         */
        $lucro_liquido = bcsub(
            $valor_liquido,
            $this->Valor,
            $this->scale
        );

        return [

            'dias' => $dias,

            'evolucao' => $evolucao,

            'rentabilidade' => number_format(
                (float)$rentabilidade,
                2,
                ',',
                '.'
            ),

            'valor_bruto' => number_format(
                (float)$valor_final,
                2,
                ',',
                '.'
            ),

            'ir' => $ir,

            'valor_liquido' => number_format(
                (float)$valor_liquido,
                2,
                ',',
                '.'
            ),

            'lucro_liquido' => number_format(
                (float)$lucro_liquido,
                2,
                ',',
                '.'
            ),


        ];
    }

    /**
     * calcula dias entre datas
     */
    private function calcularDias(): int
    {
        $inicio = new DateTime(
            $this->Data_Inicial
        );

        $fim = new DateTime(
            $this->Data_Final
        );

        $dias_uteis = 0;

        while ($inicio < $fim) {

            $dia_semana = $inicio->format('N');

            /**
             * 6 = sábado
             * 7 = domingo
             */
            if ($dia_semana < 6) {

                $dias_uteis++;
            }

            $inicio->modify('+1 day');
        }

        return $dias_uteis;
    }

        /**
         * IR regressivo
         */
        private function getIR(int $dias): string
        {

            /**
             * converte para meses aproximados
             */
            $meses = floor($dias / 30);

            if ($meses <= 6) {
                return "22.5";
            }

            if ($meses <= 12) {
                return "20";
            }

            if ($meses <= 24) {
                return "17.5";
            }

            return "15";
        }
}
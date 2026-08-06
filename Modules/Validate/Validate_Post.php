<?php

class Validate_Post
{
    protected array $Erros_Formulario = [];

    public ?float $Valor;
    public ?string $Data_Inicial;
    public ?string $Data_Final;
    public ?float $CDI;
    public ?float $CDI_ATUAL;
    public ?float $Taxa_Prefixada;
    public ?string $Tipo_CDB;

    public function __construct()
    {
        if (isset($_POST["valor"])) {

            $valor = preg_replace(
                    '/[^\d,.]/',
                    '',
                    $_POST['valor']
                );

                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
        
        }else{
            $valor = null;
        }
        
        $this->Valor = filter_var(
            $valor,
            FILTER_VALIDATE_FLOAT
        ) ?? null;
        $this->Data_Inicial = filter_input(INPUT_POST, 'data_inicial') ?? null;
        $this->Data_Final = filter_input(INPUT_POST, 'data_final') ?? null;
        $this->CDI = filter_input(INPUT_POST, 'cdi', FILTER_VALIDATE_FLOAT) ?? null;
        $this->CDI_ATUAL = filter_input(INPUT_POST, 'cdi_atual', FILTER_VALIDATE_FLOAT) ?? null;
        $this->Taxa_Prefixada = filter_input(INPUT_POST, 'taxa_prefixada', FILTER_VALIDATE_FLOAT) ?? null;
        $this->Tipo_CDB = filter_input(INPUT_POST, 'tipo_cdb', FILTER_SANITIZE_SPECIAL_CHARS) ?? null;

    }

    public function Add_Erro(array $n): void
    {
        $this->Erros_Formulario = array_merge_recursive($this->Erros_Formulario, $n);
    }

    public function validate_post()
    {

        if ($this->Valor !== false && $this->Valor !== null) {

            if ( $this->Valor <= 0) {

                $this->Add_Erro(['Valor_Erro' => ["O VALOR DEVE SER UM NÚMERO POSITIVO"]]);
            }
        } else {

            $this->Add_Erro(['Valor_Erro' => ["O VALOR É OBRIGATÓRIO"]]);
        }


        $inicio = DateTime::createFromFormat(
        'Y-m-d',
            $this->Data_Inicial
        );

        $fim = DateTime::createFromFormat(
            'Y-m-d',
            $this->Data_Final
        );

        $errors = DateTime::getLastErrors();

        if (
            !$inicio ||
            !$fim ||
            (
                $errors !== false &&
                (
                    $errors['warning_count'] > 0 ||
                    $errors['error_count'] > 0
                )
            )
        ) {

            $this->Add_Erro(['Data_Erro' => ["AS DATAS DEVEM ESTAR NO FORMATO YYYY-MM-DD E SEREM VÁLIDAS"]]);

        }

        if ($fim <= $inicio) {

            $this->Add_Erro(['Data_Erro' => ["A data final deve ser maior que a inicial."]]);

        }

        if ($this->Tipo_CDB !== null) {

            if ($this->Tipo_CDB === 'posfixado') {

                if ($this->CDI !== "" && $this->CDI !== null) {

                            if ($this->CDI < 80 || $this->CDI > 140) {
                                $this->Add_Erro([
                                    'CDI_Erro' => [
                                        "O CDI DEVE ESTAR ENTRE 80 E 140"
                                    ]
                                ]);
                            }
                        } else {

                            $this->Add_Erro(['CDI_Erro' => ["O CDI É OBRIGATÓRIO"]]);
                        }

                if ($this->CDI_ATUAL !== "" && $this->CDI_ATUAL !== null) {

                    if ($this->CDI_ATUAL <= 0) {
                        $this->Add_Erro([
                            'CDI_ATUAL_Erro' => ["O CDI ATUAL DEVE SER UM NÚMERO POSITIVO"]
                        ]);
                    }
                } else {
                    $this->Add_Erro(['CDI_ATUAL_Erro' => ["O CDI ATUAL É OBRIGATÓRIO"]]);
                }
            
            } 
            
                
                if ($this->Tipo_CDB === 'prefixado') {

                    if ($this->Taxa_Prefixada !== "" && $this->Taxa_Prefixada !== null && $this->Taxa_Prefixada != 0) {

                        //var_dump($this->Taxa_Prefixada);

                        if ($this->Taxa_Prefixada < 0 || $this->Taxa_Prefixada > 100) {
                            $this->Add_Erro([
                                'Taxa_Prefixada_Erro' => ["A TAXA PREFIXADA DEVE ESTAR ENTRE 0 E 100"]
                            ]);
                        }
                    }
                }
       
        }else{
            
            $this->Add_Erro(['Tipo_Erro' => ["O TIPO DE CDB É OBRIGATÓRIO"]]);
        }
        

        // Retorna os erros encontrados
        return $this->Erros_Formulario;

    }

    public function validate_data($data_inicial, $data_final) 
    {

        if ($data_inicial === "" || $data_final === "") {

            $this->Add_Erro(['Data_Erro' => ["AS DATAS SÃO OBRIGATÓRIAS"]]);

            // Retorna os erros encontrados
            return $this->Erros_Formulario;
        }

        $inicio = DateTime::createFromFormat(
        'Y-m-d',
            $data_inicial
        );

        $fim = DateTime::createFromFormat(
            'Y-m-d',
            $data_final
        );

        $errors = DateTime::getLastErrors();

        if (
            !$inicio ||
            !$fim ||
            (
                $errors !== false &&
                (
                    $errors['warning_count'] > 0 ||
                    $errors['error_count'] > 0
                )
            )
        ) {

            $this->Add_Erro(['Data_Erro' => ["AS DATAS DEVEM ESTAR NO FORMATO YYYY-MM-DD E SEREM VÁLIDAS"]]);

            // Retorna os erros encontrados
            return $this->Erros_Formulario;
        }

        if ($fim <= $inicio) {

            $this->Add_Erro(['Data_Erro' => ["A data final deve ser maior que a inicial."]]);

            // Retorna os erros encontrados
            return $this->Erros_Formulario;
        }

        // Retorna os erros encontrados
        return $this->Erros_Formulario;
    }
}
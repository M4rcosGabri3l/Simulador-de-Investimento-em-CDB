<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once "Validate/Validate_Post.php";

    $Validate = new Validate_Post();

    $Erros_Formulario = $Validate->validate_post();


    if (empty($Erros_Formulario)) {

        require_once "Model/Simulador.php";

        $Simulador = new Simulador($Validate->Valor, $Validate->Data_Inicial, $Validate->Data_Final, $Validate->CDI, $Validate->CDI_ATUAL, $Validate->Taxa_Prefixada, $Validate->Tipo_CDB);

        $Resultado_Simulacao = $Simulador->Calcular();

    }

}

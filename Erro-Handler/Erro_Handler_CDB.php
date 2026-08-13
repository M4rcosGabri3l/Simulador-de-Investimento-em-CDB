<?php
date_default_timezone_set("AMERICA/SAO_PAULO");

ini_set('display_errors', '1');
ini_set('dislay_startup_errors', '1');


ini_set('log_errors', '0');
ini_set('error_log', 'C:\xampp\htdocs\CURSO_PHP\CDB_System\Erro-Handler\log-erro.txt');


class Erro_Handler {

    
   protected int $Analise_Erro = 0;

    
    public function __construct() 
      {
       register_shutdown_function([$this,'shutdownFunction']);
 
       set_error_handler([$this,'handler_error']);
        
       set_exception_handler([$this,'Erro_Exceção_Não_Capturada']);
      }
    
     
      public function Erro_Exceção_Não_Capturada($e){

         $Mensagem_Erro = "Exceção não capturada: ".$e->getMessage()." na linha (".$e->getLine().")"." no arquivo (".$e->getFile().")"." na pilha (".$e->getTraceAsString().")"." na exceção (".$e->getCode().")";
     
         error_log($Mensagem_Erro);  
         
         $this->Analise_Erro = 1;
         
         die();
      }
     
     
      public function handler_error($Level, $Menssage, $File = "", $Line = 0) {
    
        throw new \ErrorException($Menssage, 0, $Level, $File, $Line);
    
    }
    
    
    public function shutdownFunction(){

     if ($this->Analise_Erro === 0) {
    
        $Erro_Fatal = error_get_last();
        
        if ($Erro_Fatal !== null) {
         
         $e = new \ErrorException($Erro_Fatal['message'], 0, $Erro_Fatal['type'], $Erro_Fatal['file'], $Erro_Fatal['line']);

        $this->Erro_Exceção_Não_Capturada($e);
    }
   }
  }

} 

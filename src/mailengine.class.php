<?php


  class Mailengine{

    public $engine;

    public function __construct(){
      $this->engine = new PHPMailer();
      $this->engine->IsSMTP();
      $this->engine->CharSet    = 'UTF-8';
      $this->engine->Host       = HST_MAIL;
      $this->engine->Port       = PRT_MAIL;
      $this->engine->Username   = USR_MAIL;
      $this->engine->Password   = PWD_MAIL;
      $this->engine->SMTPSecure = 'tls';
    }

    public function setFrom(string $mail, string $name){
      $this->engine->SetFrom($mail,$name); 
    }

    public function addDestino(string $destino){
      $this->engine->AddAddress($destino);
    }

    public function addCopia(string $destino){
      $this->engine->AddCC($destino);
    }

    public function addCopiaOculta(string $destino){
      $this->engine->AddBCC($destino);
    }

    public function addAsunto(string $asunto){
      $this->engine->Subject  = (empty($asunto)) ? "Contacto desde la web" : $asunto;
    }

    public function bodyHtml(string $body){
      $this->engine->MsgHTML($body);
    }

    public function adjunto(string $rutArchi){
    $this->engine->AddAttachment($rutArchi);
    }

    public function cleanAddrs(){
      $this->engine->ClearAddresses();
    }

    public function sendMail(){
      if($this->engine->Send()) {
        return array('rta'=>'OK','msg'=>'Mensaje enviado correctamente.');
      } else {
        return array('rta'=>'ERROR','msg'=>'Error de envío: '.$this->engine->ErrorInfo);
      }
    }

  }

?>
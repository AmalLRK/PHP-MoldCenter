<?php
require(".\inc\PHPMailer\class.phpmailer.php");
include(".\inc\PHPMailer\class.smtp.php");

class Mail extends PHPMailer
{
  public function __construct()
  {
    parent::__construct();
    $this->IsSMTP(); // C'est un SMTP !

    $this->SMTPAutoTLS = false;
    $this->SMTPAuth = false; // Authentification
    $this->SMTPDebug = 0;
    $this->Host     = "emearelay.ec.goodyear.com"; // Nom de l'Host
    //$this->Host     = "emeaout01.goodyear.com";
    $this->Port     = 25; // Numéro du port
    $this->SetFrom('ServiceIT@goodyear.com'); //ethis "null" qui se trouve dans l'entête du this
    $this->FromName = "Service IT"; // Nom de la personne qui envoi le this 'FROM'
  }

  public function setSubject($subject)
  {
    $this->Subject = $subject;
  }

  public function setBody($body, $isHTML = true)
  {
    $this->IsHTML($isHTML);

    $this->Body = $body;
  }

  public function setBodyTemplate($filePath, $mailVars = array())
  {
    $this->IsHTML(true);
    ob_start();
    include(VIEW_DIR . $filePath);
    $this->Body = ob_get_clean();
  }
}

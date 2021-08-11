<?php
//CTRL+SHIFT+F de NEWMOD sur le dossier inc pour connaitre les fichiers a modifiés
$locale = 'fr_FR.UTF-8';
setlocale(LC_ALL, $locale);
ini_set('error_log', 'C:\inetpub\wwwroot\SIRH\logs\logs_err.log');
ini_set('max_execution_time', 0);
ini_set('max_input_time', -1);
ini_set('memory_limit', '1024M');

session_start();

//if ($_GET['debug'])
{
  ini_set('display_errors', 1);
  ini_set('auto_trace', 1);
  error_reporting(E_ALL);
}

/* /!\  http://php.net/manual/fr/language.oop5.php /!\ */

//MVC CONSTANTE
const INC_DIR = './inc/';
const CONTROLLER_DIR = INC_DIR . 'Controllers/';
const MODEL_DIR = INC_DIR . 'Models/';
const VIEW_DIR = INC_DIR . 'Views/';

//Files CONSTANTE
const JS_DIR = './js/';
const JS_WORKER_DIR = JS_DIR . 'workers/';



function search($chemin, $classSearch)
{
  $it = new RecursiveDirectoryIterator($chemin);
  // var_dump($classSearch);
  foreach (new RecursiveIteratorIterator($it) as $file) {
    if ($file->getPathname() == ($classSearch)) {
      // var_dump($file);
      return ($file);
    }
  }
}



//Methode magique Link => http://php.net/manual/fr/function.autoload.php
function __autoload($className)
{
  if (preg_match('/PHPExcel/', $className)) {
    $subClass = explode('_', $className)[1];
    $className = 'PHPExcel';
  }
  switch ($className) {
    case 'DatabaseFactory':
      include './inc/DatabaseFactory.php';
      break;
    case 'Office365_Client':
      include './libs/office365client/Office365_Client.php';
      break;
    case 'Mail':
      include './inc/Mail.php';
      break;
    case 'PHPExcel':
      include './libs/PHPExcel-1.8.1/Classes/PHPExcel.php';
      include './libs/PHPExcel-1.8.1/Classes/PHPExcel/' . $subClass . '.php';
      break;
    case 'smbclient':
      include './libs/smbclient.php';
      break;

    default:
      preg_match_all("/^([a-zA-Z]*)(Model|Controller|Element)/", $className, $res); // Expression régulière Link => http://php.net/manual/fr/function.preg-match-all.php
      if (
        $className != 'frontController'
        && $className != 'Controller'
        && preg_match('/admin/', $_SERVER['REQUEST_URI'])
        && $res[2][0] == 'Controller'
      ) {

        // Test si admin ou pas
        //if ($_SESSION['admin'] == 1)
        include './inc/' . $res[2][0] . 's/admin/' . $className . '.php';
      } else if (isset($res[2][0]) && file_exists('inc/' . $res[2][0] . 's/' . $className . '.php'))
        include_once './inc/' . $res[2][0] . 's/' . $className . '.php';
      else {
        $tmp = search("./libs", ('./libs\\' . $className . '.php'));
        if ($tmp)
          include_once($tmp);
      }
      break;
  }
}

$front = new frontController();
$front->run();

<?php

class frontController
{
  /**
   * Controller par défaut.
   * @var string nom du fichier par défaut pour le controlleur
   */
  const DEFAULT_CONTROLLER = "indexController";

  /**
   * Méthode a appelé par défaut.
   * @var string nom de la méthode par défaut
   */
  const DEFAULT_ACTION     = "index";

  /**
   * A modifié par le nom de la plateforme/site voulue (exemple CERTIF....) en majuscule c'est mieu.
   * NEW → 08/01/2019
   * @var string Nom de la plateforme
   */
  const PLATEFORME_NAME    = "MOLD";

  /**
   * active/désactive les user.
   * NEW → 14/03/2019
   * @var boolean désactiver/active le système de user
   */
  const DISABLE_LOGIN      = false;

  protected $controller    = self::DEFAULT_CONTROLLER;
  protected $action        = self::DEFAULT_ACTION;
  protected $params        = array();
  protected $basePath      = "";

  protected $admin         = false; //RUSTINE OBSELETE, peut être réutilisé dans une version future

  public function __construct(array $options = array())
  {

    if (empty($options)) {
      $this->parseUri();
    } else {
      if (isset($options["controller"])) {
        $this->setController($options["controller"]);
      }
      if (isset($options["action"])) {
        $this->setAction($options["action"]);
      }
      if (isset($options["params"])) {
        $this->setParams($options["params"]);
      }
    }
  }

  /**
   * Savoir si l'utilisateur a l'accès.
   * @param string Droit voulu
   * @param string Application qui contient les droits
   * @return boolean true si la personne a le droit ou si le système de login est désactivé
   */
  public static function haveRight($rights, $app)
  { return self::userHaveRight($rights, $app, frontController::getSession('role')['mainRule']); }

  /**
   * Savoir si un utilisateur particuliers a accès.
   * @param string Droit voulu
   * @param string Application qui contient les droits
   * @param array Tableau des droits principaux
   * @return boolean true si la personne a le droit ou si le système de login est désactivé
   */
  public static function userHaveRight($rights, $app, $mainRule)
  {
    if (self::DISABLE_LOGIN) {
      return true;
    }
    // Si l'utilisateur est administrateur alors il a toujours les droits
    if (isset($mainRule['admin']))
      return true;
    
    // Si l'application est "None" alors on vérifie que l'utilisateur n'est pas modérateur
    if ($app == 'None') {
      foreach ($mainRule as $app => $rule) {
        if (isset($rule['moderator']))
          return true;
      }
    }

    // Si l'utilisateur dispose d'un tableau de droit pour l'application voulu
    if (isset($mainRule[$app])) {
      // Si l'utilisateur est modérateur pour cette application alors il a les droits
      if (isset($mainRule[$app]['moderator']))
        return true;

      // Si l'utilisateur dispose du droit voulu dans son tableau alors il a le droit
      if (isset($mainRule[$app][$rights]))
        return true;
      else
        return false;
    } else {
      return false;
    }
  }

  public static function logged()
  {
    if (self::getSession('userid')) {
      return true;
    }
    return false;
  }

  /**
   * Donne l'adresse de l'acceuil en fonction des droits.
   * @return string chemin d'accès /controller/method de l'acceuil
   */
  public static function getAcceuil()
  {
    //exemple 1 : if(self::haveRight('EMPLOYE')) return '/employe/acceuil/index/'; → Ici /employe/ est un dossier et non un controleur voir → (TO_UPDATE)
    //exemple 2 : if(self::haveRight('ADMIN')) return '/admin/acceuil/index/';
    return '/';
  }

  /**
   * Parse l'url pour redirigé vers la bonne méthode
   */
  protected function parseUri()
  {
    $path = ltrim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
    $path = preg_replace('/[^a-zA-Z0-9]\//', "", $path);

    if ($this->basePath != '') {
      if (strpos($path, $this->basePath) === 0)
        $path = substr($path, strlen($this->basePath));
    }


    @list($controller, $action, $params) = explode("/", $path, 3);

    if (!empty($controller)) {
      if ($controller != 'admin')
        $this->setController($controller);
      else {
        $this->admin = true;
        @list($controller, $action, $params) = explode("/", str_replace('admin/', '', $path), 3);

        if (!empty($controller))
          $this->setController($controller);
      }
    }
    if (!empty($action)) {
      $this->setAction($action);
    }
    if (!empty($params)) {
      $this->setParams(explode("/", $params));
    }
  }

  /**
   * Vérifie si le controleur existe et le charge.
   * @param frontController l'instance actuel de frontController (après avoir mis le nom du fichier contentant le controller dans $this->controller)
   */
  public function setController($controller)
  {
    $controller = strtolower($controller) . "Controller";
    if (!class_exists($controller)) {
      throw new InvalidArgumentException(
        "The action controller '$controller' has not been defined."
      );
    }
    $this->controller = $controller;
    return $this;
  }

  /**
   * Vérifie si le controller possède la méthode $action.
   * @param string $action nom de la méthode
   * @return frontController l'instance actuel de frontController (après avoir mis le nom de la méthode dans $this->action)
   */
  public function setAction($action)
  {
    $reflector = new ReflectionClass($this->controller);
    if (!$reflector->hasMethod($action)) {
      throw new InvalidArgumentException(
        "The controller action '$action' has been not defined."
      );
    }
    $this->action = $action;
    return $this;
  }

  public function setParams(array $params)
  {
    $this->params = $params;
    return $this;
  }

  private function checkBrowser()
  {
    preg_match_all('/(MSIE|(?!Gecko.+)Firefox|(?!AppleWebKit.+Chrome.+)Safari|Edge|(?!AppleWebKit.+)Chrome|AppleWebKit(?!.+Edge|.+Chrome|.+Safari)|Gecko(?!.+Firefox))(?: |\/)([\d\.apre]+)/',  $_SERVER['HTTP_USER_AGENT'], $matches);

    if (
      !(isset($matches[1][0]) && $matches[1][0] == 'Chrome') ||
      isset($matches[1][2]) && $matches[1][2] == 'Edge'
    ) {
      $this->setController('index');
      $this->setAction('badBrowser');
      call_user_func_array(array(new $this->controller, $this->action), $this->params);
      exit;
    }
  }

  /**
   * Lance la bonne page en fonction de l'url
   * @return [type] [description]
   */
  public function run()
  {
    $this->checkBrowser();

    //Gestion des droits
    if (self::DISABLE_LOGIN || $this->controller == 'authController' || self::getSession('userid')) {
      call_user_func_array(array(new $this->controller, $this->action), $this->params);
    } else {
      self::auth();
    }
  }

  public static function auth()
  {
    //Redirige vers la page d'authentification
    if ($_SERVER['REQUEST_URI'] !== '/auth/') {
      header('Location: ' . $_SERVER['HTTP_REFERER'] . '/auth/');
    }
  }

  /**
   * Permet de délier les variables de session, pour pouvoir être logé sur plusieurs plateforme.
   * UPDATE → 08/01/2019
   * @param string $param nom de la variable de session
   * @param string $value valeur du paramètre de session
   */
  public static function setSession($param, $value)
  {
    $_SESSION[self::PLATEFORME_NAME . '_' . $param] = $value;
  }

  /**
   * Permet d'avoir la valeur d'une variable de session.
   * @param string $param nom de la variable de session
   * @return string le contenu de la variable de session, false si non existant
   */
  public static function getSession($param) //UPDATE → 08/01/2018
  {
    if (isset($_SESSION[self::PLATEFORME_NAME . '_' . $param])) {
      return $_SESSION[self::PLATEFORME_NAME . '_' . $param];
    }
    return false;
  }

  public static function isAdmin()
  {
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)
      return true;

    return false;
  }

  /**
   * Supprime toutes les sessions (TOUTES les plateformes actuellement connecté seront détruite).
   * UPDATE → 10/01/2019
   * @redirect sur la page d'acceuil
   */
  public static function destroyAllSession()
  {
    session_destroy();
    self::redirect();
  }

  /**
   * permet de unset une variable session, ou de supprimer toute les variable de session lié a cette plateforme.
   * NEW → 08/01/2019
   * @param string $key nom de la variable de session a supprimer, null = toute les variable de la plateforme actuel
   * @return boolean si suppression d'une seul variable de session, true
   * @redirect sur la page de login si suppression de session entière ou de plateforme
   */
  public static function deleteSession($key = null)
  {
    if (!is_null($key)) {
      unset($_SESSION[self::PLATEFORME_NAME . '_' . $key]);
      return true;
    }

    //évite de supprimé la session entière (si connecter sur 2 plateforme a la fois)
    foreach ($_SESSION as $key => $data) {
      if (preg_match('/^' . self::PLATEFORME_NAME . '_/', $key)) {
        unset($_SESSION[$key]);
      }
    }
    if (empty($_SESSION)) {
      session_destroy();
    }

    //redirection sur la page de login
    header('Location: ' . $_SERVER['HTTP_ORIGIN'] . '/auth/');
  }

  /**
   * Redirige vers l'acceuil ou l'url passer en paramètre.
   * @param string $url un chemin du type : '/contoller/method/param1'
   * @redirect vers l'url demandé ou l'acceuil si $url == null
   */
  public static function redirect($url = null)
  {
    if ($url == null) {
      header('Location: ' . frontController::getAcceuil());
    } else {
      header('Location: ' . $url);
    }
  }

  public static function disconnect()
  {
    self::destroyAllSession();
    self::auth();
  }
}

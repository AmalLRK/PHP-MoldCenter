<?php

class Controller
{
  protected $params = array();
  protected $filtersParams = array();
  protected $view;
  protected $filter;
  protected $header = 'header.phtml';
  protected $footer = 'footer.phtml';
  protected $machine;

  public function __construct()
  { }

  protected function changeHeader($str)
  {
    if ($str !== null)
      $this->header = $str . '.phtml';
    else
      $this->header = null;
  }

  protected function changeFooter($str)
  {
    if ($str !== null)
      $this->footer = $str . '.phtml';
    else
      $this->footer = null;
  }

  public function setParam($name, $value)
  {
    $this->params[$name] = $value;
  }

  public function getParam($name)
  {
    return (isset($this->params[$name]) ? $this->params[$name] : false);
  }

  public function setFilterParam($name, $value)
  {
    $this->filtersParams[$name] = $value;
  }

  public function getFilterParam($name)
  {
    return (isset($this->filtersParams[$name]) ? $this->filtersParams[$name] : false);
  }

  public function setView($view)
  {
    $this->view = $view . '.phtml';
  }

  public function setFilterView($filter)
  {
    $this->filter = $filter . '.phtml';
  }

  public function showView()
  {
    ob_start();
    if ($this->header !== null)
      include(VIEW_DIR . $this->header);

    if ($this->view)
      include(VIEW_DIR . $this->view);


    include(VIEW_DIR . $this->footer);

    echo ob_get_clean();
  }

  public function showFilter()
  {
    if ($this->filter) {
      ob_start();
      include('./inc/Views/Filters/' . $this->filter);
      return ob_get_clean();
    }
    return false;
  }

  protected function getPost($name)
  {
    if (isset($_POST[$name]) && !empty($_POST[$name])) {
      $this->setParam($name, $_POST[$name]);
      return $this->getParam($name);
    }

    return false;
  }

  protected function getGet($name)
  {
    if (isset($_GET[$name]) && !empty($_GET[$name])) {
      $this->setParam($name, $_GET[$name]);
      return $this->getParam($name);
    }

    return false;
  }

  protected function getFilter($name)
  {
    if (isset($_POST[$name]) && !empty($_POST[$name])) {
      $this->setFilterParam($name, $_POST[$name]);
      return true;
    }

    return false;
  }

  public function getFilters($arrNames)
  {
    foreach ($arrNames as $name) {
      $this->getFilter($name);
    }
  }

  public function getParams($arrNames)
  {
    foreach ($arrNames as $name) {
      $this->getPost($name);
    }
  }

  public function getMachine()
  {
    return $this->machine;
  }



  /**
   * Fonction qui permet de récupérer le fichier de traduction
   * pour une application
   * Paramètre :
   *  @param string : Nom de l'application (Index, Mold, ...)
   * @return array : Tableau des traductions
   */
  protected static function getTranslate($appName)
  {
    $language = frontController::getSession('userTranslateChoice');

    if (!$language) {
      $configApp = parse_ini_file('Translate/config.ini');
      frontController::setSession('userTranslateChoice', $configApp['defaultTranslate']);
    }

    $language = frontController::getSession('userTranslateChoice');

    return parse_ini_file("Translate/$language/$appName-$language.ini");
  }

  /**
   * Fonction qui permet de changer la langue de l'application
   * Paramètre :
   *  @param string : Diminutif du langage choisit (fr ou en)
   * @return boolean : False si langage choisit incorrect et True si changement réussi
   */
  protected static function setTranslate($language)
  {
    $supportedLanguage = array();
    $supportedLanguage['fr'] = true;
    $supportedLanguage['en'] = true;

    if (!isset($supportedLanguage[$language]))
      return false;
    else {
      frontController::setSession('userTranslateChoice', $language);
      return true;
    }
  }

  /**
   * Fonction qui permet de récupérer la traduction d'un éléments et d'avoir
   * une donnée à afficher à chaque fois (permet d'annuler l'apparition d'erreur !)
   * Paramètres :
   *  @param array : Tableau qui contient les traductions
   *  @param string : Element recherché dans le tableau de traduction
   * @return string : Traduction ou erreur à afficher
   */
  protected static function getVerifyTranslate($translateData, $translation)
  {
    if (isset($translateData[$translation]))
      return $translateData[$translation];
    else
      return '! No Translation Data ! For ' . $translation;
  }

  /**
   * Fonction qui permet de changer l'application sur laquelle on est actuellement
   * Paramètre :
   *  @param string : Nom de l'application
   */
  protected static function setApp($appBaseName)
  {
    frontController::setSession('currentApp', $appBaseName);
  }
}

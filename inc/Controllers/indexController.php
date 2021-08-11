<?php

class indexController extends Controller
{
  /**
   * Fonction qui permet d'afficher la liste des applications disponibles
   */
  public function index()
  {
    $indexModel = new indexModel();

    // Récupération de la liste des applications disponibles
    $pageList = $indexModel->getIndexList();

    $translate = $this->getTranslate('Index');
    $translateList = $this->getTranslate('Index_List');

    $this->setParam('pageList', $pageList);

    $this->setParam('translate', $translate);
    $this->setParam('translateList', $translateList);

    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('index');
    $this->setApp('Index');
    $this->showView();
  }

  /**
   * Fonction qui permet d'indiquer que le navigateur n'est pas le bon
   */
  public function badBrowser()
  {
    $translate = $this->getTranslate('Index');

    $this->setParam('translate', $translate);

    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('badBrowser');
    $this->setApp('Index');
    $this->showView();
  }

  /**
   * Fonction qui permet de changer la langue de l'application
   * Paramètre :
   *  @param string : Nouvelle langue
   */
  public function language($language)
  {
    $this->setTranslate($language);
    header('Location: /');
  }
}

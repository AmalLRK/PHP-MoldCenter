<?php

class authController extends Controller
{
  /**
   * Fonction qui permet d'afficher la page de connection
   */
  public function index()
  {
    // Redirection vers la page d'accueil si on est connecté
    if (frontController::logged()) {
      frontController::redirect();
    } else {
      $translate = $this->getTranslate('Auth');

      $this->setParam('translate', $translate);

      $this->setParam('titlePage', $translate['pageTitle']);
      $this->setView('auth');
      $this->showView();
    }
  }

  /**
   * Fonction qui permet de se connecter
   */
  public function login()
  {
    // On récupère en post le login et le pwd
    $this->getPost('pwd');
    $this->getPost('login');

    $userModel = new userModel($this->getParam('login'), $this->getParam('pwd'));

    var_dump($userModel);

    // Si les informations de connexions sont OK, redirection vers l'index.
    if ($userModel->checkUser()) {
      header('Location: ' . $_SERVER['HTTP_ORIGIN'] . frontController::getAcceuil());
    }
    // Sinon, on redirige à nouveau vers la page de login
    else {
      header('Location: ' . $_SERVER['HTTP_ORIGIN'] . '/auth/?errorMsg=Login%20ou%20mot%20de%20passe%20incorrect');
    }
  }

  /**
   * Fonction qui permet de déconnecter un utilisateur
   * @return Redirection vers la page de login
   */
  public function logout()
  {
    frontController::disconnect();
    header('Location: ' . $_SERVER['HTTP_ORIGIN'] . '/auth/');
  }

  /**
   * Fonction qui permet de gérer son compte utilisateur et
   * ses préférences
   * Paramètre :
   *  @param int : Identifiant de l'utilisateur
   * @return Redirection vers la page de gestion du compte personnel
   */
  public function manage($id)
  {
    $translate = $this->getTranslate('Manage');
    $this->setParam('translate', $translate);

    $userModel = new userModel();

    // Si l'utilisateur est administrateur alors il peut voir les paramètres qu'il veut
    if (frontController::haveRight('moderator', 'None') and !frontController::userHaveRight('admin', 'ALL', $userModel->getUser($id)['MAIN_RULE']))
      $user = $userModel->getUser($id);
    else if (frontController::haveRight('admin', 'ALL'))
      $user = $userModel->getUser($id);
    else
      $user = $userModel->getUser(frontController::getSession('userid'));


    $this->setParam('userID', $user['USERID']);
    $this->setParam('nompre', $user['NOMPRE']);
    $this->setParam('dataRule', $user['DATA_RULE']);
    $this->setParam('mainRule', $user['MAIN_RULE']);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();
    $dataList = $dataModel->getAllDataList();
    $this->setParam('dataList', $dataList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    // Récupération de la liste des applications disponibles
    $indexModel = new indexModel();
    $appList = $indexModel->getIndexList();
    $this->setParam('appList', $appList);

    // traduction des données sur les applications
    $translateApp = $this->getTranslate('Index_List');
    $this->setParam('translateApp', $translateApp);


    // Récupération des informations sur les droits principaux
    $mainRuleModel = new mainRuleModel();
    $mainRuleList = $mainRuleModel->getMainRuleList();
    $this->setParam('mainRuleList', $mainRuleList);

    // traduction des données sur les droits principaux
    $translateMainRule = $this->getTranslate('Main_Rule_List');
    $this->setParam('translateMainRule', $translateMainRule);


    // Récupération des informations sur les status pour les moules
    $moldModel = new moldModel();
    $moldStatusList = $moldModel->getStatusList();
    $this->setParam('moldStatusList', $moldStatusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    // Récupération des statuts des codes
    $codeModel = new codeModel();
    $codeStatusList = $codeModel->getStatusListDic();
    $this->setParam('codeStatusList', $codeStatusList);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('manage');
    $this->setApp('Manage');
    $this->showView();
  }

  /**
   * Fonction qui permet de changer les préférences utilisateurs
   * Paramètre :
   *  @param POST : Données en POST
   * @return database : Modification des préférences utilisateurs
   */
  public function changeSettings()
  {
    // Récupération des droits actuel de l'utilisateur
    $userModel = new userModel();
    $user = $userModel->getUser($_POST['userid']);

    // Récupération de la liste des applications
    $indexModel = new indexModel();
    $appList = $indexModel->getIndexList();


    // Construction du tableau des droits principaux
    $mainRule = array();

    // Implémentation du système d'administrateur
    if (isset($_POST['mainRule']['admin']))
      $mainRule['admin'] = true;
    else {
      // Ajout du droit modérateur si il existe pour chaque application et des autres droits sinon
      foreach ($_POST['mainRule'] as $app => $rule) {
        if (isset($_POST['mainRule'][$app]['moderator']))
          $mainRule[$app]['moderator'] = true;
        else {
          // Ajout des autres droits
          foreach ($rule as $ruleName => $value)
            $mainRule[$app][$ruleName] = true;
        }
      }
    }

    // Si aucun droit, on récupère ceux de base
    if (empty($mainRule))
      $mainRule = $user['MAIN_RULE'];


    // Construction du tableau des droits sur les données
    $dataRule = array();

    // Ajout des données qui sont autorisées ainsi que des préférences utilisateurs
    foreach ($_POST['dataRuleActivated'] as $app => $rule) {
      $dataRule[$app] = array();

      foreach ($rule as $dataBaseName => $value) {
        $dataRule[$app][$dataBaseName] = array();

        if (isset($_POST['dataRuleEdit'][$app][$dataBaseName]))
          $dataRule[$app][$dataBaseName]['edit'] = true;

        if (isset($_POST['dataRuleSearch'][$app][$dataBaseName]))
          $dataRule[$app][$dataBaseName]['search'] = true;

        if (isset($_POST['dataRulePrimary'][$app][$dataBaseName])) {
          if ($_POST['dataRulePrimary'][$app][$dataBaseName] == 1)
            $dataRule[$app][$dataBaseName]['primary'] = true;
        }
      }
    }


    // Ajout des filtres sur les données
    foreach ($_POST['filterActivated'] as $app => $filter) {
      foreach ($filter['FILTER'] as $filterBaseName => $value) {
        $dataRule[$app]['FILTER'][$filterBaseName] = true;

        if (isset($_POST['filterDefault'][$app]['FILTER'][$filterBaseName]))
          $dataRule[$app]['FILTER'][$filterBaseName] = array('default' => true);
      }
    }
    

    // Vérification qu'au moins un filtre est présent pour l'utilisateur sinon ajout d'un filtre par défaut
    if (!isset($dataRule['Mold_App']['FILTER'])) {
      $dataRule['Mold_App']['FILTER']['Operationnel'] = array('default' => true);
    } else {
      // Vérification qu'au moins un filtre est actif par défaut
      $defaultExist = false;
      foreach ($dataRule['Mold_App']['FILTER'] as $filter => $ok) {
        if (isset($dataRule['Mold_App']['FILTER'][$filter]['default']))
          $defaultExist = true;
      }

      // Mise à défaut du premier filtre
      if (!$defaultExist) {
        $key = array_keys($dataRule['Mold_App']['FILTER'])[0];
        $dataRule['Mold_App']['FILTER'][$key] = array('default' => true);
      }
    }


    // Edition de l'utilisateur pour ses préférences et sa configuration
    /*
     * Pour le mot de passe de l'utilisateur, on vérifie que l'on ne l'a pas modifié
     * Si c'est le cas, alors on change la valeur correspondante
     * Sinon on conserve les valeurs par défaut
     */
    $userModel->editUser(
      $user['USERID'],
      $_POST['nom'],
      $_POST['prenom'],
      $_POST['password'] == '' ? $user['PASSWORD'] : $_POST['password'],
      $mainRule,
      $dataRule,
      $_POST['password'] == '' ? true : false   // Si le mot de passe est vide alors il est déjà hasher (on utilise le mot de passe enregistré)
    );


    // Changement des paramètres utilisateur sauf si l'administrateur ou le modérateur les a modifiés
    if ($user['USERID'] == frontController::getSession('userid')) {
      // Si le mot de passe à été changé il faut déconnecter l'utilisateur sinon on change juste les droits et valeurs
      if ($_POST['password'] == '') {
        // Conception du tableau des rôles
        $rule = frontController::getSession('role');

        // Ajout des accès disponibles
        $rule['mainRule'] = $mainRule;
        // Ajout des droits sur les données principales et secondaires
        $rule['dataRule'] = $dataRule;

        // Modification des droits
        frontController::setSession('role', $rule);
        // Modification du prénom si besoin
        frontController::setSession('nomPrenom', $_POST['nom'] . ' ' . $_POST['prenom']);

        $header = 'Location: /';
      } else {
        $header = 'Location: /auth/logout';
      }
    } else {
      $header = 'Location: /';
    }

    header($header);
  }

  /**
   * Fonction qui permet de gérer tout les utilisateurs
   * de l'application et leurs droits
   * @return Redirection vers la page de gestion des utilisateur
   */
  public function system()
  {
    // Si l'utilisateur est administrateur alors il peut voir les paramètres qu'il veut
    $admin = frontController::haveRight('moderator', 'None');

    if ($admin) {
      $translate = $this->getTranslate('System');
      $this->setParam('translate', $translate);

      // Récupération de la liste des utilisateurs et de l'utilisateur actuel
      $userModel = new userModel();

      if (frontController::haveRight('admin', 'ALL'))
        $userList = $userModel->getUserList();
      else
        $userList = $userModel->getUserListForModerator(frontController::getSession('userid'));

      $this->setParam('userList', $userList);


      // Récupération des roles disponibles
      $roleList = json_decode(file_get_contents('./bdd/DEFAULT_USER_RULE.json'), true);
      $this->setParam('roleList', $roleList);

      $translateRoleList = $this->getTranslate('Role_List');
      $this->setParam('translateRoleList', $translateRoleList);


      $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
      $this->setView('system');
      $this->setApp('System');
      $this->showView();
    } else {
      header('Location: /');
    }
  }

  /**
   * Fonction qui permet d'ajouter un utilisateur
   * @return add : Ajout d'un utilisateur
   */
  public function systemAdd()
  {
    $userModel = new userModel();

    // Récupération des droits par défauts
    $tmpRule = json_decode(file_get_contents('./bdd/DEFAULT_USER_RULE.json'), true);
    // Création du tableau des droits principaux par défaut
    $mainRule = $tmpRule[$_POST['role']]['mainRule'];
    // Création du tableau des droits sur les données
    $dataRule = $tmpRule[$_POST['role']]['dataRule'];

    // Ajout de l'utilisateur
    $userModel->addUser(
      $_POST['userid'],
      $_POST['nom'],
      $_POST['prenom'],
      $_POST['password'],
      $mainRule,
      $dataRule
    );

    // Redirection vers la page de gestion utilisateur
    $this->system();
  }

  /**
   * Fonction qui permet de supprimer un utilisateur
   * @return remove : Suppression d'un utilisateur
   */
  public function systemRemove($userList = '')
  {
    if (frontController::haveRight('admin', 'ALL') and $userList != '') {
      $userModel = new userModel();
      // Suppresion du ou des utilisateurs
      $userModel->removeUser($userList);

      // Redirection vers la page de gestion utilisateur
      $this->system();
    } else
      header('Location: /');
  }

  /**
   * Fonction qui permet de générer un hash pour changer ou tester
   * une mot de passe
   * Paramètre :
   *  @param string : Texte à hasher
   * @return string : Texte hasher
   */
  public function hashSystem($text)
  {
    $userModel = new userModel();

    var_dump($userModel->hash($text));
  }
}

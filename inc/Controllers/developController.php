<?php

class developController extends Controller
{
  /**
   * Activation ou non du système d'aide au développement
   */
  private static $DEVELOP_SYSTEM = true;

  /**
   * Fonction qui permet d'afficher la liste des outils pour le développeur
   */
  public function index()
  {
    if (self::$DEVELOP_SYSTEM and frontController::haveRight('admin', 'ALL')) {
      $linkList = array(
        'View Table' => array(
          'User' => 'develop/displayTable/MOLD_USERS',

          'Status' => 'develop/displayTable/MOLD_STATUS',
          'Status Type' => 'develop/displayTable/MOLD_STATUS_TYPE',
          'Location Type' => 'develop/displayTable/MOLD_LOCATION_TYPE',

          'Data List' => 'develop/displayTable/MOLD_DATA_LIST',
          'Data Category Type' => 'develop/displayTable/MOLD_DATA_CATEGORY_TYPE',

          'History' => 'develop/displayTable/MOLD_HISTORY',
          'History Type' => 'develop/displayTable/MOLD_HISTORY_TYPE',

          'Mold Tech' => 'develop/displayTable/MOLD_TECH',
          'Mold Exemption' => 'develop/displayTable/MOLD_EXEMPTION',
          'Mold Metrology' => 'develop/displayTable/MOLD_METROLOGY',

          'Code Tech' => 'develop/displayTable/CODE_TECH',

          'Mold List' => 'develop/displayTable/EXT_MOLDEQUIPMENTSLIST',
          'Mold Spec' => 'develop/displayTable/EXT_MOLDEQUIPMENTSPEC'
        ),
        'Data Tools' => array(
          'Add Data' => 'develop/addData',
          'Add Data Category' => 'develop/addDataCategory'
        )
      );
      $this->setParam('linkList', $linkList);

      $this->setParam('titlePage', 'Develop');
      $this->setView('developIndex');
      $this->setApp('Develop');
      $this->showView();
    }
  }

  /**
   * Fonction qui permet d'afficher une table de la base de donnée
   */
  public function displayTable($tableName)
  {
    if (self::$DEVELOP_SYSTEM and frontController::haveRight('admin', 'ALL')) {
      $developModel = new developModel();

      $table = $developModel->getTable($tableName);
      $this->setParam('table', $table);
      $this->setParam('tableName', $tableName);
  
      $this->setParam('titlePage', 'Develop Table ' . $tableName);
      $this->setView('developTable');
      $this->setApp('Develop');
      $this->showView();
    }
  }

  /**
   * Fonction qui permet d'afficher le formulaire d'ajout d'une donnée.
   * Cette fonction peut également ajouter une donnée avec les informations récupéré en POST
   */
  public function addData()
  {
    if (self::$DEVELOP_SYSTEM and frontController::haveRight('admin', 'ALL')) {
      if (!empty($_POST)) {
        if ($_POST['appName'] != '' and $_POST['baseName'] != '' and $_POST['name'] != '' and $_POST['description'] != '' and $_POST['categoryName'] != '') {
          $dataModel = new dataModel();
          $dataModel->addData($_POST['appName'], $_POST['categoryName'], $_POST['baseName'], $_POST['name'], $_POST['description']);
        }

        header('Location: /develop');
      } else {
        $dataModel = new dataModel();
        $dataCategoryList = $dataModel->getDataCategoryList();
        $this->setParam('dataCategoryList', $dataCategoryList);

        $this->setParam('titlePage', 'Add Data');
        $this->setView('developAddData');
        $this->setApp('Develop');
        $this->showView();
      }
    }
  }

  /**
   * Fonction qui permet d'afficher le formulaire d'ajout d'une catégorie d'une donnée
   * Cette fonction peut également ajouter une catégorie d'une donnée avec les informations récupéré en POST
   */
  public function addDataCategory()
  {
    if (self::$DEVELOP_SYSTEM and frontController::haveRight('admin', 'ALL')) {
      if (!empty($_POST)) {
        if ($_POST['categoryName'] != '' and $_POST['categoryColor'] != '') {
          $dataModel = new dataModel();
          $dataModel->addDataCategory($_POST['categoryName'], explode('#', $_POST['categoryColor'])[1]);
        }

        header('Location: /develop');
      } else {
        $this->setParam('titlePage', 'Add Data Category');
        $this->setView('developAddDataCategory');
        $this->setApp('Develop');
        $this->showView();
      }
    }
  }

  /**
   * Fonction qui permet de savoir si le système d'aide au développement est actif ou non
   */
  public static function isActivated()
  {
    return self::$DEVELOP_SYSTEM;
  }
}

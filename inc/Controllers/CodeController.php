<?php

/**
 * Classe qui contient le contrôleur pour les codes
 * - Affichage de l'index : Redirection vers la liste des codes
 * - Affichage de la liste des codes
 * - Affichage de la vue avancée d'un code
 * - Affichage du formulaire pour ajouter un code
 * - Ajout d'un code
 * - Affichage du formulaire pour éditer un code
 * - Edition d'un code
 * - Suppression d'un code
 * - Exportation de la liste des code
 * - Affichage de l'analyse des codes
 */
class codeController extends Controller
{
  /**
   * Fonction qui permet d'afficher l'index
   */
  public function index()
  {
    $this->view();
  }

  /**
   * Fonction qui permet d'afficher la liste des codes et de gérer la page de base
   */
  public function view()
  {
    $translate = $this->getTranslate('Code');
    $this->setParam('translate', $translate);


    // Système de gestion du formulaire de tri
    $sort = array();

    // Récupération de la liste des statuts pour le système de trie
    $statusListUser = frontController::getSession('role')['dataRule']['Code_App']['FILTER'];

    if (!empty($_GET)) {
      // Récupération des status voulu après un filtre
      foreach ($statusListUser as $statusBaseName => $default) {
        $sort[$statusBaseName] = ($this->getGet('status-' . $statusBaseName)) ? array('default' => true) : true;
      }
    } else {
      // Valeurs par défaut du filtre
      $sort = $statusListUser;
    }

    // Parcours des filtres pour vérifier qu'au moins un filtre est actif
    $empty = true;
    foreach ($sort as $sortBaseName => $default) {
      if (isset($sort[$sortBaseName]['default']))
        $empty = false;
    }

    // Si aucun filtre actif alors on utilise ceux par défaut
    if ($empty)
      $sort = $statusListUser;

    $this->setParam('sort', $sort);


    // Récupération de la liste des codes selon les filtres sinon valeur par défaut
    $codeModel = new codeModel();
    $codeList = $codeModel->getCodeListByStatusList($sort);
    $this->setParam('codeList', $codeList);

    $statusList = $codeModel->getStatusListDic();
    $this->setParam('statusList', $statusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    // Ajout des accès pour la visualisation des données
    $this->setParam('dataRule', frontController::getSession('role')['dataRule']['Code_App']);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Code_App');
    $this->setParam('dataList', $dataList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('code');
    $this->setApp('Code_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Code_App'))
      $this->showView();
    else
      header('Location: /');
  }

  /**
   * Fonction qui permet d'afficher les détails d'un code
   * Paramètre :
   *  @param int : Identifiant unique du code
   * @return view : Affichage de la vue
   */
  public function advancedView($id)
  {
    $translate = $this->getTranslate('CodeAdvancedView');
    $this->setParam('translate', $translate);


    $codeModel = new codeModel();

    $code = $codeModel->getCode($id);
    $this->setParam('code', $code);


    $statusList = $codeModel->getStatusListDic();
    $this->setParam('statusList', $statusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    // Ajout des accès pour la visualisation des données
    $this->setParam('dataRule', frontController::getSession('role')['dataRule']['Code_App']);

    
    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Code_App');
    $this->setParam('dataList', $dataList);

    $dataCategoryList = $dataModel->getDataCategoryListUser('Code_App');
    $this->setParam('dataCategoryList', $dataCategoryList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle') . ' ' . $id);
    $this->setView('codeVision');
    $this->setApp('Code_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Code_App'))
      $this->showView();
    else
      header('Location: /');
  }



  /**
   * Fonction qui permet d'afficher le formulaire d'ajout pour un code
   * @return view : Formulaire d'ajout d'un code
   */
  public function addView()
  {
    $translate = $this->getTranslate('CodeAddView');
    $this->setParam('translate', $translate);

    $codeModel = new codeModel();

    $statusList = $codeModel->getStatusList();
    $this->setParam('statusList', $statusList);

    $moldRefSpecs = $codeModel->getRefSpecsPossible('R1');
    $this->setParam('refSpecs', $moldRefSpecs);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Code_App');
    $this->setParam('dataList', $dataList);

    $dataCategoryList = $dataModel->getDataCategoryListUser('Code_App');
    $this->setParam('dataCategoryList', $dataCategoryList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('codeAdd');
    $this->setApp('Code_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('add', 'Code_App'))
      $this->showView();
    else
      header('Location: /code');
  }



  /**
   * Fonction qui permet d'ajouter un code
   * Paramètres :
   *  @param POST : Données en POST sur le code
   * @return insert : Ajout d'un code
   */
  public function add()
  {
    // Vérification des droits utilisateurs
    if (frontController::haveRight('add', 'Code_App')) {
      // Récupération des informations sur les données
      $dataModel = new dataModel();

      $dataList = $dataModel->getDataList('Code_App');


      // Construction du code avec les données
      $code = array();
      foreach ($dataList as $dataBaseName => $data)
        $code[$dataBaseName] = $_POST[$dataBaseName];


      // Ajout du code
      $codeModel = new codeModel();
      $codeModel->addCode($code);


      // Récupération des données de l'ancien code
      $oldCode = $codeModel->getCode($code['CODE_PROD_1']);

      foreach ($oldCode as $key => $data)
        $oldCode[$key] = '';


      // Ajout de l'historique pour ce code
      $historyModel = new historyModel();
      $historyModel->add('Code_App', $oldCode, $code, 'insert', $code['CODE_PROD_1']);
    }

    header('Location: /code');
  }

  /**
   * Fonction qui permet d'afficher le formulaire de modification pour le code
   * Paramètre :
   *  @param string : Identifiant unique du code à modifier
   * @return view : Forumaile de modification du code
   */
  public function editView($id)
  {
    $translate = $this->getTranslate('CodeEditView');
    $this->setParam('translate', $translate);

    $this->setParam('dataID', $id);

    // Récupération des informations du code en question
    $codeModel = new codeModel();
    $code = $codeModel->getCode($id);
    $this->setParam('code', $code);

    $moldRefSpecs = $codeModel->getRefSpecsPossible('R1');
    $this->setParam('refSpecs', $moldRefSpecs);

    $statusList = $codeModel->getStatusList();
    $this->setParam('statusList', $statusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Code_App');
    $this->setParam('dataList', $dataList);

    $dataCategoryList = $dataModel->getDataCategoryListUserEdit('Code_App');
    $this->setParam('dataCategoryList', $dataCategoryList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    $this->setParam('dataRule', frontController::getSession('role')['dataRule']['Code_App']);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle') . ' ' . $id);
    $this->setView('codeEdit');
    $this->setApp('Code_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('edit', 'Code_App'))
      $this->showView();
    else
      header('Location: /code');
  }

  /**
   * Fonction qui permet de modifier un code
   * Paramètre :
   *  @param POST : Données en POST sur le code
   * @return update : Edition du code
   */
  public function edit()
  {
    // Vérification des droits utilisateurs
    if (frontController::haveRight('edit', 'Code_App')) {
      // Récupération des informations sur les données
      $dataModel = new dataModel();

      $dataList = $dataModel->getDataList('Code_App');


      $codeModel = new codeModel();

      // Récupération des données de l'ancien moule
      $oldCode = $codeModel->getCode($_POST['CODE_PROD_1']);

      // Construction du moule avec les données
      $code = array();

      // Ajout des données actuelles
      foreach ($dataList as $dataBaseName => $data) {
        $code[$dataBaseName] = $oldCode[$dataBaseName];

        // Conversion des champs date
        if ($dataBaseName == 'SMARTEAM')
          $code[$dataBaseName] = $oldCode[$dataBaseName] ? date_create_from_format('d-m-Y H:i:s', $oldCode[$dataBaseName])->format('Y-m-d\TH:i:s') : '';
        else
          $code[$dataBaseName] = $oldCode[$dataBaseName];
      }

      // Ajout des nouvelles données
      foreach ($dataList as $dataBaseName => $data) {
        if (isset($_POST[$dataBaseName]))
          $code[$dataBaseName] = $_POST[$dataBaseName];
      }

      // Modification du code
      $codeModel->editCode($code);


      // Ajout de l'historique pour ce code
      $historyModel = new historyModel();
      $historyModel->add('Code_App', $oldCode, $code, 'update', $code['CODE_PROD_1']);
    }

    header('Location: /code/advancedView/' . $code['CODE_PROD_1']);
  }

  /**
   * Fonction qui permet de supprimer un code
   * Paramètre :
   *  @param GET : Données en GET sur le code à supprimer
   * @return delete : Suppression d'un code
   */
  public function remove($id)
  {
    // Vérification des droits utilisateurs
    if (frontController::haveRight('remove', 'Code_App')) {
      $codeModel = new codeModel();

      // Récupération de l'ancien code
      $oldCode = $codeModel->getCode($id);

      // Récupération du nouveau code (vide)
      $newCode = $codeModel->getCode($id);

      foreach ($newCode as $key => $data)
        $newCode[$key] = '';


      if ($id != '')
        $codeModel->removeCode($id);


      // Ajout de l'historique pour ce code
      $historyModel = new historyModel();
      $historyModel->add('Code_App', $oldCode, $newCode, 'delete', $id);
    }

    echo "<script>window.close();</script>";
  }

  /**
   * Fonction qui permet d'exporter la liste des codes au format Excel
   * @return view : Page d'exportation de la liste des codes
   */
  public function export()
  {
    // Ajout des accès pour la visualisation des données
    $dataRule = frontController::getSession('role')['dataRule']['Code_App'];

    $filterList = $dataRule['FILTER'];
    foreach ($filterList as $filter => $default) {
      if (!isset($filterList[$filter]['default']))
        $filterList[$filter] = array('default' => true);
    }

    $codeModel = new codeModel();

    // Récupération de la liste des codes
    $codeList = $codeModel->getCodeListByStatusList($filterList);


    // Traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');


    $dataModel = new dataModel();

    // Récupération de la liste des données
    $codeDataList = $dataModel->getDataList('Code_App');

    // traduction des données
    $translateData = $this->getTranslate('Data_List');


    // Création du fichier Excel
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Code List');


    if (frontController::haveRight('view', 'Code_App')) {
      // Ecriture des données
      $currentColumn = 1;
      $currentRow = 1;

      // Parcours de toute les données disponible
      foreach ($dataRule as $dataBaseName => $rule) {
        if ($dataBaseName != 'FILTER') {
          // Ecriture du nom de la donnée
          $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $this->getVerifyTranslate($translateData, $codeDataList[$dataBaseName]['name']));
          $sheet->getCellByColumnAndRow($currentColumn, $currentRow)
            ->getStyle()
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF9999FF');
          $sheet->getCellByColumnAndRow($currentColumn, $currentRow)
            ->getStyle()
            ->getFont()
            ->setBold(true);
          $currentRow = $currentRow + 1;

          // Ecriture de toute les données disponibles
          foreach ($codeList as $key => $code) {
            switch ($dataBaseName) {
              case 'STATUS':
                if ($code[$dataBaseName] != '')
                  $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $this->getVerifyTranslate($translateStatusList, $code[$dataBaseName]));
                else
                  $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, '');
                break;

              default:
                $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $code[$dataBaseName]);
                break;
            }
            $currentRow = $currentRow + 1;
          }

          // Passage à la colonne suivante
          $currentColumn = $currentColumn + 1;
          $currentRow = 1;
        }
      }

      // Récupération de la dernière colonne
      $maxColumn = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColumn - 1);

      // Ajout du filtre automatique pour chaque colonne
      $sheet->setAutoFilter('A1:' . $maxColumn . '1');

      // Centrage du texte
      $sheet->getStyle('A1:' . $maxColumn . (count($codeList) + 1))->getAlignment()->setHorizontal('center');

      // Ajout de la taille automatique pour chaque colonne
      foreach (range(1, $currentColumn - 1) as $columnID) {
        $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnID))->setWidth(150);
        $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnID))->setAutoSize(true);
      }
    }

    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Code_App')) {
      $fileName = './tmp/Code_Export_' . date('d-m-Y_H-i-s') . '.xlsx';

      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $fileName . '"');
      header('Cache-Control: max-age=0');

      // Ecriture du fichier Excel
      $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
    }
    else
      header('Location: /code');
  }

  /**
   * Fonction qui permet d'afficher les graphiques d'analyse sur les codes
   * @return view : Page d'analyse des codes
   */
  public function analyse()
  {
    $codeModel = new codeModel();

    // Récupération des statistiques sur les statuts
    $codeNbStatusList = $codeModel->getNbCodeByStatus();
    $this->setParam('codeNbStatusList', $codeNbStatusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    $translate = $this->getTranslate('CodeAnalyse');
    $this->setParam('translate', $translate);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Code_App');
    $this->setParam('dataList', $dataList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('codeAnalyse');
    $this->setApp('Code_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Code_App'))
      $this->showView();
    else
      header('Location: /code');
  }
}

<?php

/**
 * Classe qui contient le contrôleur pour les moules
 * - Affichage de l'index : Redirection vers la liste des moules
 * - Affichage de la liste des moules
 * - Affichage de la vue avancée d'un moule
 * - Affichage du formulaire pour ajouter un moule
 * - Ajout d'un moule
 * - Affichage du formulaire pour éditer un moule
 * - Edition d'un moule
 * - Suppression d'un moule
 * - Exportation de la liste des moule
 * - Affichage de l'analyse des moules
 */
class moldController extends Controller
{
  /**
   * Fonction qui permet d'afficher l'index
   */
  public function index()
  {
    $this->view();
  }

  /**
   * Fonction qui permet d'afficher la liste des moules et de gérer la page de base
   */
  public function view()
  {
    $translate = $this->getTranslate('Mold');
    $this->setParam('translate', $translate);


    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    // Système de gestion du formulaire de tri
    $sort = array();

    // Récupération de la liste des statuts pour le système de trie
    $statusListUser = frontController::getSession('role')['dataRule']['Mold_App']['FILTER'];

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


    // Récupération de la liste des moules selon les filtres sinon valeur par défaut
    $moldModel = new moldModel();
    $moldList = $moldModel->getMoldListByStatusList($sort);
    $this->setParam('moldList', $moldList);

    $statusList = $moldModel->getStatusListDic();
    $this->setParam('statusList', $statusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);

    $locationTypeList = $moldModel->getLocationTypeListDic();
    $this->setParam('locationTypeList', $locationTypeList);

    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    // Ajout des accès pour la visualisation des données
    $this->setParam('dataRule', frontController::getSession('role')['dataRule']['Mold_App']);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Mold_App');
    $this->setParam('dataList', $dataList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('mold');
    $this->setApp('Mold_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Mold_App'))
      $this->showView();
    else
      header('Location: /');
  }

  /**
   * Fonction qui permet d'afficher les détails d'un moule
   * Paramètre :
   *  @param int : Identifiant unique du moule
   * @return view : Affichage de la vue
   */
  public function advancedView($id)
  {
    $translate = $this->getTranslate('MoldAdvancedView');
    $this->setParam('translate', $translate);


    $moldModel = new moldModel();

    $mold = $moldModel->getMold($id);
    $this->setParam('mold', $mold);
    $moldCodePossible = $moldModel->getMoldCodePossible($id);
    $this->setParam('moldCodePossible', $moldCodePossible);


    $statusList = $moldModel->getStatusListDic();
    $this->setParam('statusList', $statusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);

    $locationTypeList = $moldModel->getLocationTypeListDic();
    $this->setParam('locationTypeList', $locationTypeList);

    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    // Ajout des accès pour la visualisation des données
    $this->setParam('dataRule', frontController::getSession('role')['dataRule']['Mold_App']);

    
    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Mold_App');
    $this->setParam('dataList', $dataList);

    $dataCategoryList = $dataModel->getDataCategoryListUser('Mold_App');
    $this->setParam('dataCategoryList', $dataCategoryList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle') . ' ' . $id);
    $this->setView('moldVision');
    $this->setApp('Mold_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Mold_App'))
      $this->showView();
    else
      header('Location: /');
  }



  /**
   * Fonction qui permet d'afficher le formulaire d'ajout pour un moule
   * @return view : Formulaire d'ajout d'un moule
   */
  public function addView()
  {
    $translate = $this->getTranslate('MoldAddView');
    $this->setParam('translate', $translate);

    $moldModel = new moldModel();
    $patternSizeList = $moldModel->getPatternSizeList();
    $this->setParam('patternSizeList', $patternSizeList);

    $statusList = $moldModel->getStatusList();
    $this->setParam('statusList', $statusList);

    $codeModel = new codeModel();
    $moldRefSpecs = $codeModel->getRefSpecsPossible('V1');
    $this->setParam('refSpecs', $moldRefSpecs);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);

    $locationTypeList = $moldModel->getLocationTypeList();
    $this->setParam('locationTypeList', $locationTypeList);

    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Mold_App');
    $this->setParam('dataList', $dataList);

    $dataCategoryList = $dataModel->getDataCategoryListUser('Mold_App');
    $this->setParam('dataCategoryList', $dataCategoryList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('moldAdd');
    $this->setApp('Mold_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('add', 'Mold_App'))
      $this->showView();
    else
      header('Location: /mold');
  }



  /**
   * Fonction qui permet d'ajouter un moule
   * Paramètres :
   *  @param POST : Données en POST sur le moule
   * @return insert : Ajout d'un moule
   */
  public function add()
  {
    // Vérification des droits utilisateurs
    if (frontController::haveRight('add', 'Mold_App')) {
      // Récupération des informations sur les données
      $dataModel = new dataModel();

      $dataList = $dataModel->getDataList('Mold_App');


      // Construction du moule avec les données
      $mold = array();
      foreach ($dataList as $dataBaseName => $data)
        $mold[$dataBaseName] = isset($_POST[$dataBaseName]) ? $_POST[$dataBaseName] : '';


      // Ajout du moule
      $moldModel = new moldModel();
      $moldModel->addMold($mold);


      // Récupération des données de l'ancien moule
      $oldMold = $moldModel->getMold($mold['REF_MOULE']);

      foreach ($oldMold as $key => $data)
        $oldMold[$key] = '';


      // Ajout de l'historique pour ce moule
      $historyModel = new historyModel();
      $historyModel->add('Mold_App', $oldMold, $mold, 'insert', $mold['REF_MOULE']);
    }

    header('Location: /mold');
  }

  /**
   * Fonction qui permet d'afficher le formulaire de modification pour le moule
   * Paramètre :
   *  @param string : Identifiant unique du moule à modifier
   * @return view : Forumaile de modification du moule
   */
  public function editView($id)
  {
    $translate = $this->getTranslate('MoldEditView');
    $this->setParam('translate', $translate);

    $this->setParam('dataID', $id);

    // Récupération des informations du moule en question
    $moldModel = new moldModel();

    $mold = $moldModel->getMold($id);
    $this->setParam('mold', $mold);

    $patternSizeList = $moldModel->getPatternSizeList();
    $this->setParam('patternSizeList', $patternSizeList);

    $codeModel = new codeModel();
    $moldRefSpecs = $codeModel->getRefSpecsPossible('V1');
    $this->setParam('refSpecs', $moldRefSpecs);

    $statusList = $moldModel->getStatusList();
    $this->setParam('statusList', $statusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);

    $locationTypeList = $moldModel->getLocationTypeList();
    $this->setParam('locationTypeList', $locationTypeList);

    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Mold_App');
    $this->setParam('dataList', $dataList);

    $dataCategoryList = $dataModel->getDataCategoryListUserEdit('Mold_App');
    $this->setParam('dataCategoryList', $dataCategoryList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);
    $translateDataCategory = $this->getTranslate('Data_Category_List');
    $this->setParam('translateDataCategory', $translateDataCategory);


    $this->setParam('dataRule', frontController::getSession('role')['dataRule']['Mold_App']);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle') . ' ' . $id);
    $this->setView('moldEdit');
    $this->setApp('Mold_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('edit', 'Mold_App'))
      $this->showView();
    else
      header('Location: /mold');
  }

  public function getCodes($spec) {
    $codeModel = new codeModel();

    echo json_encode($codeModel->getCodesFromSpec($spec));
  }

  /**
   * Fonction qui permet de modifier un moule
   * Paramètre :
   *  @param POST : Données en POST sur le moule
   * @return update : Edition du moule
   */
  public function edit()
  {
    // Vérification des droits utilisateurs
    if (frontController::haveRight('edit', 'Mold_App')) {
      // Récupération des informations sur les données
      $dataModel = new dataModel();

      $dataList = $dataModel->getDataList('Mold_App');


      $moldModel = new moldModel();

      // Récupération des données de l'ancien moule
      $oldMold = $moldModel->getMold($_POST['REF_MOULE']);

      // Construction du moule avec les données
      $mold = array();

      // Ajout des données actuelles
      foreach ($dataList as $dataBaseName => $data) {
        $mold[$dataBaseName] = $oldMold[$dataBaseName];

        // Conversion des champs date
        if ($dataBaseName == 'POSITION_ACTUEL_DATE' or
        $dataBaseName == 'START_DATE' or
        $dataBaseName == 'END_DATE' or
        $dataBaseName == 'LAST_CHECK_DATE' or
        $dataBaseName == 'DATE_LAST_ENVELOPPE' or
        $dataBaseName == 'DATE_GET_MOLD')
          $mold[$dataBaseName] = $oldMold[$dataBaseName] ? date_create_from_format('d-m-Y H:i:s', $oldMold[$dataBaseName])->format('Y-m-d\TH:i:s') : '';
        else
          $mold[$dataBaseName] = $oldMold[$dataBaseName];
      }

      // Ajout des nouvelles données
      foreach ($dataList as $dataBaseName => $data) {
        if (isset($_POST[$dataBaseName]))
          $mold[$dataBaseName] = $_POST[$dataBaseName];
      }

      // Modification du moule
      $moldModel->editMold($mold);


      // Ajout de l'historique pour ce moule
      $historyModel = new historyModel();
      $historyModel->add('Mold_App', $oldMold, $mold, 'update', $mold['REF_MOULE']);
    }

    header('Location: /mold/advancedView/' . $mold['REF_MOULE']);
  }

  /**
   * Fonction qui permet de supprimer un moule
   * Paramètre :
   *  @param GET : Données en GET sur le moule à supprimer
   * @return delete : Suppression d'un moule
   */
  public function remove($id)
  {
    // Vérification des droits utilisateurs
    if (frontController::haveRight('remove', 'Mold_App')) {
      $moldModel = new moldModel();

      // Récupération de l'ancien moule
      $oldMold = $moldModel->getMold($id);

      // Récupération du nouveau moule (vide)
      $newMold = $moldModel->getMold($id);

      foreach ($newMold as $key => $data)
        $newMold[$key] = '';


      if ($id != '')
        $moldModel->removeMold($id);


      // Ajout de l'historique pour ce moule
      $historyModel = new historyModel();
      $historyModel->add('Mold_App', $oldMold, $newMold, 'delete', $id);
    }

    echo "<script>window.close();</script>";
  }

  /**
   * Fonction qui permet d'exporter la liste des moules au format Excel
   * @return view : Page d'exportation de la liste des moules
   */
  public function export()
  {
    // Ajout des accès pour la visualisation des données
    $dataRule = frontController::getSession('role')['dataRule']['Mold_App'];

    $filterList = $dataRule['FILTER'];
    foreach ($filterList as $filter => $default) {
      if (!isset($filterList[$filter]['default']))
        $filterList[$filter] = array('default' => true);
    }

    $moldModel = new moldModel();

    // Récupération de la liste des moules
    $moldList = $moldModel->getMoldListByStatusList($filterList);


    // Traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');

    // Traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');


    $dataModel = new dataModel();

    // Récupération de la liste des données
    $moldDataList = $dataModel->getDataList('Mold_App');

    // traduction des données
    $translateData = $this->getTranslate('Data_List');


    // Création du fichier Excel
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Mold List');


    if (frontController::haveRight('view', 'Mold_App')) {
      // Ecriture des données
      $currentColumn = 1;
      $currentRow = 1;

      // Parcours de toute les données disponible
      foreach ($dataRule as $dataBaseName => $rule) {
        if ($dataBaseName != 'FILTER') {
          // Ecriture du nom de la donnée
          $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $this->getVerifyTranslate($translateData, $moldDataList[$dataBaseName]['name']));
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
          foreach ($moldList as $key => $mold) {
            switch ($dataBaseName) {
              case 'STATUT':
                if ($mold[$dataBaseName] != '')
                  $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $this->getVerifyTranslate($translateStatusList, $mold[$dataBaseName]));
                else
                  $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, '');
                break;

              case 'LOCATION_TYPE':
                if ($mold[$dataBaseName] != '')
                  $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $this->getVerifyTranslate($translateLocationTypeList, $mold[$dataBaseName]));
                else
                  $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, '');
                break;

              default:
                $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $mold[$dataBaseName]);
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
      $sheet->getStyle('A1:' . $maxColumn . (count($moldList) + 1))->getAlignment()->setHorizontal('center');

      // Ajout de la taille automatique pour chaque colonne
      foreach (range(1, $currentColumn - 1) as $columnID) {
        $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnID))->setWidth(150);
        $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnID))->setAutoSize(true);
      }
    }

    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Mold_App')) {
      $fileName = './tmp/Mold_Export_' . date('d-m-Y_H-i-s') . '.xlsx';

      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $fileName . '"');
      header('Cache-Control: max-age=0');

      // Ecriture du fichier Excel
      $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
    }
    else
      header('Location: /mold');
  }

  /**
   * Fonction qui permet d'afficher les graphiques d'analyse sur les moules
   * @return view : Page d'analyse des moules
   */
  public function analyse()
  {
    $moldModel = new moldModel();

    // Récupération des statistiques sur les statuts
    $moldNbStatusList = $moldModel->getNbMoldByStatus();
    $this->setParam('moldNbStatusList', $moldNbStatusList);

    // traduction des statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);


    $moldNbLocationList = $moldModel->getNbMoldByLocation();
    $this->setParam('moldNbLocationList', $moldNbLocationList);

    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    $translate = $this->getTranslate('MoldAnalyse');
    $this->setParam('translate', $translate);


    // Récupération de la liste des données avec leurs descriptions
    $dataModel = new dataModel();

    $dataList = $dataModel->getDataList('Mold_App');
    $this->setParam('dataList', $dataList);

    // traduction des données et des catégories
    $translateData = $this->getTranslate('Data_List');
    $this->setParam('translateData', $translateData);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle'));
    $this->setView('moldAnalyse');
    $this->setApp('Mold_App');


    // Vérification des droits utilisateurs
    if (frontController::haveRight('view', 'Mold_App'))
      $this->showView();
    else
      header('Location: /mold');
  }
}

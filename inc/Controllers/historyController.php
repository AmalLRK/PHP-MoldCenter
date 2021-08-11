<?php

class historyController extends Controller
{
  /**
   * Fonction qui permet de voir l'historique global d'une application
   * Paramètre :
   *  @param string : Nom de l'application
   * @return View : Vue des données de l'historique
   */
  public function view($appBaseName, $dataID = '')
  {
    $translate = $this->getTranslate('History');
    $this->setParam('translate', $translate);

    $translateHistoryTypeList = $this->getTranslate('History_Type_List');
    $this->setParam('translateHistoryTypeList', $translateHistoryTypeList);


    // Récupération de la liste des applications disponibles
    $indexModel = new indexModel();
    $pageList = $indexModel->getIndexList();
    $this->setParam('appBaseName', $appBaseName);
    $this->setParam('pageList', $pageList);

    // Récupération de la traduction pour l'application
    $translateIndexList = $this->getTranslate('Index_List');
    $this->setParam('translateIndexList', $translateIndexList);


    // Récupération des informations sur les données de l'application
    $dataModel = new dataModel();
    $datalist = $dataModel->getDataList($appBaseName);
    $this->setParam('datalist', $datalist);

    $translateDataList = $this->getTranslate('Data_List');
    $this->setParam('translateDataList', $translateDataList);


    // Récupération de la traduction sur les statuts
    $translateStatusList = $this->getTranslate('Status_List');
    $this->setParam('translateStatusList', $translateStatusList);

    // traduction des localisations
    $translateLocationTypeList = $this->getTranslate('Location_List');
    $this->setParam('translateLocationTypeList', $translateLocationTypeList);


    // Récupération des filtres de l'historique
    $sort = array();

    if (!empty($_GET)) {
      $sort['MIN_DATE'] = date('d/m/Y', strtotime($_GET['min_date']));
      $sort['MAX_DATE'] = date('d/m/Y', strtotime($_GET['max_date']));
    } else {
      $sort['MIN_DATE'] = date('d/m/Y', strtotime('-30 days'));
      $sort['MAX_DATE'] = date('d/m/Y');
    }

    $this->setParam('sort', $sort);


    // Récupération de l'historique de l'application
    $historyModel = new historyModel();
    if ($dataID != '')
      $historyList = $historyModel->getHistoryOfDataByDate($appBaseName, $dataID, $sort['MIN_DATE'], $sort['MAX_DATE']);
    else
      $historyList = $historyModel->getHistoryOfAppByDate($appBaseName, $sort['MIN_DATE'], $sort['MAX_DATE']);
    $this->setParam('historyList', $historyList);

    $historyColumnList = $historyModel->getColumnList();
    $this->setParam('historyColumnList', $historyColumnList);


    $this->setParam('dataID', $dataID);


    $this->setParam('titlePage', $this->getVerifyTranslate($translate, 'pageTitle') . ' ' . $this->getVerifyTranslate($translateIndexList, $pageList[$appBaseName]['name']) . ' ' . $dataID);
    $this->setView('history');
    $this->setApp('History');

    // Vérification des droits utilisateurs pour afficher les bonnes informations
    if (frontController::haveRight('moderator', 'Mold_App'))
      $this->showView();
    elseif ($dataID != '')
      $this->showView();
    else
      header('Location: /' . $pageList[$appBaseName]['link']);
  }

  /**
   * Fonction qui permet d'exporter la liste de l'historique en fichier Excel
   * Paramètre :
   *  @param string : Nom de l'application pour l'exportation de son historique
   * @return export : Exportation en fichier Excel
   */
  public function export($appBaseName)
  {
    // Récupération de l'historique de l'application
    $historyModel = new historyModel();
    $historyList = $historyModel->getHistoryOfApp($appBaseName);
    $historyColumnList = $historyModel->getColumnList();

    $translate = $this->getTranslate('History');

    // Récupération de la traduction pour l'application
    $translateIndexList = $this->getTranslate('Index_List');

    // Récupération des informations sur les données de l'application
    $dataModel = new dataModel();
    $datalist = $dataModel->getDataList($appBaseName);
    $translateDataList = $this->getTranslate('Data_List');

    // Récupération de la traduction sur les statuts
    $translateStatusList = $this->getTranslate('Status_List');


    // Création du fichier Excel
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('History');


    // Ecriture des données
    $currentColumn = 1;
    $currentRow = 1;

    // Parcours de toute les données disponible
    foreach ($historyColumnList as $columnBaseName => $columnBddName) {
      // Ecriture du nom de la donnée
      $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $this->getVerifyTranslate($translate, $columnBaseName));
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
      foreach ($historyList as $key => $historyData) {
        $sheet->setCellValueByColumnAndRow($currentColumn, $currentRow, $historyData[$columnBddName]);
        $currentRow = $currentRow + 1;
      }

      // Passage à la colonne suivante
      $currentColumn = $currentColumn + 1;
      $currentRow = 1;
    }

    // Récupération de la dernière colonne
    $maxColumn = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColumn - 1);

    // Ajout du filtre automatique pour chaque colonne
    $sheet->setAutoFilter('A1:' . $maxColumn . '1');

    // Centrage du texte
    $sheet->getStyle('A1:' . $maxColumn . (count($historyList) + 1))->getAlignment()->setHorizontal('center');

    // Ajout de la taille automatique pour chaque colonne
    foreach (range(1, $currentColumn - 1) as $columnID) {
      $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnID))->setWidth(150);
      $sheet->getColumnDimension(PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnID))->setAutoSize(true);
    }


    // Vérification des droits pour obtenir le fichier d'exportation
    if (frontController::haveRight('moderator', 'Mold_App')) {
      $fileName = './tmp/History_Export_' . date('d-m-Y_H-i-s') . '.xlsx';

      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $fileName . '"');
      header('Cache-Control: max-age=0');

      // Ecriture du fichier Excel
      $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
      $writer->save('php://output');
    } else
      header('Location: /');    
  }
}

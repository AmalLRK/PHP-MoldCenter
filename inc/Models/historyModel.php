<?php

/**
 * Classe qui contient le modèle pour l'historique
 * - Récupération de l'historique d'une application
 * - Récupération de l'historique d'une donnée
 */
class historyModel extends Model
{
  /**
   * Fonction qui permet de récupérer l'historique d'une application
   * Paramètre :
   *  @param string : Nom de l'application
   * @return array : Liste de l'historique de l'application
   */
  public function getHistoryOfApp($appBaseName)
  {
    $this->query(
      'SELECT t1.APP, t1.ID_DATA, t1.DATETIME, t1.DATA_BASE_NAME, t1.NEW_VALUE, t1.USERID,
      t2.TYPE, t2.COLOR AS TYPE_COLOR ,
      t3.NOMPRE
      FROM MOLD_HISTORY t1, MOLD_HISTORY_TYPE t2, MOLD_USERS t3
      WHERE t1.APP = :app AND t1.HISTORY_TYPE = t2.ID AND t1.USERID = t3.USERID
      ORDER BY t1.DATETIME DESC',
      array(
        ':app' => $appBaseName
      )
    );

    $historyList = $this->fetchAll('Dictionary');

    return $historyList;
  }

  /**
   * Fonction qui permet de récupérer l'historique d'une application dans
   * un intervalle de date voulu
   * Paramètres :
   *  @param string : Nom de l'application
   *  @param string : Date de début sous le format DD/MM/YYYY
   *  @param string : Date de fin sous le format DD/MM/YYYY
   * @return array : Liste de l'historique de l'application
   */
  public function getHistoryOfAppByDate($appBaseName, $dateMin, $dateMax)
  {
    $this->query(
      'SELECT t1.APP, t1.ID_DATA, t1.DATETIME, t1.DATA_BASE_NAME, t1.NEW_VALUE, t1.USERID,
      t2.TYPE, t2.COLOR AS TYPE_COLOR,
      t3.NOMPRE
      FROM MOLD_HISTORY t1, MOLD_HISTORY_TYPE t2, MOLD_USERS t3
      WHERE t1.APP = :app AND t1.HISTORY_TYPE = t2.ID AND t1.USERID = t3.USERID
      AND t1.DATETIME BETWEEN TO_DATE(:dateMin, \'DD/MM/YYYY\') AND TO_DATE(:dateMax, \'DD/MM/YYYY\') + 1
      ORDER BY t1.DATETIME DESC',
      array(
        ':app' => $appBaseName,
        ':dateMin' => $dateMin,
        ':dateMax' => $dateMax
      )
    );

    $historyList = $this->fetchAll('Dictionary');

    return $historyList;
  }

  /**
   * Fonction qui permet de récupérer l'historique d'une donnée
   * Paramètres :
   *  @param string : Nom de l'application
   *  @param string : Nom de la donnée
   * @return array : Liste de l'historique de la donnée
   */
  public function getHistoryOfData($appBaseName, $dataID)
  {
    $this->query(
      'SELECT t1.APP, t1.ID_DATA, t1.DATETIME, t1.DATA_BASE_NAME, t1.NEW_VALUE, t1.USERID,
      t2.TYPE, t2.COLOR AS TYPE_COLOR ,
      t3.NOMPRE
      FROM MOLD_HISTORY t1, MOLD_HISTORY_TYPE t2, MOLD_USERS t3
      WHERE t1.APP = :app AND t1.ID_DATA = :data AND t1.HISTORY_TYPE = t2.ID AND t1.USERID = t3.USERID
      ORDER BY t1.DATETIME DESC',
      array(
        ':app' => $appBaseName,
        ':data' => $dataID
      )
    );

    $historyList = $this->fetchAll('Dictionary');

    return $historyList;
  }

  /**
   * Fonction qui permet de récupérer l'historique d'une donnée
   * Paramètres :
   *  @param string : Nom de l'application
   *  @param string : Nom de la donnée
   *  @param string : Date de début sous le format DD/MM/YYYY
   *  @param string : Date de fin sous le format DD/MM/YYYY
   * @return array : Liste de l'historique de la donnée
   */
  public function getHistoryOfDataByDate($appBaseName, $dataID, $dateMin, $dateMax)
  {
    $this->query(
      'SELECT t1.APP, t1.ID_DATA, t1.DATETIME, t1.DATA_BASE_NAME, t1.NEW_VALUE, t1.USERID,
      t2.TYPE, t2.COLOR AS TYPE_COLOR ,
      t3.NOMPRE
      FROM MOLD_HISTORY t1, MOLD_HISTORY_TYPE t2, MOLD_USERS t3
      WHERE t1.APP = :app AND t1.ID_DATA = :data AND t1.HISTORY_TYPE = t2.ID AND t1.USERID = t3.USERID
      AND t1.DATETIME BETWEEN TO_DATE(:dateMin, \'DD/MM/YYYY\') AND TO_DATE(:dateMax, \'DD/MM/YYYY\') + 1
      ORDER BY t1.DATETIME DESC',
      array(
        ':app' => $appBaseName,
        ':data' => $dataID,
        ':dateMin' => $dateMin,
        ':dateMax' => $dateMax
      )
    );

    $historyList = $this->fetchAll('Dictionary');

    return $historyList;
  }

  /**
   * Fonction qui permet de récupérer la liste des type d'historique
   * @return array : Liste des types d'historique
   */
  public function getHistoryTypeList()
  {
    $this->query(
      'SELECT TYPE, COLOR
      FROM MOLD_HISTORY_TYPE');

    $historyTypeList = $this->fetchAll('Dictionary');

    return $historyTypeList;
  }

  /**
   * Fonction qui permet d'ajouter un enregistrement dans l'historique
   * Paramètres :
   *  @param string : Nom de l'application
   *  @param string : Identifiant de la donnée
   *  @param date : Date de la donnée
   *  @param string : Type d'historique
   *  @param string : Nom de base de la donnée
   *  @param string : Nouvelle valeur
   *  @param string : Identifiant de l'utilisateur
   * @return Insert : Insertion de la donnée dans l'historique
   */
  public function addHistory($appBaseName, $dataID, $dateTime, $historyType, $dataBaseName, $newValue, $userID)
  {
    $this->query(
      'INSERT INTO MOLD_HISTORY (
        APP,
        ID_DATA,
        DATETIME,
        HISTORY_TYPE,
        DATA_BASE_NAME,
        NEW_VALUE,
        USERID)
			VALUES (
        :app,
        :dataID,
        TO_DATE(:dateTime, \'YYYY-MM-DD HH24:MI:SS\'),
        (SELECT ID FROM MOLD_HISTORY_TYPE WHERE TYPE = :historyType),
        :dataBaseName,
        :newValue,
        :userID)',
      array(
        ':app' => $appBaseName,
        ':dataID' => $dataID,
        ':dateTime' => $dateTime,
        ':historyType' => $historyType,
        ':dataBaseName' => $dataBaseName,
        ':newValue' => $newValue,
        ':userID' => strtoupper($userID)
      )
    );
  }

  /**
   * Fonction qui permet d'ajouter des lignes dans l'historique
   * Paramètres : POST
   *  @param string appBaseName : Nom de l'application
   *  @param array oldData : Anciennes données
   *  @param array newData : Nouvelles données
   *  @param string historyType : Type d'historique (insert / update / delete)
   *  @param string dataID : Identifiant unique de la donnée
   * @return Insert : Ajout de l'enregistrement dans l'historique
   */
  public function add($appBaseName, $oldData, $newData, $historyType, $dataID)
  {
    // Récupération des informations sur les données de l'application
    $dataModel = new dataModel();
    $datalist = $dataModel->getDataList($appBaseName);


    // Récupération des anciennes et nouvelles données
    $oldValue = array();
    $newValue = array();

    // Correction des formats de date
    foreach ($datalist as $dataBaseName => $data) {
      if ($dataBaseName == 'POSITION_ACTUEL_DATE' or
        $dataBaseName == 'START_DATE' or
        $dataBaseName == 'END_DATE' or
        $dataBaseName == 'LAST_CHECK_DATE' or
        $dataBaseName == 'DATE_LAST_ENVELOPPE' or
        $dataBaseName == 'DATE_GET_MOLD' or
        $dataBaseName == 'SMARTEAM') {
          $oldValue[$dataBaseName] = $oldData[$dataBaseName] ? date_create_from_format('d-m-Y H:i:s', $oldData[$dataBaseName])->format('Y-m-d\TH:i') : '';
          $newValue[$dataBaseName] = $newData[$dataBaseName];
        } else {
          $oldValue[$dataBaseName] = $oldData[$dataBaseName];
          $newValue[$dataBaseName] = $newData[$dataBaseName];
        }
    }


    // Récupération des changements effectué
    $changement = array();

    foreach ($newValue as $dataBaseName => $value) {
      if ($value != $oldValue[$dataBaseName])
        $changement[$dataBaseName] = $value;
    }


    // Enregistrement dans l'historique des changements apportés
    foreach ($changement as $dataBaseName => $value) {
      $this->addHistory(
        $appBaseName,
        $dataID,
        date('Y-m-d H:i:s'),
        $historyType,
        $dataBaseName,
        $value,
        frontController::getSession('userid')
      );
    }
  }

  /**
   * Fonction qui permet de récupérer la liste des nom des colonnes
   * @return array : Liste des colonnes
   */
  public function getColumnList()
  {
    $columnList = array();

    $columnList['dataIDName'] = 'ID_DATA';
    $columnList['dateTimeName'] = 'DATETIME';
    $columnList['historyTypeName'] = 'HISTORY_TYPE';
    $columnList['dataBaseNameName'] = 'DATA_BASE_NAME';
    $columnList['newValueName'] = 'NEW_VALUE';
    $columnList['userID'] = 'USERID';
    $columnList['userName'] = 'NOMPRE';

    return $columnList;
  }
}

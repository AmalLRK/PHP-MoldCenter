<?php

/**
 * Classe qui contient le modèle pour les moules
 * - Récupération d'un moule
 * - Récupération de la liste de tous les moules
 * - Récupération de la liste des moules d'un statuts particuliers
 * - Récupération de la liste des statuts Disponible
 * - Récupération de la liste des statuts Disponible sous forme de dictionnaire
 * - Récupération de la liste des localisation Disponible
 * - Récupération de la liste des localisation Disponible sous forme de dictionnaire
 * - Récupération du nombre de moule par statuts
 * - Récupération du nombre de moule par localisation
 * - Ajout d'un moule
 * - Edition d'un moule
 * - Suppression d'un moule
 */
class moldModel extends Model
{
  /**
   * Fonction qui permet de récupérer un moule
   * Paramètre :
   *  @param string : Identifiant unique du moule
   * @return array : Liste des données du moule
   */
  public function getMold($id)
  {
    $this->query(
      'SELECT distinct t1.REF_MOULE, t1.STATUS_MOULE, t1.COMMENTAIRE, t1.PATTERNSIZE, t1.REF_SPECS, t1.CODE_PROD_2,
      t1.DIM_CTN2, t1.GOUPILLE2, t1.REF_CTN, t1.REF_SEGMENT, t1.REF_FLANC_SUP, t1.REF_FLANC_INF,
      t1.TYPE_CTN2, t1.POSITION_ACTUEL_DATE, t1.GSWR_BOTTOM, t1.GSWR_TOP, t1.GTRD, t1.DRAWING,
      t2.S_DIAMETRE, t2.L_DIAMETRE, t2.JEUX_TOTAL_SECTEUR,
      t3.NB_CYCLE_CUISSON_LIFE, t3.NB_CYCLE_CUISSON_NOW, t3.DATE_GET_MOLD, t3.CLOGGED_HOLES,
      t4.LOCATION AS LOCATION_CHOICE,
      CASE WHEN t9.NO_PRESSE IS NULL THEN
          t4.LOCATION
      ELSE
          (t9.NO_PRESSE || CASE t9.ID_CAVITE WHEN \'-\' THEN \'\' ELSE t9.ID_CAVITE END)
      END AS LOCATION,
      t5.TYPE as STATUT, t5.COLOR as STATUT_COLOR,
      t6.TYPE as LOCATION_TYPE, t6.COLOR as LOCATION_TYPE_COLOR,
      t7.LAST_CHECK_DATE, t7.LAST_RESULT, t7.COMMENT_METRO,
      t8.START_DATE, t8.END_DATE, t8.COMMENT_EXCEPT,
      t9.NO_PRESSE, t9.ID_CAVITE
      FROM EXT_MOLDEQUIPMENTSLIST t1
      LEFT JOIN EXT_MOLDEQUIPMENTSPEC t2 ON t1.CODE_PROD_2 like \'%\' || t2.CODE_PROD_1 || \'%\'
      LEFT JOIN MOLD_TECH t3 ON t1.REF_MOULE = t3.ID_MOLD
      LEFT JOIN CAVITES t9 ON t9.NO_MOULE = t1.REF_MOULE
      LEFT JOIN MOLD_STATUS t4 ON t1.REF_MOULE = t4.ID_MOLD
      LEFT JOIN MOLD_STATUS_TYPE t5 ON t5.ID = t4.STATUS_TYPE
      LEFT JOIN MOLD_LOCATION_TYPE t6 ON t6.ID = t4.LOCATION_TYPE
      LEFT JOIN MOLD_METROLOGY t7 ON t7.ID_MOLD = t1.REF_MOULE
      LEFT JOIN MOLD_EXEMPTION t8 ON t8.ID_MOLD = t1.REF_MOULE
      WHERE REF_MOULE = :id
      ORDER BY t1.REF_MOULE DESC',
      array(
        ':id' => $id
      )
    );

    $mold = $this->fetch('Dictionary');

    // TODO Récupération via SAP des informations suivantes
    $mold['DATE_LAST_ENVELOPPE'] = '';
    $mold['GRADES'] = '';
    $mold['CAUSE_GRADE'] = '';

    return $mold;
  }

  /**
   * Fonction qui permet de récupérer et de calculer les codes
   * de production liées à un moule.
   * Paramètre :
   *  @param string : Identifiant unique du moule
   * @return array : Liste des moules avec leurs code et code liées
   */
  public function getMoldCodePossible($id)
  {
    $id = preg_replace('[a-zA-Z]', '', $id);
    $id = explode('-', $id)[0];

    $this->query(
      'SELECT distinct t1.REF_MOULE, t1.CODE_PROD_2
      FROM EXT_MOLDEQUIPMENTSLIST t1
      WHERE REF_MOULE LIKE :id',
      array(
        ':id' => '%' . $id . '%'
      )
    );

    $tmp = $this->fetchAll('Dictionary');

    $moldList = array();
    foreach ($tmp as $key => $mold) {
      $moldList[$mold['REF_MOULE']] = array('CODE' => $mold['CODE_PROD_2'], 'CODE_POSSIBLE' => '');

      foreach ($tmp as $key => $moldOther) {
        if ($moldOther['REF_MOULE'] != $mold['REF_MOULE'])
          $moldList[$mold['REF_MOULE']]['CODE_POSSIBLE'] .= $moldOther['CODE_PROD_2'] ?  $moldOther['CODE_PROD_2'] . ';' : '';
      }
    }

    return $moldList;
  }

  /**
   * Fonction qui permet de récupérer la liste des patternSize
   * disponible.
   * @return array : Liste des patternSize
   */
  public function getPatternSizeList()
  {
    $this->query(
      'SELECT DISTINCT t1.PATTERNSIZE
      FROM EXT_MOLDEQUIPMENTSLIST t1'
    );

    $patternSizeList = $this->fetchAll('Dictionary');

    return $patternSizeList;
  }

  /**
   * Fonction qui permet de récupérer la liste des moules
   * @return array : Liste des données de chaque moule
   */
  public function getMoldList()
  {
    $this->query(
      'SELECT distinct t1.REF_MOULE, t1.STATUS_MOULE, t1.COMMENTAIRE, t1.PATTERNSIZE, t1.REF_SPECS, t1.CODE_PROD_2,
      t1.DIM_CTN2, t1.GOUPILLE2, t1.REF_CTN, t1.REF_SEGMENT, t1.REF_FLANC_SUP, t1.REF_FLANC_INF,
      t1.TYPE_CTN2, t1.POSITION_ACTUEL_DATE, t1.GSWR_BOTTOM, t1.GSWR_TOP, t1.GTRD, t1.DRAWING,
      t2.S_DIAMETRE, t2.L_DIAMETRE, t2.JEUX_TOTAL_SECTEUR,
      t3.NB_CYCLE_CUISSON_LIFE, t3.NB_CYCLE_CUISSON_NOW, t3.DATE_GET_MOLD, t3.CLOGGED_HOLES,
      t4.LOCATION AS LOCATION_CHOICE,
      CASE WHEN t9.NO_PRESSE IS NULL THEN
          t4.LOCATION
      ELSE
          (t9.NO_PRESSE || CASE t9.ID_CAVITE WHEN \'-\' THEN \'\' ELSE t9.ID_CAVITE END)
      END AS LOCATION,
      t5.TYPE as STATUT, t5.COLOR as STATUT_COLOR,
      t6.TYPE as LOCATION_TYPE, t6.COLOR as LOCATION_TYPE_COLOR,
      t7.LAST_CHECK_DATE, t7.LAST_RESULT, t7.COMMENT_METRO,
      t8.START_DATE, t8.END_DATE, t8.COMMENT_EXCEPT,
      t9.NO_PRESSE, t9.ID_CAVITE
      FROM EXT_MOLDEQUIPMENTSLIST t1
      LEFT JOIN EXT_MOLDEQUIPMENTSPEC t2 ON t1.CODE_PROD_2 like \'%\' || t2.CODE_PROD_1 || \'%\'
      LEFT JOIN MOLD_TECH t3 ON t1.REF_MOULE = t3.ID_MOLD
      LEFT JOIN CAVITES t9 ON t9.NO_MOULE = t1.REF_MOULE
      LEFT JOIN MOLD_STATUS t4 ON t1.REF_MOULE = t4.ID_MOLD
      LEFT JOIN MOLD_STATUS_TYPE t5 ON t5.ID = t4.STATUS_TYPE
      LEFT JOIN MOLD_LOCATION_TYPE t6 ON t6.ID = t4.LOCATION_TYPE
      LEFT JOIN MOLD_METROLOGY t7 ON t7.ID_MOLD = t1.REF_MOULE
      LEFT JOIN MOLD_EXEMPTION t8 ON t8.ID_MOLD = t1.REF_MOULE
      GROUP BY  t1.REF_MOULE, t1.STATUS_MOULE, t1.COMMENTAIRE, t1.PATTERNSIZE, t1.CODE_PROD_2, t1.REF_SPECS,
      t1.DIM_CTN2, t1.GOUPILLE2, t1.REF_CTN, t1.REF_SEGMENT, t1.REF_FLANC_SUP, t1.REF_FLANC_INF,
      t1.TYPE_CTN2, t1.POSITION_ACTUEL_DATE, t1.GSWR_BOTTOM, t1.GSWR_TOP, t1.GTRD, t1.DRAWING,
      t2.S_DIAMETRE, t2.L_DIAMETRE, t2.JEUX_TOTAL_SECTEUR,
      t3.NB_CYCLE_CUISSON_LIFE, t3.NB_CYCLE_CUISSON_NOW, t3.DATE_GET_MOLD, t3.CLOGGED_HOLES,
      t4.LOCATION,
      CASE WHEN t9.NO_PRESSE IS NULL THEN
          t4.LOCATION
      ELSE
          (t9.NO_PRESSE || CASE t9.ID_CAVITE WHEN \'-\' THEN \'\' ELSE t9.ID_CAVITE END)
      END,
      t5.TYPE, t5.COLOR,
      t6.TYPE, t6.COLOR,
      t7.LAST_CHECK_DATE, t7.LAST_RESULT, t7.COMMENT_METRO,
      t8.START_DATE, t8.END_DATE, t8.COMMENT_EXCEPT,
      t9.NO_PRESSE, t9.ID_CAVITE
      ORDER BY t1.REF_MOULE DESC'
    );

    $moldList = $this->fetchAll('Dictionary');

    foreach ($moldList as $id => $mold) {
      // TODO Récupération via SAP des informations suivantes
      $moldList[$id]['DATE_LAST_ENVELOPPE'] = '';
      $moldList[$id]['GRADES'] = '';
      $moldList[$id]['CAUSE_GRADE'] = '';
    }

    return $moldList;
  }

  /**
   * Fonction qui permet de récupérer la liste des moules avec les filtres voulu
   * @return array : Liste des données de chaque moule
   */
  public function getMoldListByStatusList($statusList)
  {
    $statusListString = '';

    // Construction de la condition pour la requête avec les statuts voulu
    foreach ($statusList as $status => $default) {
      if (isset($statusList[$status]['default'])) {
        if (strlen($statusListString) == 0)
          $statusListString = $statusListString . 'STATUS_MOULE=\'' . $status . '\'';
        else
          $statusListString = $statusListString . ' or STATUS_MOULE=\'' . $status . '\'';
      }
    }

    $this->query(
      'SELECT distinct t1.REF_MOULE, t1.STATUS_MOULE, t1.COMMENTAIRE, t1.PATTERNSIZE, t1.REF_SPECS, t1.CODE_PROD_2,
      t1.DIM_CTN2, t1.GOUPILLE2, t1.REF_CTN, t1.REF_SEGMENT, t1.REF_FLANC_SUP, t1.REF_FLANC_INF,
      t1.TYPE_CTN2, t1.POSITION_ACTUEL_DATE, t1.GSWR_BOTTOM, t1.GSWR_TOP, t1.GTRD, t1.DRAWING,
      t2.S_DIAMETRE, t2.L_DIAMETRE, t2.JEUX_TOTAL_SECTEUR,
      t3.NB_CYCLE_CUISSON_LIFE, t3.NB_CYCLE_CUISSON_NOW, t3.DATE_GET_MOLD, t3.CLOGGED_HOLES,
      t4.LOCATION AS LOCATION_CHOICE,
      CASE WHEN t9.NO_PRESSE IS NULL THEN
          t4.LOCATION
      ELSE
          (t9.NO_PRESSE || CASE t9.ID_CAVITE WHEN \'-\' THEN \'\' ELSE t9.ID_CAVITE END)
      END AS LOCATION,
      t5.TYPE as STATUT, t5.COLOR as STATUT_COLOR,
      t6.TYPE as LOCATION_TYPE, t6.COLOR as LOCATION_TYPE_COLOR,
      t7.LAST_CHECK_DATE, t7.LAST_RESULT, t7.COMMENT_METRO,
      t8.START_DATE, t8.END_DATE, t8.COMMENT_EXCEPT,
      t9.NO_PRESSE, t9.ID_CAVITE
      FROM EXT_MOLDEQUIPMENTSLIST t1
      LEFT JOIN EXT_MOLDEQUIPMENTSPEC t2 ON t1.CODE_PROD_2 like \'%\' || t2.CODE_PROD_1 || \'%\'
      LEFT JOIN MOLD_TECH t3 ON t1.REF_MOULE = t3.ID_MOLD
      LEFT JOIN CAVITES t9 ON t9.NO_MOULE = t1.REF_MOULE
      LEFT JOIN MOLD_STATUS t4 ON t1.REF_MOULE = t4.ID_MOLD
      LEFT JOIN MOLD_STATUS_TYPE t5 ON t5.ID = t4.STATUS_TYPE
      LEFT JOIN MOLD_LOCATION_TYPE t6 ON t6.ID = t4.LOCATION_TYPE
      LEFT JOIN MOLD_METROLOGY t7 ON t7.ID_MOLD = t1.REF_MOULE
      LEFT JOIN MOLD_EXEMPTION t8 ON t8.ID_MOLD = t1.REF_MOULE
      WHERE ' . $statusListString . ' 
      GROUP BY  t1.REF_MOULE, t1.STATUS_MOULE, t1.COMMENTAIRE, t1.PATTERNSIZE, t1.CODE_PROD_2, t1.REF_SPECS,
      t1.DIM_CTN2, t1.GOUPILLE2, t1.REF_CTN, t1.REF_SEGMENT, t1.REF_FLANC_SUP, t1.REF_FLANC_INF,
      t1.TYPE_CTN2, t1.POSITION_ACTUEL_DATE, t1.GSWR_BOTTOM, t1.GSWR_TOP, t1.GTRD, t1.DRAWING,
      t2.S_DIAMETRE, t2.L_DIAMETRE, t2.JEUX_TOTAL_SECTEUR,
      t3.NB_CYCLE_CUISSON_LIFE, t3.NB_CYCLE_CUISSON_NOW, t3.DATE_GET_MOLD, t3.CLOGGED_HOLES,
      t4.LOCATION,
      CASE WHEN t9.NO_PRESSE IS NULL THEN
          t4.LOCATION
      ELSE
          (t9.NO_PRESSE || CASE t9.ID_CAVITE WHEN \'-\' THEN \'\' ELSE t9.ID_CAVITE END)
      END,
      t5.TYPE, t5.COLOR,
      t6.TYPE, t6.COLOR,
      t7.LAST_CHECK_DATE, t7.LAST_RESULT, t7.COMMENT_METRO,
      t8.START_DATE, t8.END_DATE, t8.COMMENT_EXCEPT,
      t9.NO_PRESSE, t9.ID_CAVITE
      ORDER BY t1.REF_MOULE DESC'
    );

    $moldList = $this->fetchAll('Dictionary');

    foreach ($moldList as $id => $mold) {
      // TODO Récupération via SAP des informations suivantes
      $moldList[$id]['DATE_LAST_ENVELOPPE'] = '';
      $moldList[$id]['GRADES'] = '';
      $moldList[$id]['CAUSE_GRADE'] = '';
    }

    return $moldList;
  }

  /**
   * Fonction qui permet de récupérer la liste des statuts disponibles
   * @return array : Liste des statuts disponibles
   */
  public function getStatusList()
  {
    $this->query(
      'SELECT TYPE AS STATUS_MOULE, COLOR
      FROM MOLD_STATUS_TYPE'
    );

    $statusList = $this->fetchAll('Dictionary');

    return $statusList;
  }

  /**
   * Fonction qui permet de récupérer la liste des statuts disponibles
   * ordonnés dans un dictionnaire
   * @return array : Liste des statuts disponibles et ordonnés
   */
  public function getStatusListDic()
  {
    $this->query(
      'SELECT TYPE, COLOR
      FROM MOLD_STATUS_TYPE'
    );

    $tmp = $this->fetchAll('Dictionary');

    // Création de la liste des statuts
    $statusList = array();

    foreach ($tmp as $id => $status)
      $statusList[$status['TYPE']] = $status['COLOR'];

    return $statusList;
  }

  /**
   * Fonction qui permet de récupérer la liste des localisations disponibles
   * @return array : Liste des localisations disponibles
   */
  public function getLocationTypeList()
  {
    $this->query(
      'SELECT TYPE AS LOCATION_TYPE, COLOR
      FROM MOLD_LOCATION_TYPE'
    );

    $locationTypeList = $this->fetchAll('Dictionary');

    return $locationTypeList;
  }

  /**
   * Fonction qui permet de récupérer la liste des localisations disponibles
   * ordonnés dans un dictionnaire
   * @return array : Liste des localisations disponibles et ordonnés
   */
  public function getLocationTypeListDic()
  {
    $this->query(
      'SELECT TYPE, COLOR
      FROM MOLD_LOCATION_TYPE'
    );

    $tmp = $this->fetchAll('Dictionary');

    // Création de la liste des statuts
    $locationTypeList = array();

    foreach ($tmp as $id => $location)
      $locationTypeList[$location['TYPE']] = $location['COLOR'];

    return $locationTypeList;
  }

  /**
   * Fonction qui permet de récupérer le nombre de moule pour chaque statuts
   * @return array : Liste du nombre de moule par statuts
   */
  public function getNbMoldByStatus()
  {
    $this->query(
      'SELECT t2.TYPE, t2.COLOR, COUNT(*) AS NB
      FROM MOLD_STATUS t1, MOLD_STATUS_TYPE t2
      WHERE t1.STATUS_TYPE = t2.ID
      GROUP BY t2.TYPE, t2.COLOR'
    );

    $nbMoldByStatusList = $this->fetchAll('Dictionary');

    return $nbMoldByStatusList;
  }

  /**
   * Fonction qui permet de récupérer le nombre de moule pour chaque localisation
   * @return array : Liste du nombre de moule par localisation
   */
  public function getNbMoldByLocation()
  {
    $this->query(
      'SELECT t2.TYPE, t2.COLOR, COUNT(*) AS NB
      FROM MOLD_STATUS t1, MOLD_LOCATION_TYPE t2
      WHERE t1.LOCATION_TYPE = t2.ID
      GROUP BY t2.TYPE, t2.COLOR'
    );

    $nbMoldByLocationList = $this->fetchAll('Dictionary');

    return $nbMoldByLocationList;
  }

  /**
   * Fonction qui permet d'ajouter un moule
   * Paramètres :
   *  @param array : Données du moule
   * @return array : Ajout dans la base de données
   */
  public function addMold($mold)
  {
    // Ajout du moule
    // TODO : Ajouter REF_SPECS DANS UPDATE ET
    $this->query(
      'INSERT INTO EXT_MOLDEQUIPMENTSLIST (
        STATUS_MOULE,
        REF_MOULE,
        REF_CTN,
        REF_SPECS,
        REF_SEGMENT,
        REF_FLANC_SUP,
        REF_FLANC_INF,
        PATTERNSIZE,
        CODE_PROD_2,
        TYPE_CTN2,
        DIM_CTN2,
        GOUPILLE2,
        POSITION_ACTUEL_DATE,
        COMMENTAIRE,
        GSWR_BOTTOM,
        GSWR_TOP,
        GTRD,
        DRAWING
      )
      VALUES (
        :status_moule,
        :ref_moule,
        :ref_ctn,
        :ref_specs,
        :ref_segment,
        :ref_flanc_sup,
        :ref_flanc_inf,
        :patternsize,
        :code_prod_2,
        :type_ctn2,
        :dim_ctn2,
        :goupille2,
        TO_DATE(:position_actuel_date, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        :commentaire,
        :gswr_bottom,
        :gswr_top,
        :gtrd,
        :drawing
      )',
      array(
        ':status_moule' => $mold['STATUT'],
        ':ref_moule' => $mold['REF_MOULE'],
        ':ref_ctn' => $mold['REF_CTN'],
        ':ref_specs' => $mold['REF_SPECS'],
        ':ref_segment' => $mold['REF_SEGMENT'],
        ':ref_flanc_sup' => $mold['REF_FLANC_SUP'],
        ':ref_flanc_inf' => $mold['REF_FLANC_INF'],
        ':patternsize' => $mold['PATTERNSIZE'],
        ':code_prod_2' => $mold['CODE_PROD_2'],
        ':type_ctn2' => $mold['TYPE_CTN2'],
        ':dim_ctn2' => $mold['DIM_CTN2'],
        ':goupille2' => $mold['GOUPILLE2'],
        ':position_actuel_date' => $mold['POSITION_ACTUEL_DATE'],
        ':commentaire' => $mold['COMMENTAIRE'],
        ':gswr_bottom' => $mold['GSWR_BOTTOM'],
        ':gswr_top' => $mold['GSWR_TOP'],
        ':gtrd' => $mold['GTRD'],
        ':drawing' => $mold['DRAWING']
      )
    );

    // Ajout des spécifications téchnologiques du moule
    $this->query(
      'INSERT INTO MOLD_TECH (
        ID_MOLD,
        NB_CYCLE_CUISSON_LIFE,
        NB_CYCLE_CUISSON_NOW,
        CLOGGED_HOLES,
        DATE_GET_MOLD)
      VALUES (
        :moldID,
        :nbCycleCuissonLife,
        :nbCycleCuissonNow,
        :cloggedHoles,
        TO_DATE(:dateGetMold, \'YYYY-MM-DD"T"HH24:MI:SS\'))',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':nbCycleCuissonLife' => $mold['NB_CYCLE_CUISSON_LIFE'],
        ':nbCycleCuissonNow' => $mold['NB_CYCLE_CUISSON_NOW'],
        ':cloggedHoles' => $mold['CLOGGED_HOLES'],
        ':dateGetMold' => $mold['DATE_GET_MOLD']
      )
    );

    // Ajout des informations pour le statut du moule
    $this->query(
      'INSERT INTO MOLD_STATUS (
        ID_MOLD,
        STATUS_TYPE,
        LOCATION_TYPE,
        LOCATION)
      VALUES (
        :moldID,
        (SELECT ID FROM MOLD_STATUS_TYPE WHERE TYPE = :statusType),
        (SELECT ID FROM MOLD_LOCATION_TYPE WHERE TYPE = :locationType),
        :location)',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':statusType' => $mold['STATUT'],
        ':locationType' => $mold['LOCATION_TYPE'],
        ':location' => $mold['LOCATION']
      )
    );

    // Ajout des informations pour la métrologie
    $this->query(
      'INSERT INTO MOLD_METROLOGY (
        ID_MOLD,
        LAST_CHECK_DATE,
        LAST_RESULT,
        COMMENT_METRO)
      VALUES (
        :moldID,
        TO_DATE(:lastCheckDate, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        :lastResult,
        :commentMetro)',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':lastCheckDate' => $mold['LAST_CHECK_DATE'],
        ':lastResult' => $mold['LAST_RESULT'],
        ':commentMetro' => $mold['COMMENT_METRO']
      )
    );

    // Ajout des informations pour les dérogations
    $this->query(
      'INSERT INTO MOLD_EXEMPTION (
        ID_MOLD,
        START_DATE,
        END_DATE,
        COMMENT_EXCEPT)
      VALUES (
        :moldID,
        TO_DATE(:startDate, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        TO_DATE(:endDate, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        :commentExcept)',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':startDate' => $mold['START_DATE'],
        ':endDate' => $mold['END_DATE'],
        ':commentExcept' => $mold['COMMENT_EXCEPT']
      )
    );
  }

  /**
   * Fonction qui permet d'éditer un moule
   * Paramètres :
   *  @param array : Nouvelles données du moule
   * @return array : Edition dans la base de données
   */
  public function editMold($mold)
  {
    // Ajout du moule
    $this->query(
      'UPDATE EXT_MOLDEQUIPMENTSLIST
      SET
        STATUS_MOULE=:status_moule,
        REF_CTN=:ref_ctn,
        REF_SPECS=:ref_specs,
        REF_SEGMENT=:ref_segment,
        REF_FLANC_SUP=:ref_flanc_sup,
        REF_FLANC_INF=:ref_flanc_inf,
        PATTERNSIZE=:patternsize,
        CODE_PROD_2=:code_prod_2,
        TYPE_CTN2=:type_ctn2,
        DIM_CTN2=:dim_ctn2,
        GOUPILLE2=:goupille2,
        POSITION_ACTUEL_DATE=TO_DATE(:position_actuel_date, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        COMMENTAIRE=:commentaire,
        GSWR_BOTTOM=:gswr_bottom,
        GSWR_TOP=:gswr_top,
        GTRD=:gtrd,
        DRAWING=:drawing
      WHERE REF_MOULE=:ref_moule',
      array(
        ':status_moule' => $mold['STATUT'],
        ':ref_moule' => $mold['REF_MOULE'],
        ':ref_ctn' => $mold['REF_CTN'],
        ':ref_specs' => $mold['REF_SPECS'],
        ':ref_segment' => $mold['REF_SEGMENT'],
        ':ref_flanc_sup' => $mold['REF_FLANC_SUP'],
        ':ref_flanc_inf' => $mold['REF_FLANC_INF'],
        ':patternsize' => $mold['PATTERNSIZE'],
        ':code_prod_2' => $mold['CODE_PROD_2'],
        ':type_ctn2' => $mold['TYPE_CTN2'],
        ':dim_ctn2' => $mold['DIM_CTN2'],
        ':goupille2' => $mold['GOUPILLE2'],
        ':position_actuel_date' => $mold['POSITION_ACTUEL_DATE'],
        ':commentaire' => $mold['COMMENTAIRE'],
        ':gswr_bottom' => $mold['GSWR_BOTTOM'],
        ':gswr_top' => $mold['GSWR_TOP'],
        ':gtrd' => $mold['GTRD'],
        ':drawing' => $mold['DRAWING']
      )
    );

    // Ajout des spécifications téchnologiques du moule
    $this->query(
      'UPDATE MOLD_TECH
      SET
        NB_CYCLE_CUISSON_LIFE=:nbCycleCuissonLife,
        NB_CYCLE_CUISSON_NOW=:nbCycleCuissonNow,
        CLOGGED_HOLES=:cloggedHoles,
        DATE_GET_MOLD=TO_DATE(:dateGetMold, \'YYYY-MM-DD"T"HH24:MI:SS\')
      WHERE ID_MOLD=:moldID',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':nbCycleCuissonLife' => $mold['NB_CYCLE_CUISSON_LIFE'],
        ':nbCycleCuissonNow' => $mold['NB_CYCLE_CUISSON_NOW'],
        ':cloggedHoles' => $mold['CLOGGED_HOLES'],
        ':dateGetMold' => $mold['DATE_GET_MOLD']
      )
    );

    // Ajout des informations pour le statut du moule
    $this->query(
      'UPDATE MOLD_STATUS
      SET
        STATUS_TYPE=(SELECT ID FROM MOLD_STATUS_TYPE WHERE TYPE = :statusType),
        LOCATION_TYPE=(SELECT ID FROM MOLD_LOCATION_TYPE WHERE TYPE = :locationType),
        LOCATION=:location
      WHERE ID_MOLD=:moldID',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':statusType' => $mold['STATUT'],
        ':locationType' => $mold['LOCATION_TYPE'],
        ':location' => $mold['LOCATION']
      )
    );

    // Ajout des informations pour la métrologie
    $this->query(
      'UPDATE MOLD_METROLOGY
      SET
        LAST_CHECK_DATE=TO_DATE(:lastCheckDate, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        LAST_RESULT=:lastResult,
        COMMENT_METRO=:commentMetro
      WHERE ID_MOLD=:moldID',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':lastCheckDate' => $mold['LAST_CHECK_DATE'],
        ':lastResult' => $mold['LAST_RESULT'],
        ':commentMetro' => $mold['COMMENT_METRO']
      )
    );

    // Ajout des informations pour les dérogations
    $this->query(
      'UPDATE MOLD_EXEMPTION
      SET
        START_DATE=TO_DATE(:startDate, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        END_DATE=TO_DATE(:endDate, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        COMMENT_EXCEPT=:commentExcept
      WHERE ID_MOLD=:moldID',
      array(
        ':moldID' => $mold['REF_MOULE'],
        ':startDate' => $mold['START_DATE'],
        ':endDate' => $mold['END_DATE'],
        ':commentExcept' => $mold['COMMENT_EXCEPT']
      )
    );
  }

  /**
   * Fonction qui permet de supprimer un ou plusieurs moule
   * Paramètres :
   *  @param string : Identifiant du moule
   * @return array : Suppression unique dans la base de données
   */
  public function removeMold($id)
  {
    // Suppression des données du moule
    $this->query(
      'DELETE FROM EXT_MOLDEQUIPMENTSLIST WHERE REF_MOULE=:id',
      array(
        ':id' => $id
      )
    );

    // Suppression des informations supplémentaires techniques du moule
    $this->query(
      'DELETE FROM MOLD_TECH WHERE ID_MOLD=:id',
      array(
        ':id' => $id
      )
    );

    // Suppression des informations sur le statuts et la localisation du moule
    $this->query(
      'DELETE FROM MOLD_STATUS WHERE ID_MOLD=:id',
      array(
        ':id' => $id
      )
    );

    // Suppression des informations sur la métrologie pour le moule
    $this->query(
      'DELETE FROM MOLD_METROLOGY WHERE ID_MOLD=:id',
      array(
        ':id' => $id
      )
    );

    // Suppression des informations sur la dérogation du moule
    $this->query(
      'DELETE FROM MOLD_EXEMPTION WHERE ID_MOLD=:id',
      array(
        ':id' => $id
      )
    );
  }
}

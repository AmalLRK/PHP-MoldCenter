<?php

/**
 * Classe qui contient le modèle pour les codes
 * - Récupération d'un code
 * - Récupération de la liste de tous les codes
 * - Récupération de la liste des statuts Disponible
 * - Récupération de la liste des statuts Disponible sous forme de dictionnaire
 * - Récupération du nombre de code par statuts
 * - Ajout d'un code
 * - Edition d'un code
 * - Suppression d'un code
 */
class codeModel extends Model
{
  /**
   * Fonction qui permet de récupérer un code
   * Paramètre :
   *  @param string : Identifiant du code voulu
   * @return array : Liste des données du code
   */
  public function getCode($id)
  {
    $this->query(
      'SELECT t1.CODE_PROD_1, t1.STATUS, t1.S_DIA_RELIEVE, t1.SEUIL_CRYO_CODE,
      t1.N_SPEC_EQUIP, t1.CODE_PROFIL, t1.GOUPILLE, t1.BAGO_BMR, t1.BAGO_TMR,
      t1.BAGO_ADAPTER, t1.BAGO_BLADDER, t1.MHI_BMR, t1.MHI_BLADDER, t1.T_MINI,
      t1.T_MAXI, t1.S_DIAMETRE, t1.L_DIAMETRE, t1.JEUX_TOTAL_SECTEUR, t1.REF_SPEC,
      t2.SALES_CODE, t2.BAGUES_TYPE, t2.SMARTEAM, t2.TYPE_PRESSE,
      t3.PCI
      FROM EXT_MOLDEQUIPMENTSPEC t1
      LEFT JOIN CODE_TECH t2 ON t1.CODE_PROD_1 = t2.ID_CODE
      LEFT JOIN CURES t3 ON t1.CODE_PROD_1 = t3.FACT_CODE
      WHERE t1.CODE_PROD_1 = :id',
      array(
        ':id' => $id
      )
    );

    $code = $this->fetch('Dictionary');

    return $code;
  }

  /**
   * Fonction qui permet de récupérer la liste des codes
   * @return array : Liste des données de chaque code
   */
  public function getCodeList()
  {
    $this->query(
      'SELECT t1.CODE_PROD_1, t1.STATUS, t1.S_DIA_RELIEVE, t1.SEUIL_CRYO_CODE,
      t1.N_SPEC_EQUIP, t1.CODE_PROFIL, t1.GOUPILLE, t1.BAGO_BMR, t1.BAGO_TMR,
      t1.BAGO_ADAPTER, t1.BAGO_BLADDER, t1.MHI_BMR, t1.MHI_BLADDER, t1.T_MINI,
      t1.T_MAXI, t1.S_DIAMETRE, t1.L_DIAMETRE, t1.JEUX_TOTAL_SECTEUR, t1.REF_SPEC,
      t2.SALES_CODE, t2.BAGUES_TYPE, t2.SMARTEAM, t2.TYPE_PRESSE,
      t3.PCI
      FROM EXT_MOLDEQUIPMENTSPEC t1
      LEFT JOIN CODE_TECH t2 ON t1.CODE_PROD_1 = t2.ID_CODE
      LEFT JOIN CURES t3 ON t1.CODE_PROD_1 = t3.FACT_CODE
      GROUP BY t1.CODE_PROD_1, t1.STATUS, t1.S_DIA_RELIEVE, t1.SEUIL_CRYO_CODE,
      t1.N_SPEC_EQUIP, t1.CODE_PROFIL, t1.GOUPILLE, t1.BAGO_BMR, t1.BAGO_TMR,
      t1.BAGO_ADAPTER, t1.BAGO_BLADDER, t1.MHI_BMR, t1.MHI_BLADDER, t1.T_MINI,
      t1.T_MAXI, t1.S_DIAMETRE, t1.L_DIAMETRE, t1.JEUX_TOTAL_SECTEUR, t1.REF_SPEC,
      t2.SALES_CODE, t2.BAGUES_TYPE, t2.SMARTEAM, t2.TYPE_PRESSE,
      t3.PCI
      ORDER BY t1.CODE_PROD_1'
    );

    $codeList = $this->fetch('Dictionary');
    var_dump($codeList);
    return $codeList;
  }

  /**
   * Fonction qui permet de récupérer la liste des codes avec les filtres voulu
   * @return array : Liste des données de chaque code
   */
  public function getCodeListByStatusList($statusList)
  {
    $statusListString = '';

    // Construction de la condition pour la requête avec les statuts voulu
    foreach ($statusList as $status => $default) {
      if (isset($statusList[$status]['default'])) {
        if (strlen($statusListString) == 0)
          $statusListString = $statusListString . 'STATUS=\'' . $status . '\'';
        else
          $statusListString = $statusListString . ' or STATUS=\'' . $status . '\'';
      }
    }

    $this->query(
      'SELECT t1.CODE_PROD_1, t1.STATUS, t1.S_DIA_RELIEVE, t1.SEUIL_CRYO_CODE,
      t1.N_SPEC_EQUIP, t1.CODE_PROFIL, t1.GOUPILLE, t1.BAGO_BMR, t1.BAGO_TMR,
      t1.BAGO_ADAPTER, t1.BAGO_BLADDER, t1.MHI_BMR, t1.MHI_BLADDER, t1.T_MINI,
      t1.T_MAXI, t1.S_DIAMETRE, t1.L_DIAMETRE, t1.JEUX_TOTAL_SECTEUR, t1.REF_SPEC,
      t2.SALES_CODE, t2.BAGUES_TYPE, t2.SMARTEAM, t2.TYPE_PRESSE,
      t3.PCI
      FROM EXT_MOLDEQUIPMENTSPEC t1
      LEFT JOIN CODE_TECH t2 ON t1.CODE_PROD_1 = t2.ID_CODE
      LEFT JOIN CURES t3 ON t1.CODE_PROD_1 = t3.FACT_CODE
      
      WHERE ' . $statusListString . '
      GROUP BY t1.CODE_PROD_1, t1.STATUS, t1.S_DIA_RELIEVE, t1.SEUIL_CRYO_CODE,
      t1.N_SPEC_EQUIP, t1.CODE_PROFIL, t1.GOUPILLE, t1.BAGO_BMR, t1.BAGO_TMR,
      t1.BAGO_ADAPTER, t1.BAGO_BLADDER, t1.MHI_BMR, t1.MHI_BLADDER, t1.T_MINI,
      t1.T_MAXI, t1.S_DIAMETRE, t1.L_DIAMETRE, t1.JEUX_TOTAL_SECTEUR, t1.REF_SPEC,
      t2.SALES_CODE, t2.BAGUES_TYPE, t2.SMARTEAM, t2.TYPE_PRESSE,
      t3.PCI
      ORDER BY t1.CODE_PROD_1'
    );

    $codeList = $this->fetchAll('Dictionary');

    return $codeList;
  }

  /**
   * Fonction qui permet de récupérer la liste des statuts disponibles
   * @return array : Liste des statuts disponibles
   */
  public function getStatusList()
  {
    $statusList = array(
      0 => array('TYPE' => 'activeName', 'COLOR' => '78e08f'),
      1 => array('TYPE' => 'inactiveName', 'COLOR' => 'eb2f06'),
      2 => array('TYPE' => 'destroyName', 'COLOR' => 'b71540')
    );

    return $statusList;
  }

  /**
   * Fonction qui permet de récupérer la liste des statuts disponibles
   * ordonnés dans un dictionnaire
   * @return array : Liste des statuts disponibles et ordonnés
   */
  public function getStatusListDic()
  {
    $statusList = array();

    $statusList['activeName'] = '78e08f';
    $statusList['inactiveName'] = 'eb2f06';
    $statusList['destroyName'] = 'b71540';

    return $statusList;
  }

  /**
   * Fonction qui permet de récupérer le nombre de code pour chaque statuts
   * @return array : Liste du nombre de code par statuts
   */
  public function getNbCodeByStatus()
  {
    $this->query(
      'SELECT STATUS as TYPE, COUNT(*) AS NB
      FROM EXT_MOLDEQUIPMENTSPEC
      GROUP BY STATUS'
    );

    $nbCodeByStatusList = $this->fetchAll('Dictionary');

    $tmpStatusList = $this->getStatusListDic();

    foreach ($nbCodeByStatusList as $id => $data)
      $nbCodeByStatusList[$id]['COLOR'] = $tmpStatusList[$data['TYPE']];

    return $nbCodeByStatusList;
  }

  /**
   * Fonction qui permet d'ajouter un code
   * Paramètres :
   *  @param array : Données du code
   * @return array : Ajout dans la base de données
   */
  public function addCode($code)
  {
    $this->query(
      'SELECT count(*) as nb FROM EXT_MOLDEQUIPMENTSPEC WHERE CODE_PROD_1 = :code_prod_1',
      array(
        ':code_prod_1' => $code['CODE_PROD_1']
      )
    );
    $nb = $this->fetch()->NB;
    // var_dump($nb);
    if($nb == 0) {
      // Ajout du code
      $this->query(
        'INSERT INTO EXT_MOLDEQUIPMENTSPEC (
          CODE_PROD_1,
          STATUS,
          S_DIA_RELIEVE,
          SEUIL_CRYO_CODE,
          N_SPEC_EQUIP,
          CODE_PROFIL,
          GOUPILLE,
          BAGO_BMR,
          BAGO_TMR,
          BAGO_ADAPTER,
          BAGO_BLADDER,
          MHI_BMR,
          MHI_BLADDER,
          T_MINI,
          T_MAXI,
          S_DIAMETRE,
          L_DIAMETRE,
          JEUX_TOTAL_SECTEUR,
          REF_SPEC
        )
        VALUES (
          :code_prod_1,
          :status,
          :s_dia_relieve,
          TO_NUMBER(:seuil_cryo_code),
          :n_spec_equip,
          :code_profil,
          :goupille,
          :bago_bmr,
          :bago_tmr,
          :bago_adapter,
          :bago_bladder,
          :mhi_bmr,
          :mhi_bladder,
          :t_mini,
          :t_maxi,
          TO_NUMBER(:s_diametre),
          TO_NUMBER(:l_diametre),
          TO_NUMBER(:jeux_total_secteur),
          :ref_spec
        )',
        array(
          ':code_prod_1' => $code['CODE_PROD_1'],
          ':status' => $code['STATUS'],
          ':s_dia_relieve' => $code['S_DIA_RELIEVE'],
          ':seuil_cryo_code' => $code['SEUIL_CRYO_CODE'],
          ':n_spec_equip' => $code['N_SPEC_EQUIP'],
          ':code_profil' => $code['CODE_PROFIL'],
          ':goupille' => $code['GOUPILLE'],
          ':bago_bmr' => $code['BAGO_BMR'],
          ':bago_tmr' => $code['BAGO_TMR'],
          ':bago_adapter' => $code['BAGO_ADAPTER'],
          ':bago_bladder' => $code['BAGO_BLADDER'],
          ':mhi_bmr' => $code['MHI_BMR'],
          ':mhi_bladder' => $code['MHI_BLADDER'],
          ':t_mini' => $code['T_MINI'],
          ':t_maxi' => $code['T_MAXI'],
          ':s_diametre' => $code['S_DIAMETRE'],
          ':l_diametre' => $code['L_DIAMETRE'],
          ':jeux_total_secteur' => $code['JEUX_TOTAL_SECTEUR'],
          ':ref_spec' => $code['REF_SPEC']
        )
      );

      $Spec = str_replace('V1', '', str_replace('R1', '', $code['REF_SPEC']));
      $this->updateMoldCodeProd($Spec);

      // Ajout des spécifications téchnologiques du code
      $this->query(
        'INSERT INTO CODE_TECH (
          ID_CODE,
          SALES_CODE,
          BAGUES_TYPE,
          SMARTEAM,
          TYPE_PRESSE
        )
        VALUES (
          :codeID,
          :sales_code,
          :bagues_type,
          TO_DATE(:smarteam, \'YYYY-MM-DD"T"HH24:MI:SS\'),
          :type_presse
        )',
        array(
          ':codeID' => $code['CODE_PROD_1'],
          ':sales_code' => $code['SALES_CODE'],
          ':bagues_type' => $code['BAGUES_TYPE'],
          ':smarteam' => $code['SMARTEAM'],
          ':type_presse' => $code['TYPE_PRESSE']
        )
      );

      // Ajout des spécifications téchnologiques du code
      $this->query(
        'INSERT INTO CURES (
          FACT_CODE,
          PCI,
          TS_CODE
        )
        VALUES (
          :codeID,
          :pci,
          :ts_code
        )',
        array(
          ':codeID' => $code['CODE_PROD_1'],
          ':pci' => $code['PCI'],
          ':ts_code' => $code['CODE_PROD_1'] . '  A0'
        )
      );
    }
  }

  /**
   * Fonction qui permet d'avoir les possibilité de famille de code
   * @param : $type si moule 'V1' si code 'R1'
   * @return : Liste des Réference spécifique (famille)
   */
  public function getRefSpecsPossible($type = '')
  {
    $this->query('SELECT DISTINCT SUBSTR(REF_SPEC, 1, 4) || \'' . $type . '\' AS REF_SPEC FROM EXT_MOLDEQUIPMENTSPEC WHERE STATUS = \'activeName\'');
    return $this->fetchAll('Dictionary');
  }

  /**
   * Fonction qui retourne les code de production d'une famille séparer par des points virgule
   * @param $spec la référence spécifique (famille)
   */
  public function getCodesFromSpec($spec)
  {
    //Convertion des specs
    $spec = str_replace('V1', '', str_replace('R1', '', $spec));
    $this->query(
      'SELECT distinct CODE_PROD_1 FROM EXT_MOLDEQUIPMENTSPEC WHERE STATUS = \'activeName\' AND REF_SPEC like \'' . $spec . '%\' ORDER BY CODE_PROD_1'
    );

    $res = '';
    $codes = $this->fetchAll('Dictionary');
    foreach ($codes as $code) {
      $res .= $code['CODE_PROD_1'] . ';';
    }

    return $res;
  }

  /**
   * Met à jour les références
   * @param $spec la référence spécifique (famille)
   */
  public function updateMoldCodeProd($spec) {
    $spec = str_replace('V1', '', str_replace('R1', '', $spec));

    $this->query(
      'UPDATE EXT_MOLDEQUIPMENTSLIST SET CODE_PROD_2=:code_prod_2 WHERE REF_SPECS like \'' . $spec . '%\'',
      array(
        ':code_prod_2' => $this->getCodesFromSpec($spec)
      )
    );
  }

  /**
   * Fonction qui permet d'éditer un code
   * Paramètres :
   *  @param array : Nouvelles données du code
   * @return array : Edition dans la base de données
   */
  public function editCode($code)
  {
    $this->query(
      'SELECT distinct REF_SPEC FROM EXT_MOLDEQUIPMENTSPEC WHERE CODE_PROD_1=:code_prod_1',
      array(
        ':code_prod_1' => $code['CODE_PROD_1']
      )
    );

    $oldSpec = $this->fetch()->REF_SPEC;

    // Ajout du code
    $this->query(
      'UPDATE EXT_MOLDEQUIPMENTSPEC
      SET
        STATUS=:status,
        S_DIA_RELIEVE=:s_dia_relieve,
        SEUIL_CRYO_CODE=TO_NUMBER(:seuil_cryo_code),
        N_SPEC_EQUIP=:n_spec_equip,
        CODE_PROFIL=:code_profil,
        GOUPILLE=:goupille,
        BAGO_BMR=:bago_bmr,
        BAGO_TMR=:bago_tmr,
        BAGO_ADAPTER=:bago_adapter,
        BAGO_BLADDER=:bago_bladder,
        MHI_BMR=:mhi_bmr,
        MHI_BLADDER=:mhi_bladder,
        T_MINI=:t_mini,
        T_MAXI=:t_maxi,
        S_DIAMETRE=TO_NUMBER(:s_diametre),
        L_DIAMETRE=TO_NUMBER(:l_diametre),
        JEUX_TOTAL_SECTEUR=TO_NUMBER(:jeux_total_secteur),
        REF_SPEC=:ref_spec
      WHERE CODE_PROD_1=:code_prod_1',
      array(
        ':code_prod_1' => $code['CODE_PROD_1'],
        ':status' => $code['STATUS'],
        ':s_dia_relieve' => $code['S_DIA_RELIEVE'],
        ':seuil_cryo_code' => $code['SEUIL_CRYO_CODE'],
        ':n_spec_equip' => $code['N_SPEC_EQUIP'],
        ':code_profil' => $code['CODE_PROFIL'],
        ':goupille' => $code['GOUPILLE'],
        ':bago_bmr' => $code['BAGO_BMR'],
        ':bago_tmr' => $code['BAGO_TMR'],
        ':bago_adapter' => $code['BAGO_ADAPTER'],
        ':bago_bladder' => $code['BAGO_BLADDER'],
        ':mhi_bmr' => $code['MHI_BMR'],
        ':mhi_bladder' => $code['MHI_BLADDER'],
        ':t_mini' => $code['T_MINI'],
        ':t_maxi' => $code['T_MAXI'],
        ':s_diametre' => $code['S_DIAMETRE'],
        ':l_diametre' => $code['L_DIAMETRE'],
        ':jeux_total_secteur' => $code['JEUX_TOTAL_SECTEUR'],
        ':ref_spec' => $code['REF_SPEC']
      )
    );

    if($oldSpec != $code['REF_SPEC']) {
      $this->updateMoldCodeProd($oldSpec);
    }
    $this->updateMoldCodeProd($code['REF_SPEC']);

    // Ajout des spécifications téchnologiques du code
    $this->query(
      'UPDATE CODE_TECH
      SET
        SALES_CODE=:sales_code,
        BAGUES_TYPE=:bagues_type,
        SMARTEAM=TO_DATE(:smarteam, \'YYYY-MM-DD"T"HH24:MI:SS\'),
        TYPE_PRESSE=:type_presse
      WHERE ID_CODE=:codeID',
      array(
        ':codeID' => $code['CODE_PROD_1'],
        ':sales_code' => $code['SALES_CODE'],
        ':bagues_type' => $code['BAGUES_TYPE'],
        ':smarteam' => $code['SMARTEAM'],
        ':type_presse' => $code['TYPE_PRESSE']
      )
    );

    // Ajout des spécifications téchnologiques du code
    $this->query(
      'UPDATE CURES
      SET
        PCI=:pci
      WHERE FACT_CODE=:codeID',
      array(
        ':codeID' => $code['CODE_PROD_1'],
        ':pci' => $code['PCI']
      )
    );
  }

  /**
   * Fonction qui permet de supprimer un code
   * Paramètres :
   *  @param string : Identifiant du code
   * @return array : Suppression unique dans la base de données
   */
  public function removeCode($id)
  {
    $this->query(
      'SELECT distinct REF_SPEC FROM EXT_MOLDEQUIPMENTSPEC WHERE CODE_PROD_1=:code_prod_1',
      array(
        ':code_prod_1' => $id
      )
    );

    $oldSpec = str_replace('V1', '', str_replace('R1', '', $this->fetch()->REF_SPEC));

    // Suppression des données du code
    $this->query(
      'DELETE FROM EXT_MOLDEQUIPMENTSPEC WHERE CODE_PROD_1=:id',
      array(
        ':id' => $id
      )
    );

    $this->updateMoldCodeProd($oldSpec);

    // Suppression des informations supplémentaires techniques du code
    $this->query(
      'DELETE FROM CODE_TECH WHERE ID_CODE=:id',
      array(
        ':id' => $id
      )
    );

    // Suppression des informations supplémentaires techniques du code
    $this->query(
      'DELETE FROM CURES WHERE FACT_CODE=:id',
      array(
        ':id' => $id
      )
    );
  }
}

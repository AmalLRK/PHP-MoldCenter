<?php

/**
 * Classe qui contient le modèle pour l'envoi des données Excel en base de données
 * - Envoi des données dans Mold Equipment List
 * - Envoi des données dans Mold Equipment Spec
 */
class importModel extends Model
{
  /**
   * Fonction qui permet de convertir une date au bon format.
   * Paramètres :
   *  @param string : Date à convertir.
   *  @param boolean : Affichage ou non.
   * @return string : Date au format voulu.
   */
  public static function dateMDY($date, $display = false)
  {
    $display ? var_dump($display) : null;

    if ($date && $date != 'NA') {
      $dateRes = $date;
      $dateRes = date_create_from_format('m/d/Y', $date)->format('d/m/Y');

      $display ? var_dump($dateRes) : null;

      if ($dateRes == '01/01/1970')
        $dateRes = null;

      $display ? var_dump($dateRes) : null;
      return $dateRes;
    } else { return null; }
  }

  /**
   * Fonction qui permet d'inserer des données automatiquement dans la base de donnée Mold Equipment List
   * Paramètre :
   *  @param array : Données de la ligne importée
   * @return insert : Insertion en base de donnée
   * 
   * /!\ INFORMATION BDD /!\
   * INDICE_C_V Actual max : 20 -> il faut augmenter la taille ! (100 !)
   * STATUS_MOULE Actual max : 20 -> il faut augmenter la taille ! (40 !)
   * 
   * /!\ DOUBLONS DANS LES DONNEES /!\
   * Trouver les doublons : https://support.office.com/fr-fr/article/rechercher-et-supprimer-des-doublons-00e35bea-b46a-4d5d-b28e-66a552dc138d
   * Doublons trouvés à supprimer :
   *    - REF_MOULE = 366835
   *    - REF_MOULE = 367147
   */
  public function importMoldList($row)
  {
    $this->query(
      'INSERT INTO EXT_MOLDEQUIPMENTSLIST (
        STATUS_MOULE,
        REF_MOULE,
        REF_CTN,
        REF_SEGMENT,
        REF_FLANC_SUP,
        REF_FLANC_INF,
        COMMENTAIRE_1,
        REF_SPECS,
        PATTERNSIZE,
        CODE_PROD_2,
        INDICE_C_V,
        FLANCS_COMPATIBILITE,
        TYPE_CTN2,
        DIM_CTN2,
        GOUPILLE2,
        POSITION_ACTUEL,
        POSITION_ACTUEL_DATE,
        COMMENTAIRE_2,
        COMMENTAIRE,
        GSWR_BOTTOM,
        GSWR_TOP,
        GTRD,
        DRAWING,
        REVISION
      )
      VALUES (
        :status_moule,
        :ref_moule,
        :ref_ctn,
        :ref_segment,
        :ref_flanc_sup,
        :ref_flanc_inf,
        :commentaire_1,
        :ref_specs,
        :patternsize,
        :code_prod_2,
        :indice_c_v,
        :flancs_compatibilite,
        :type_ctn2,
        :dim_ctn2,
        :goupille2,
        :position_actuel,
        TO_DATE(:position_actuel_date, \'DD/MM/YYYY\'),
        :commentaire_2,
        :commentaire,
        :gswr_bottom,
        :gswr_top,
        :gtrd,
        :drawing,
        :revision
      )',
      array(
        ':status_moule' => $row[0],
        ':ref_moule' => $row[1],
        ':ref_ctn' => $row[2],
        ':ref_segment' => $row[3],
        ':ref_flanc_sup' => $row[4],
        ':ref_flanc_inf' => $row[5],
        ':commentaire_1' => $row[6],
        ':ref_specs' => $row[7],
        ':patternsize' => $row[8],
        ':code_prod_2' => $row[9],
        ':indice_c_v' => $row[10],
        ':flancs_compatibilite' => $row[11],
        ':type_ctn2' => $row[13],
        ':dim_ctn2' => $row[14],
        ':goupille2' => $row[15],
        ':position_actuel' => $row[16],
        ':position_actuel_date' => self::dateMDY($row[17], 'Mold List'),
        ':commentaire_2' => $row[18],
        ':commentaire' => $row[19],
        ':gswr_bottom' => $row[20],
        ':gswr_top' => $row[21],
        ':gtrd' => $row[22],
        ':drawing' => $row[23],
        ':revision' => $row[24]
      )
    );

    // Ajout des données supplémentaires dans la table prévu
    $this->query(
      'INSERT INTO MOLD_TECH (
        ID_MOLD,
        DATE_GET_MOLD,
        CLOGGED_HOLES
      )
      VALUES (
        :id_mold,
        TO_DATE(:date_get_mold, \'DD/MM/YYYY\'),
        :clogged_holes
      )',
      array(
        ':id_mold' => $row[1],
        ':date_get_mold' => self::dateMDY($row[12], 'Mold Tech'),
        ':clogged_holes' => $row[25] == 'OK' ? null : 'Y'
      )
    );

    // Ajout des statuts pour l'ensemble des moules
    $this->query(
      'INSERT INTO MOLD_STATUS (
        ID_MOLD,
        STATUS_TYPE,
        LOCATION_TYPE,
        LOCATION
      )
      VALUES (
        :id_mold,
        (SELECT ID FROM MOLD_STATUS_TYPE WHERE TYPE = :status_type),
        (SELECT ID FROM MOLD_LOCATION_TYPE WHERE TYPE = :location_type),
        :location
      )',
      array(
        ':id_mold' => $row[1],
        ':status_type' => $row[0],
        ':location_type' => $row[16],
        ':location' => $row[16]
      )
    );

    // Ajout des dérogations pour l'ensemble des moules
    $this->query(
      'INSERT INTO MOLD_EXEMPTION (
        ID_MOLD
      )
      VALUES (
        :id_mold
      )',
      array(
        ':id_mold' => $row[1],
      )
    );


    

    // Ajout de la métrologie pour l'ensemble des moules
    $this->query(
      'INSERT INTO MOLD_METROLOGY (
        ID_MOLD,
        LAST_CHECK_DATE,
        LAST_RESULT,
        COMMENT_METRO
      )
      VALUES (
        :id_mold,
        :last_check_date,
        :last_result,
        :comment_metro
      )',
      array(
        ':id_mold' => $row[1],
        ':last_check_date' => self::dateMDY($row[26], 'Metrology'),
        ':last_result' => strtoupper($row[27]) == 'NON CONFORME' ? 'notOkMetro' : strtoupper($row[27]) == 'CONFORME' ? 'okMetro' : 'notOkMetro',
        ':comment_metro' => $row[28],
      )
    );
  }



  /**
   * Fonction qui permet d'inserer des données automatiquement dans la base de donnée Mold Equipment Spec
   * Paramètre :
   *  @param array : Données de la ligne importée
   * @return insert : Insertion en base de donnée
   * 
   * /!\ INFORMATION BDD /!\
   * Aucun problème détecté
   */
  public function importMoldSpec($row)
  {
    $this->query(
      'INSERT INTO EXT_MOLDEQUIPMENTSPEC (
        STATUS,
        REF_SPEC,
        CODE_PROD_1,
        DIMENSION,
        INDICE_C_V,
        PATTERNSIZE,
        HEURE_RECORD_DATA,
        CODE_PROD_LIE,
        CODE_PROD_POSSIBLE,
        S_DIA_RELIEVE,
        SEUIL_CRYO_CODE,
        N_SPEC_EQUIP,
        CODE_PROFIL,
        CODE_DOT,
        CODE_HOMOLOGATION,
        CODE_ENOISE,
        CCCS,
        COM_SPECIF,
        TYPE_CTN,
        DIM_CTN,
        GOUPILLE,
        BAGO_BMR,
        BAGO_TMR,
        BAGO_ADAPTER,
        BAGO_BLADDER,
        MHI_BMR,
        MHI_BLADDER,
        JEUX_TOTAL_SECTEUR,
        S_DIAMETRE,
        L_DIAMETRE,
        T_MINI,
        T_MAXI,
        COM_DIVERS
      )
      VALUES (
        :status,
        :ref_spec,
        :code_prod_1,
        :dimension,
        :indice_c_v,
        :patternsize,
        TO_DATE(:heure_record_data, \'DD/MM/YYYY\'),
        :code_prod_lie,
        :code_prod_possible,
        :s_dia_relieve,
        TO_NUMBER(:seuil_cryo_code),
        :n_spec_equip,
        :code_profil,
        :code_dot,
        :code_homologation,
        :code_enoise,
        :cccs,
        :com_specif,
        :type_ctn,
        :dim_ctn,
        :goupille,
        :bago_bmr,
        :bago_tmr,
        :bago_adapter,
        :bago_bladder,
        :mhi_bmr,
        :mhi_bladder,
        TO_NUMBER(:jeux_total_secteur),
        TO_NUMBER(:s_diametre),
        TO_NUMBER(:l_diametre),
        :t_mini,
        :t_maxi,
        :com_divers
      )',
      array(
        ':status' => $row[0],
        ':ref_spec' => $row[1],
        ':code_prod_1' => $row[2],
        ':dimension' => $row[3],
        ':indice_c_v' => $row[4],
        ':patternsize' => $row[5],
        ':heure_record_data' => self::dateMDY($row[6], 'Mold Spec'),
        ':code_prod_lie' => $row[7],
        ':code_prod_possible' => $row[8],
        ':s_dia_relieve' => $row[9],
        ':seuil_cryo_code' => str_replace(',', '.', $row[10]),
        ':n_spec_equip' => $row[11],
        ':code_profil' => $row[12],
        ':code_dot' => $row[13],
        ':code_homologation' => $row[14],
        ':code_enoise' => $row[15],
        ':cccs' => $row[16],
        ':com_specif' => $row[17],
        ':type_ctn' => $row[19],
        ':dim_ctn' => $row[20],
        ':goupille' => $row[21],
        ':bago_bmr' => $row[22],
        ':bago_tmr' => $row[23],
        ':bago_adapter' => $row[24],
        ':bago_bladder' => $row[25],
        ':mhi_bmr' => $row[26],
        ':mhi_bladder' => $row[27],
        ':jeux_total_secteur' => str_replace(',', '.', $row[28]),
        ':s_diametre' => str_replace(',', '.', $row[29]),
        ':l_diametre' => str_replace(',', '.', $row[30]),
        ':t_mini' => $row[31],
        ':t_maxi' => $row[32],
        ':com_divers' => $row[33]
      )
    );

    $this->query(
      'INSERT INTO CODE_TECH (
        ID_CODE,
        SMARTEAM,
        SALES_CODE,
        TYPE_PRESSE
      )
      VALUES (
        :id_code,
        TO_DATE(:smarteam, \'DD/MM/YYYY\'),
        :sales_code,
        :type_presse
      )',
      array(
        ':id_code' => $row[2],
        ':smarteam' => self::dateMDY($row[18], 'Code Tech'),
        ':sales_code' => $row[34],
        ':type_presse' => $row[36]
      )
    );

    $this->query(
      'INSERT INTO CURES (
        TS_CODE,
        FACT_CODE,
        PCI
      )
      VALUES (
        :ts_code,
        :id_code,
        :pci
      )',
      array(
        ':ts_code' => $row[2] . '  A0',
        ':id_code' => $row[2],
        ':pci' => $row[35] == 'OUI' ? 'Y' : null
      )
    );
  }
}

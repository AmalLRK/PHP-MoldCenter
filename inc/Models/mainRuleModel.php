<?php

/**
 * Classe qui contient le modèle pour les droits principaux
 * - Récupération de la liste de tout les droits principaux
 */
class mainRuleModel extends Model
{
  /**
   * Fonction qui permet de récupérer la liste des droits principaux
   * @return array : Liste des données de chaque droits principaux
   */
  public function getMainRuleList()
  {
    $mainRuleList = json_decode(file_get_contents('./bdd/MAIN_RULE.json'), true);

    return $mainRuleList;
  }
}

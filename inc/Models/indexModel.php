<?php

/**
 * Classe qui contient le modèle pour les applications
 * - Récupération de la liste de toute les applications
 */
class indexModel extends Model
{
  /**
   * Fonction qui permet de récupérer la liste des applications
   * @return array : Liste des données de chaque applications
   */
  public function getIndexList()
  {
    $indexList = json_decode(file_get_contents('./bdd/INDEX.json'), true);

    return $indexList;
  }
}

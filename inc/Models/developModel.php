<?php

/**
 * Classe qui contient le modèle pour l'aide au développement
 * - Récupération du contenu d'une table
 */
class developModel extends Model
{
  /**
   * Fonction qui permet de récupérer le contenu d'un table
   * @return array : Liste des données d'une table
   */
  public function getTable($tableName)
  {
    $this->query('SELECT * FROM ' . $tableName);

    $table = $this->fetchAll('Dictionary');
    // var_dump($table,$tableName, 'SELECT * FROM ' . $tableName);
    return $table;
  }
}

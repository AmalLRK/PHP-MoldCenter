<?php

/**
 * Classe qui contient le modèle pour les données
 * - Récupération de la liste des données d'une application
 * - Récupération de la liste des données par application
 */
class dataModel extends Model
{
  /**
   * Fonction qui permet de récupérer la liste des données d'une application
   * @return array : Liste des données d'une application
   */
  public function getDataList($app)
  {
    $this->query(
      'SELECT BASE_NAME, NAME, DESCRIPTION, TYPE, COLOR
      FROM MOLD_DATA_LIST, MOLD_DATA_CATEGORY_TYPE
      WHERE CATEGORY=MOLD_DATA_CATEGORY_TYPE.ID AND APP=:app
      ORDER BY MOLD_DATA_LIST.ID',
      array(
        ':app' => $app
      )
    );

    $tmp = $this->fetchAll('Dictionary');

    // Création du tableau pour les données
    $dataList = array();

    // Application de la traduction pour chaque donnée et constitution du tableau des données
    foreach ($tmp as $id => $data) {
      $dataList[$data['BASE_NAME']]['name'] = $data['NAME'];
      $dataList[$data['BASE_NAME']]['description'] = $data['DESCRIPTION'];
      $dataList[$data['BASE_NAME']]['category']['name'] = $data['TYPE'];
      $dataList[$data['BASE_NAME']]['category']['color'] = $data['COLOR'];
    }

    return $dataList;
  }

  /**
   * Fonction qui permet de récupérer la liste de toute les données pour
   * chaque application.
   * @return array : Liste des données par application
   */
  public function getAllDataList()
  {
    // Récupération de la liste des applications qui dispose de données
    $this->query(
      'SELECT distinct APP
      FROM MOLD_DATA_LIST'
    );

    $appList = $this->fetchAll('Dictionary');

    // Création de la liste des données par application
    $dataList = array();

    foreach ($appList as $id => $app) {
      $dataList[$app['APP']] = $this->getDataList($app['APP']);
    }

    return $dataList;
  }

  /**
   * Fonction qui permet de récupérer la liste de toute les catgories disponible
   * @return array : Liste des catégories
   */
  public function getDataCategoryList()
  {
    // Récupération de la liste des catégorie
    $this->query(
      'SELECT TYPE, COLOR
      FROM MOLD_DATA_CATEGORY_TYPE'
    );

    $tmp = $this->fetchAll('Dictionary');

    // Création de la liste des catégories
    $dataCategoryList = array();

    foreach ($tmp as $id => $category)
      $dataCategoryList[$category['TYPE']] = $category['COLOR'];

    return $dataCategoryList;
  }

  /**
   * Fonction qui permet de récupérer la liste de toute les catégories dont
   * l'utilisateur dispose les droits de visualisation
   * Paramètre :
   *  @param string : Nom de l'application
   * @return array : Liste des catégories
   */
  public function getDataCategoryListUser($app)
  {
    // Récupération des droits utilisateurs
    $dataRule = frontController::getSession('role')['dataRule'][$app];

    // Récupération des informations sur les données
    $dataList = $this->getDataList($app);

    // Récupération de toute les catégories disponibles
    $dataCategoryList = $this->getDataCategoryList();

    $dataCategoryListUser = array();

    // Parcours de l'ensemble des catégories disponibles
    foreach ($dataCategoryList as $categoryBaseName => $color) {
      $find = false;
      // Vérification que l'utilisateur dispose de la catégorie
      foreach ($dataRule as $dataBaseName => $data) {
        if ($dataBaseName != 'FILTER') {
          if ($dataList[$dataBaseName]['category']['name'] == $categoryBaseName)
            $find = true;
        }
      }

      // Enregistrement de la catégorie si l'utilisateur la possède
      if ($find)
        $dataCategoryListUser[$categoryBaseName] = $color;
    }

    return $dataCategoryListUser;
  }

  /**
   * Fonction qui permet de récupérer la liste de toute les catégories dont
   * l'utilisateur dispose les droits de modification
   * Paramètre :
   *  @param string : Nom de l'application
   * @return array : Liste des catégories avec le droit de modification
   */
  public function getDataCategoryListUserEdit($app)
  {
    // Récupération des droits utilisateurs
    $dataRule = frontController::getSession('role')['dataRule'][$app];

    // Récupération des informations sur les données
    $dataList = $this->getDataList($app);

    // Récupération de toute les catégories disponibles
    $dataCategoryList = $this->getDataCategoryList();

    $dataCategoryListUserEdit = array();


    // Parcours de l'ensemble des catégories disponibles
    foreach ($dataCategoryList as $categoryBaseName => $color) {
      $find = false;
      // Vérification que l'utilisateur dispose de la catégorie
      foreach ($dataRule as $dataBaseName => $data) {
        if ($dataBaseName != 'FILTER') {
          if ($dataList[$dataBaseName]['category']['name'] == $categoryBaseName) {
            if (isset($dataRule[$dataBaseName]['edit']))
              $find = true;
          }
        }
      }

      // Enregistrement de la catégorie si l'utilisateur la possède
      if ($find)
        $dataCategoryListUserEdit[$categoryBaseName] = $color;
    }

    return $dataCategoryListUserEdit;
  }

  /**
   * Fonction qui permet d'ajouter une nouvelle donnée
   * Paramètres :
   *  @param string : Nom de l'application
   *  @param string : Nom de la catégorie
   *  @param string : Nom de la donnée en base de donnée
   *  @param string : Nom de la donnée
   *  @param string : Description de la donnée
   * @return insert : Ajout de la nouvelle donnée
   */
  public function addData($app, $categoryName, $dataBaseName, $dataName, $dataDescription)
  {
    $this->query(
      'INSERT INTO MOLD_DATA_LIST (
        APP,
        CATEGORY,
        BASE_NAME,
        NAME,
        DESCRIPTION)
      VALUES (
        :app,
        (SELECT ID FROM MOLD_DATA_CATEGORY_TYPE WHERE TYPE=:category),
        :baseName,
        :name,
        :description)',
      array(
        ':app' => $app,
        ':category' => $categoryName,
        ':baseName' => $dataBaseName,
        ':name' => $dataName,
        ':description' => $dataDescription
      )
    );
  }

  /**
   * Fonction qui permet d'ajouter une nouvelle catégorie de donnée
   * Paramètres :
   *  @param string : Nom de la nouvelle catégorie
   *  @param string : Couleur en hexadécimal de la nouvelle catégorie
   * @return insert : Ajout de la nouvelle catégorie
   */
  public function addDataCategory($categoryName, $categoryColor)
  {
    $this->query(
      'INSERT INTO MOLD_DATA_CATEGORY_TYPE (
        TYPE,
        COLOR)
      VALUES (
        :type,
        :color)',
      array(
        ':type' => $categoryName,
        ':color' => $categoryColor
      )
    );
  }
}

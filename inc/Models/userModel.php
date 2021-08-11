<?php

class userModel extends Model
{
  private $userid;
  private $password;

  public function __construct($userid = null, $password = null)
  {
    parent::__construct();

    $this->userid = strtoupper($userid);
    $this->password = $password == null ? null : $this->hash($password);
  }

  /**
   * Permet de vérifier si le client est logger.
   * @return boolean si connecté : true
   */
  public function checkUser()
  {
    if (!frontController::getSession('userid')) {
      $this->query(
        'SELECT USERID, NOMPRE, PASSWORD,
        dbms_lob.substr(MAIN_RULE) AS MAIN_RULE,
        dbms_lob.substr(DATA_RULE) AS DATA_RULE
				FROM MOLD_USERS USERS
				WHERE USERID = :userId AND PASSWORD = :password',
        array(
          ':userId' => $this->userid,
          ':password' => $this->password
        )
      );

      $userInfo = $this->fetch('Dictionary');

      if (!empty($userInfo)) {
        // Tableau pour les droits divers
        $rule = array();

        // Ajout des droits principaux disponibles
        $rule['mainRule'] = json_decode($userInfo['MAIN_RULE'], true);
        // Ajout des droits sur les données
        $rule['dataRule'] = json_decode($userInfo['DATA_RULE'], true);


        frontController::setSession('userid', $userInfo['USERID']);
        frontController::setSession('nomPrenom', $userInfo['NOMPRE']);
        frontController::setSession('role', $rule);
      }
    }
    if (frontController::getSession('userid')) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Fonction qui permet de récupérer un utilisateur
   * Paramètre :
   * 	@param int id : Identifiant unique de l'utilisateur
   * @return array utilisateur
   */
  public function getUser($id)
  {
    $this->query(
      'SELECT USERID, NOMPRE, PASSWORD,
      dbms_lob.substr(MAIN_RULE) AS MAIN_RULE,
      dbms_lob.substr(DATA_RULE) AS DATA_RULE
			FROM MOLD_USERS USERS
			WHERE USERID = :userId',
      array(
        ':userId' => $id
      )
    );

    $user = $this->fetch('Dictionary');

    // Conversion du JSON en tableau
    $user['MAIN_RULE'] = json_decode($user['MAIN_RULE'], true);
    $user['DATA_RULE'] = json_decode($user['DATA_RULE'], true);

    return $user;
  }

  /**
   * Fonction qui permet d'ajouter un nouvel utilisateur
   * Paramètres :
   * 	@param int id : Identifiant unique de l'utilisateur
   * 	@param string nom : Nom de l'utilisateur
   * 	@param string prenom : Prénom de l'utilisateur
   * 	@param string password : Mot de passe de l'utilisateur en hash !
   * 	@param array mainRule : Droits principaux des applications
   *  @param array dataRule : Droits sur les données
   * @return Ajout d'un utilisateur
   */
  public function addUser($id, $nom, $prenom, $password, $mainRule, $dataRule)
  {
    $this->query(
      'INSERT INTO MOLD_USERS (
        USERID,
        NOMPRE,
        PASSWORD,
        MAIN_RULE,
        DATA_RULE)
			VALUES (
        :id,
        :nompre,
        :password,
        TO_CLOB(:mainRule),
        TO_CLOB(:dataRule))',
      array(
        ':id' => strtoupper($id),
        ':nompre' => $nom . ' ' . $prenom,
        ':password' => $this->hash($password),
        ':mainRule' => json_encode($mainRule),
        ':dataRule' => json_encode($dataRule)
      )
    );
  }

  /**
   * Fonction qui permet de modifier un utilisateur
   * Paramètres :
   * 	@param int id : Identifiant unique de l'utilisateur
   * 	@param string nom : Nom de l'utilisateur
   * 	@param string prenom : Prénom de l'utilisateur
   * 	@param string password : Mot de passe de l'utilisateur en hash !
   * 	@param array mainRule : Droits principaux des applications
   *  @param array dataRule : Droits sur les données
   *  @param boolean passwordHash : Savoir si le mot de passe est Hash ou non
   * @return Modification de l'utilisateur
   */
  public function editUser($id, $nom, $prenom, $password, $mainRule, $dataRule, $passwordHash)
  {
    $this->query(
      'UPDATE MOLD_USERS
			SET NOMPRE=:nompre, PASSWORD=:password,
      MAIN_RULE=TO_CLOB(:mainRule), DATA_RULE=TO_CLOB(:dataRule)
			WHERE USERID=:id',
      array(
        ':id' => strtoupper($id),
        ':nompre' => $nom . ' ' . $prenom,
        ':password' => $passwordHash ? $password : $this->hash($password),
        ':mainRule' => json_encode($mainRule),
        ':dataRule' => json_encode($dataRule)
      )
    );
  }

  /**
   * Fonction qui permet de supprimer un utilisateur
   * Paramètre :
   * 	@param array Liste des utilisateurs à supprimer
   * @return Suppression de l'utilisateur
   */
  public function removeUser($userID = '')
  {
    if ($userID != '') {
      $this->query(
        'DELETE FROM MOLD_USERS WHERE USERID=:id',
        array(
          ':id' => $userID
        )
      );
    }
  }

  /**
   * Fonction qui permet de récupérer la liste des utilisateurs
   * @return array : Liste des utilisateurs
   */
  public function getUserList()
  {
    $this->query(
      'SELECT USERID, NOMPRE, PASSWORD,
      dbms_lob.substr(MAIN_RULE) AS MAIN_RULE,
      dbms_lob.substr(DATA_RULE) AS DATA_RULE
			FROM MOLD_USERS USERS'
    );

    $userList = $this->fetchAll('Dictionary');

    // Conversion du JSON en tableau pour les droits de chaque utilisateurs
    foreach ($userList as $key => $user)
    {
      $userList[$key]['MAIN_RULE'] = json_decode($user['MAIN_RULE'], true);
      $userList[$key]['DATA_RULE'] = json_decode($user['DATA_RULE'], true);
      $userList[$key]['ADMIN'] = frontController::userHaveRight('admin', 'ALL', $userList[$key]['MAIN_RULE']);
      $userList[$key]['MODERATOR'] = frontController::userHaveRight('moderator', 'None', $userList[$key]['MAIN_RULE']);
    }

    return $userList;
  }

  /**
   * Fonction qui permet de récupérer la liste des utilisateurs que le modérateur peut accéder
   * Paramètre :
   *  @param string : Identifiant unique de l'utilisateur
   * @return array : Liste des utilisateurs pour le modérateur
   */
  public function getUserListForModerator($userID)
  {
    $this->query(
      'SELECT USERID, NOMPRE, PASSWORD,
      dbms_lob.substr(MAIN_RULE) AS MAIN_RULE,
      dbms_lob.substr(DATA_RULE) AS DATA_RULE
			FROM MOLD_USERS USERS'
    );

    $userListTmp = $this->fetchAll('Dictionary');

    // Récupération des informations sur le modérateur
    $userModerator = $this->getUser($userID);

    // Préparation du tableau pour voir les utilisateurs voulu
    $userList = array();

    foreach ($userListTmp as $key => $user) {
      // Conversion du JSON en tableau pour les droits de chaque utilisateurs
      $userListTmp[$key]['MAIN_RULE'] = json_decode($user['MAIN_RULE'], true);
      $userListTmp[$key]['DATA_RULE'] = json_decode($user['DATA_RULE'], true);
      $userListTmp[$key]['ADMIN'] = frontController::userHaveRight('admin', 'ALL', $userListTmp[$key]['MAIN_RULE']);
      $userListTmp[$key]['MODERATOR'] = frontController::userHaveRight('moderator', 'None', $userListTmp[$key]['MAIN_RULE']);

      // Si l'utilisateur est administrateur alors on ne peut pas le voir
      if (!isset($userListTmp[$key]['MAIN_RULE']['admin'])) {
        $find = false;

        foreach ($userListTmp[$key]['MAIN_RULE'] as $app => $rule) {
          if (isset($userModerator['MAIN_RULE'][$app]['moderator']))
            $find = true;
        }

        if ($find)
          array_push($userList, $userListTmp[$key]);
      }
    }

    return $userList;
  }

  /**
   * Fonction qui permet de récupérer la liste des utilisateurs dans un dictionnaire
   * trier selon l'identifiant de l'utilisateur
   * ! Sans les informations sur les droits et les mots de passes !
   * @return array : Liste des utilisateurs sans les droits et mots de passes
   */
  public function getUserListIDSorted()
  {
    $this->query(
      'SELECT USERID, NOMPRE
			FROM MOLD_USERS USERS'
    );

    $userListTmp = $this->fetchAll('Dictionary');

    $userList = array();

    // Remplissage du tableau des utilisateurs trié
    foreach ($userListTmp as $key => $user)
      $userList[$user['USERID']] = array('nompre' => $user['NOMPRE']);

    return $userList;
  }

  /**
   * Génère un mot de passe hashé.
   * @param string $string mot de passe
   * @param string $algo type de hash (par défaut : 'sha1')
   * @return string le mot de passe hashé
   */
  public function hash($string, $algo = 'sha1')
  {
    return hash($algo, $string);
  }
}

<?php

class Model
{
  private $db;
  private $state;
  protected $elementName = 'Element';

  public function __construct()
  {
    $this->db = DatabaseFactory::getInstance();
    $this->query('ALTER SESSION SET NLS_DATE_FORMAT = \'DD-MM-YYYY HH24:MI:SS\'');
    $this->query('ALTER SESSION SET NLS_NUMERIC_CHARACTERS = \'.,\'');
  }

  final public function query($q, $fieldsValues = [])
  {
    try {
      $this->state = $this->db->prepare($q);

      foreach ($fieldsValues as $key => $value)
        $this->bindParam($key, $value);

      $this->state->execute();
    } catch (PDOException $e) {

      if (!isset($_SERVER['HTTP_X_AJAX'])) {
        $controller = new Controller();
        $controller->setParam(
          'error',
          array(
            'message' => $e->getMessage(),
            'query' => $q,
            'trace' => $e->getTrace(),
            'xdebug' => $e->xdebug_message
          )
        );
        $controller->setView('error');
        $controller->showView();
        exit;
      } else {
        http_response_code(400);
        echo json_encode(array('message' => $e->getMessage(), 'query' => $q));
        exit;
      }
    }
  }

  final public function commit()
  {
    return $this->state->commit();
  }

  final public function bindParam($parameter, $value, $var_type = null)
  {
    if (is_null($var_type)) {
      switch (true) {
        case is_bool($value):
          $var_type = PDO::PARAM_BOOL;
          break;
        case is_int($value):
          $var_type = PDO::PARAM_INT;
          break;
        case is_null($value):
          $var_type = PDO::PARAM_NULL;
          break;
          // case preg_match('/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/', $value):
          //     $value = $this->toDate($value);
          //     break;

        default:
          $var_type = PDO::PARAM_STR;
      }
    }

    $this->state->bindParam($parameter, $value, $var_type, strlen($value) + 1);
  }

  /*public function toDate($val)
    {
        return 'TO_DATE(\'' . $val . '\', dd/mm/yyyy)';
    }*/

  public function setElementName($elementName)
  {
    $this->elementName = $elementName;
  }

  public function getElementName()
  {
    return $this->elementName;
  }

  final public function fetch($elementName = '')
  {
    if ($elementName) {
      if ($elementName == 'Dictionary')
        $this->state->setFetchMode(PDO::FETCH_ASSOC);
      else
        $this->state->setFetchMode(PDO::FETCH_CLASS, $elementName);
    } else
      $this->state->setFetchMode(PDO::FETCH_CLASS, $this->elementName);

    return $this->state->fetch();
  }

  /**
   * Fonction qui permet de récupérer les données en base
   * Paramètre :
   *  @param string : Nom de la classe qui va récupérer les données ou
   * Dictionary pour obtenir un tableau associatif de donnée
   * @return array or object : Tableau associatif ou objet qui contient les données
   */
  final public function fetchAll($elementName = '')
  {
    if ($elementName) {
      if ($elementName == 'Dictionary')
        $this->state->setFetchMode(PDO::FETCH_ASSOC);
      else
        $this->state->setFetchMode(PDO::FETCH_CLASS, $elementName);
    } else
      $this->state->setFetchMode(PDO::FETCH_CLASS, $this->elementName);

    return $this->state->fetchall();
  }

  public function __set($name, $value)
  {

    $this->{$name} = $value;
  }

  public function __get($name)
  {
    if (isset($this->{$name}))
      return $this->{$name};

    return false;
  }
}

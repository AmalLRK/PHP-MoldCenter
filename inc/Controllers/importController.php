<?php

class importController extends Controller
{
  /**
   * Fonction qui permet d'afficher le bouton pour importer des données
   * /!\ ATTENTION /!\
   * Avant d'activer le système il faut aller voir le modèle d'importation pour
   * savoir comment le fichier Excel doit être formatté
   * 
   * Il est possible de juste visualiser un fichier Excel (avant de l'importer pour vérifier les données)
   * à l'aide des variables UPLOAD à false et VISUAL à true, avec ACTIVATED à true.
   */
  public function index()
  {
    $ACTIVATED = true; // Activation du système d'importation
    // $ACTIVATED = false; // Désactivation du système d'importation

    $VISUAL = true; // Activation de la visualisation des données
    // $VISUAL = false; // Désactivation de la visualisation des données

    $UPLOAD = true; // Activation du système d'importation vers la base de donnée
    // $UPLOAD = false; // Désactivation du système d'importation vers la base de donnée

    $this->setParam('activated', $ACTIVATED);

    // Importation si actif
    if ($ACTIVATED and frontController::haveRight('admin', 'ALL')) {
      // importation si un fichier est disponible
      if (isset($_FILES['file_moldSpec']) or isset($_FILES['file_moldList'])) {
        $importModel = new importModel();

        // Récupération du fichier envoyé
        $moldList = false;
        if (isset($_FILES['file_moldList'])) {
          $file = $_FILES['file_moldList'];
          $moldList = true;
        }

        $moldSpec = false;
        if (isset($_FILES['file_moldSpec'])) {
          $file = $_FILES['file_moldSpec'];
          $moldSpec = true;
        }

        // Création du lecteur Excel
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);

        $sheet = $spreadsheet->getActiveSheet();

        // Conversion en tableau des données Excel
        $tab = $sheet->toArray();

        // Envoi des données en base de données
        foreach ($tab as $key => $row) {
          if ($key > 0) {
            if ($UPLOAD) {
              // importation des bonnes données
              if ($moldList)
                $importModel->importMoldList($row);
              if ($moldSpec)
                $importModel->importMoldSpec($row);
            }

            if ($VISUAL)
              var_dump($row);
          }
        }

        if ($VISUAL)
        {
          var_dump($tab[0]);
          var_dump(count($tab));
        }
      } else {
        // Sinon affichage de la page principal d'importation
        $this->setParam('titlePage', '/!\ Import System /!\\');
        $this->setView('import');
        $this->setApp('Import');
        $this->showView();
      }
    } else {
      // Si non actif redirection vers le centre
      header('Location: /');
    }
  }
}

<?php

class toolsController extends Controller
{
  /**
   * Fonction qui d'obtenir la couleur d'un texte par rapport à la couleur de fond donnée
   * Paramètres :
   *  @param string : Couleur du fond voulu
   *  @param string : Couleur pour le texte clair
   *  @param string : Couleur pour le texte foncé
   *  @param int : seuil de changement de couleur (par défaut 128)
   * @return string : Couleur du texte par rapport au fond voulu
   */
  public static function autoTextColor($background, $colorBright = '#FFFFFF', $colorDark = '#000000', $seuil = 128)
  {
    //Nettoyage de la chaine
    $color = str_replace('#', '', $background);

    //Couleur au format rgb integer
    $rgb = array(
      'r' => hexdec(substr($color, 0, 2)),
      'g' => hexdec(substr($color, 2, 2)),
      'b' => hexdec(substr($color, 4, 2))
    );

    //coefficient de luminosité
    $coef = $rgb['r'] * 0.3 + $rgb['g'] * 0.59 + $rgb['b'] * 0.11;

    // coef <= 128 alors -> blanc
    // sinon -> noir
    return $coef <= $seuil ? $colorBright : $colorDark;
  }
}

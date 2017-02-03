<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/29/2017
 * Time: 7:13 AM
 */
$bodyHTML .= '<!DOCTYPE html>';
$bodyHTML .= '<html lang="de">';
$bodyHTML .= '  <head>';
$bodyHTML .= '      <meta charset="utf-8">';
$bodyHTML .= '  </head>';
$bodyHTML .= '  <body>';
$bodyHTML .= '      <div>';
// Begin content
$bodyHTML .= $mailContent;
// End content
$bodyHTML .= '      </div>';
$bodyHTML .= '  </body>';
$bodyHTML .= '</html>';
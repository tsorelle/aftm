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

$bodyHTML .= "<p>Dear $membername</p>";
$bodyHTML .= "<p>Thank you for joining Austin Friends of Traditional Music and for your membership payment of $cost</p>";
$bodyHTML .= "<p>The membership level you requested is: $membership</p>";
$bodyHTML .= $checkInfo;
$bodyHTML .= "<p>Here is the contact information we have for you.  Please contact us at aftmtexas@gmail.com if anything is incorrect, or if you have any further questions about AFTM.</p>";
$bodyHTML .= $contactInfo;
$bodyHTML .= $logo;
// End content
$bodyHTML .= '      </div>';
$bodyHTML .= '  </body>';
$bodyHTML .= '</html>';
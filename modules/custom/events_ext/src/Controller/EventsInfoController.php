<?php
/**
 * Created by PhpStorm.
 * User: nate
 * Date: 6. 08. 2018
 * Time: 19:13
 */
namespace Drupal\events_ext\Controller;

/**
 * Class EventsInfoController
 *
 * @package Drupal\events_ext\Controller
 */
class EventsInfoController
{
  /**
   * Calculates Time from Date
   *
   * @notes Returns Positive for "upcoming", negative for "ended" and 0 for "currently active" ..
   * @param string $timestamp
   * @return int
   */
  public function getDaysFromDate($timestamp)
  {
    $current = new \DateTime("now");
    $current->setTime(0, 0, 0); // Resets to midnight for clarity & easier pairing ..

    $date = \DateTime::createFromFormat("Y-m-d\\TH:i:s", $timestamp);
    $date->setTime(0, 0, 0);

    $diff = $current->diff($date);

    return (int)$diff->format( "%R%a" );
  }
}

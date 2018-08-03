<?php
/**
 * @file
 *
 * Created by PhpStorm.
 * User: Nate 'L0,
 * Date: 1. 08. 2018
 * Time: 13:54
 */
namespace Drupal\Events\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'Events' Block
 *
 * @Block(
 *  id = "block_events",
 *  admin_label = @Translation("Events block"),
 * )
 *
 * @notes A few observations:
 * In Prod scenario following options should exist in panel:
 * - Max entries to show
 * - Custom options /ability to exclude event from showing on listing
 * - Checkbox options where one could select which events to show (current|upcoming|finished)
 * - NiceTime print where instead of "in x days" it would use "in 1 day and 2 hours, 4 minutes"
 * - Ability to order by entry Created or Event Starts (as currently) in ASC/DESC order
 * - Ability to enable/disable "View More"
 * - [UX] Optionally create "flip card effect" or similar and tie lazy loading into module
 */
class EventsBlock extends BlockBase {

  /**
   * Holds DB data
   *
   * @var $output
   */
  private $output = null;

  /**
   * {@inheritdoc}
   */
  public function build() {

    $idList = \Drupal::entityQuery('node')
      ->condition('type', 'event')
      ->condition('status', 1)
      ->sort('field_event_date', 'DESC')
      ->pager(6) // 5 + 1 as we use last one as indication for "view more" ..
      ->execute();

    // There are entries ..
    if ($idList !== null && is_array($idList)) {
      $data = \Drupal::entityManager()->getStorage('node')->loadMultiple($idList);

      $i = 0;
      foreach ($data as $k => $v) {

        $this->output[$i]['link'] = $this->generateEventLink($k, $v->get('title')->value);
        $this->output[$i]['info'] = $this->generateEventInfo($v->get('field_event_date')->value);

        ++$i;
      }

      // Checks for "View More" print
      if (count($idList) > 5) {
        array_pop($this->output); // Remove 6th row..

        $this->output[6]['link'] = $this->generateMoreLink();
        $this->output[6]['info'] = null;
      }
    }

    // Build Output
    return [
      '#cache'      => ['max-age' => 0],  // Cache time in seconds (0 = off)
      '#theme'      => 'events_block',
      '#items'      => ($this->output == null ? [$this->t('There are no events..')] : $this->output),
      '#attached'   => [
        'library' => [
          'events_ext'
        ]
      ]
    ];
  }

  /**
   * Checks the date and builds output
   *
   * @param string $timestamp
   * @return text
   */
  private function generateEventInfo($timestamp) {
    $offset = $this->calculateDate($timestamp);

    // Upcoming
    if ($offset > 0) {

      if ($offset == 1) {
        return $this->t('Event starts in 1 day.');
      }
      else {
        return $this->t('Event starts in @time days.', ['@time' => $offset]);
      }
    }
    // Ended
    else if ($offset < 0) {
      return $this->t('The event has ended.');
    }

    return $this->t('The event is in progress.');
  }

  /**
   * Generates Event URL
   *
   * @param $rowID
   * @param $title
   * @return text
   */
  private function generateEventLink($rowID, $title) {

    $options = ['absolute' => true];
    $url = Url::fromRoute('entity.node.canonical', ['node' => $rowID], $options);
    $link = Link::fromTextAndUrl($title, $url);
    $link = $link->toRenderable();
    $link['#attributes'] = ['class' => ['internal']];

    return render($link);
  }

  /**
   * Generates "View More" link
   *
   * @return string
   */
  private function generateMoreLink()
  {
    $url = Url::fromUri('internal:/events');
    $link = Link::fromTextAndUrl($this->t('View More'), $url);
    $link = $link->toRenderable();
    $link['#attributes'] = ['class' => ['internal']];

    return render($link);
  }

  /**
   * Calculates Time from Date
   *
   * @notes Returns Positive for "upcoming", negative for "ended" and 0 for "currently active" ..
   * @param string $timestamp
   * @return int
   */
  private function calculateDate($timestamp) {
    $current = new \DateTime("now");
    $current->setTime(0, 0, 0); // Resets to midnight for clarity & easier pairing ..

    $date = \DateTime::createFromFormat("Y-m-d\\TH:i:s", $timestamp);
    $date->setTime(0, 0, 0);

    $diff = $current->diff($date);

    return (int)$diff->format( "%R%a" );
  }
}

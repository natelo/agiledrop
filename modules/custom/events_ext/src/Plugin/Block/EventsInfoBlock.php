<?php
/**
 * @file
 *
 * Created by PhpStorm.
 * User: Nate 'L0,
 * Date: 1. 08. 2018
 * Time: 13:54
 */
namespace Drupal\events_ext\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Events Info' Block
 *
 * @Block(
 *  id = "block_events_info",
 *  admin_label = @Translation("Events info block"),
 * )
 *
 */
class EventsInfoBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  /**
   * Just a holder for cases where we do not wish to render module ..
   *
   * @var array
   */
  private $renderOff = array();

  /**
   * Init class & Sort render
   *
   * @param $configuration
   * @param $pluginId
   * @param $pluginDefinition
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition)
  {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    // Hides the module if/when needed ..
    $this->renderOff = [ '#cache' => ['max-age' => 0]];
  }

  /**
   * Creates/re-uses Instance of class
   *
   * @param ContainerInterface $container
   * @param array $configuration
   * @param $pluginId
   * @param $pluginDefinition
   * @return EventsInfoBlock
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition)
  {
    return new static($configuration, $pluginId, $pluginDefinition, $container->get('events_ext.events_info'));
  }

  /**
   * Checks the date and builds output
   *
   * @param string $timestamp
   * @return text
   */
  private function generateEventInfo($timestamp)
  {
    $offset =  \Drupal::service('events_ext.events_info')->getDaysFromDate($timestamp);

    // Upcoming
    if ($offset > 0) {

      // Plural|Singular handling (XXX: does not account for double..)
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
   * {@inheritdoc}
   */
  public function build()
  {
    // 'GetParameter' returns url-bit after 'node/' or null which indicates we're not viewing node
    $nodeID = \Drupal::routeMatch()->getParameter('node');
    if ($nodeID === null || $nodeID->getType() != 'event') {
      return $this->renderOff;
    }

    $date = $nodeID->field_event_date->value;
    if ($date == null) {
      return $this->renderOff;
    }

    // Build Output
    return [
      '#cache'  => ['max-age' => 0],  // Cache time in seconds (0 = off)
      '#markup' => $this->generateEventInfo($date)
    ];
  }
}

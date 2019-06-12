<?php
namespace Drupal\vass_core\Controller;

use Drupal\Core\Controller\ControllerBase;

class PageGeneral extends ControllerBase {
  public function result() {
    $build = array();

    $build['#attached']['library'][] = 'cc_oktoberfest/ranking';

    $query = \Drupal::database()->select('vass_core_quiz', 'q');

    $query->join('node_field_data', 'n', 'n.nid = q.qid');

    
    $query->orderBy('q.created', 'DESC');

    $result = $query
      ->fields('q')
      ->fields('n', array('title'))
      ->execute();

    $rows = array();
    
    $date_formatter = \Drupal::service('date.formatter');
    
    foreach ($result as $item) {
      $rows[] = array(
        $date_formatter->format($item->created),
        $item->title,
        $item->response,
      );
    }
    
    $build['page'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array(
        'Date', 'Question', 'Response'
      ),
    );
  
    $build['pager'] = array(
      '#type' => 'pager',
    );
    
    return $build;
  }
}

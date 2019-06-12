<?php
/**
 * @file
 */

namespace Drupal\vass_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;


class VassQuizForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vass_quiz_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'quiz')
      ->accessCheck(FALSE);
  
    $nids = $query->execute();
  
  
    $nodes = Node::loadMultiple($nids);
    $form['questions'] = array('#tree' => TRUE);
    foreach ($nodes as $nid => $node) {
    
      $options = array();
    
      foreach ($node->get('field_answer')->getValue() as $key => $value) {
        $options[$value['value']] = $value['value'];
      }
    
      $form['questions'][$nid] = array(
        '#type' => 'select',
        '#title' => $node->getTitle(),
        '#options' => $options,
        '#required' => TRUE,
      );
    
    }
  
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save',
    );
    
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('questions') as $key => $value) {
      \Drupal::database()->insert('vass_core_quiz')
        ->fields(array(
          'created' => time(),
          'uid' => \Drupal::currentUser()->id(),
          'qid' => $key,
          'response' => $value,
        ))
        ->execute();
    }
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  
  }
}

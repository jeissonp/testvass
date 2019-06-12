<?php
namespace Drupal\vass_core\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\api_api\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'vass_core.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vass_core_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // load configuration
    $config = $this->config('vass_core.config');
    // google maps api key
    
    $form['message_success'] = [
      '#type' => 'textarea',
      '#title' => 'Message',
      '#required' => TRUE,
      '#default_value' => $config->get('message_success'),
    ];
    
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('vass_core.config')
      ->set('message_success', $form_state->getValue('message_success'))
      ->save();
  }
}

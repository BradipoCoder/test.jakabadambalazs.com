<?php

namespace Drupal\kadmin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigKadmin.
 *
 * @package Drupal\kadmin\Form
 */
class ConfigKadmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'kadmin.configkadmin',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_kadmin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('kadmin.configkadmin');
    
    $form['fontawesome'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use FontAwesome icons'),
      '#description' => $this->t('Check this to use fontawesome in the the Kadmin menu'),
      '#default_value' => $config->get('fontawesome'),
    ];
    $form['ct_links'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Content Type links'),
      '#description' => $this->t('Check this to add links per Content Types (manage fields, manage display)'),
      '#default_value' => $config->get('ct_links'),
    ];
    $form['views_links'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Views links'),
      '#description' => $this->t('Check this to add links for all views listed on this website'),
      '#default_value' => $config->get('views_links'),
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

    $this->config('kadmin.configkadmin')
      ->set('fontawesome', $form_state->getValue('fontawesome'))
      ->set('ct_links', $form_state->getValue('ct_links'))
      ->set('views_links', $form_state->getValue('views_links'))
      ->save();
  }

}

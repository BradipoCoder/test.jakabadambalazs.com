<?php

namespace Drupal\footer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Provides a 'FooterBlock' block.
 *
 * @Block(
 *  id = "footer_block",
 *  admin_label = @Translation("Footer block"),
 * )
 */
class FooterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Constructs a new FooterBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        EntityTypeManager $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
         'view_main_menu' => TRUE,
         'view_social_menu' => TRUE,
         'text' => $this->t(''),
         'date' => $this->t('2018'),
         'social' => [
          'value' => $this->t(''),
          'format' => 'full_html', 
         ],
         'img' => $this->t(''),
        ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    
    $form['img'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Footer logo'),
      '#description' => $this->t('Footer logo'),
      '#upload_validators' => array(
       'file_validate_extensions' => array('gif png jpg jpeg svg'),
       'file_validate_size' => array(25600000),
      ),
      '#default_value' => $this->configuration['img'],
      '#upload_location' => 'public://footer',
    ];

    $form['view_main_menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('View main menu'),
      '#description' => $this->t('Display the main menu in the footer'),
      '#default_value' => $this->configuration['view_main_menu'],
    ];

    $form['date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Anno in cui è stato pubblicato il sito'),
      '#description' => $this->t('Anno in cui è stato pubblicato il sito'),
      '#default_value' => $this->configuration['date'],
    ];

    $form['text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Testo'),
      '#description' => $this->t('Testo del footer'),
    ];

    //if (isset($this->configuration['text']['value'])){
    //  $form['text']['#default_value'] = $this->configuration['text']['value'];
    //}
//
    //if (isset($this->configuration['text']['format'])){
    //  $form['text']['#format'] = $this->configuration['text']['format'];
    //}

    $form['social'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Social'),
      '#description' => $this->t('Testo per i canali social'),
      '#default_value' => $this->configuration['social']['value'],
      '#format' => $this->configuration['social']['format'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['view_main_menu'] = $form_state->getValue('view_main_menu');
    $this->configuration['date'] = $form_state->getValue('date');
    $this->configuration['text'] = $form_state->getValue('text');
    $this->configuration['social'] = $form_state->getValue('social');
    
    $fid = $form_state->getValue('img');
    $this->configuration['img'] = $fid;

    // Setto il file come permanente
    if (isset($fid[0])){
      $file = File::load($fid[0]);
    
      if (!empty($file)){
        $file->setPermanent();  
        $file->save();
        $file_usage = \Drupal::service('file.usage');
        $file_usage->add($file, 'footer', 'footer', \Drupal::currentUser()->id());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $content['title'] = [
      '#markup' => '<h1 class="text-center"><i class="fa fa-cog"></i> A really awesome footer</h1>',
    ];

    $text = $this->configuration['text'];
    $content['text']['#markup'] = Markup::create($text['value']);

    $social = $this->configuration['social'];
    $content['social']['#markup'] = Markup::create($social['value']);

    $fid = $this->configuration['img'];
    if (isset($fid[0])){
      $file = File::load($fid[0]);
      if (!empty($file)){
        $uri = $file->url();
        $url = Url::fromUri($uri);
        $src = $url->toString();
        $content['logo'] = [
          '#markup' => '<img src="' . $src . '" class="img-responsive footer-logo"/>',
        ];
      }  
    }
    
    
    // Main menu
    if ($this->configuration['view_main_menu']){
      $content['menu'] = $this->loadMainMenu();  
    }

    // Nome del sito
    $config = \Drupal::config('system.site');
    $content['site_name']['#markup'] = $config->get('name');

    // Anno di creazione + anno odierno (se diverso)
    $created = $this->configuration['date'];
    $content['created']['#markup'] = $created;
    if ($created !== date('Y')){
      $content['year']['#markup'] = ' - ' . date('Y');
    }

    $build['footer'] = [
      '#theme' => 'footer',
      '#content' => $content,
    ];

    return $build;
  }

  private function loadMainMenu(){
    
    $menu_tree = \Drupal::menuTree();
    $menu_tree_parameters = new MenuTreeParameters();
    //$menu_tree_parameters->minDepth = 1;
    $menu_tree_parameters->excludeRoot();

    $tree = $menu_tree->load('main', $menu_tree_parameters);
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);

    $menu = $menu_tree->build($tree);
    $menu['#theme'] = 'menu';

    return $menu;
  }

}

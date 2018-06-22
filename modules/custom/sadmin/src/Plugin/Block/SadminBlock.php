<?php

namespace Drupal\sadmin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

use Drupal\system\Entity\Menu;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

/**
 * Provides a 'SadminBlock' block.
 *
 * @Block(
 *  id = "sadmin_block",
 *  admin_label = @Translation("Simple admin bar"),
 * )
 */
class SadminBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Constructs a new SadminBlock object.
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
         'smenu' => 0,
        ] + parent::defaultConfiguration();

 }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    
    $form['smenu'] = [
      '#type' => 'select',
      '#title' => $this->t('Menù da visualizzare'),
      '#description' => $this->t('Seleziona il menu da visualizzare nella barra Simple admin'),
      '#options' => $this->getMenuOptionList(),
      '#default_value' => $this->configuration['smenu'],
      '#size' => 1,
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['smenu'] = $form_state->getValue('smenu');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $tree = $this->getMenutree();
    $content['menu'] = $this->createMenu($tree);
    $content['user'] = $this->getUserMenu();

    $build = [
      '#theme' => 'sadmin',
      '#content' => $content,
    ];

    return $build;
  }

  private function createMenu($tree){
    $data = [];

    $current_path = \Drupal::request()->getRequestUri();

    if ($tree){

      $data['#prefix'] = '<span class="sadmin-menu-menu">';
      $data['#suffix'] = '</span>';

      foreach ($tree as $key => $item) {
        $opt = [
          'attributes' => [
            'class' => [
              'a-sadmin',
            ],
          ],
        ];

        $link = $item->link;
        if ($link->isEnabled()){
          $url = $link->getUrlObject();
          $title = $link->getTitle();

          if ($url->toString() == $current_path){
            $opt['attributes']['class'][] = 'is-active';
          }

          $url->setOptions($opt);
          $data[$key] = Link::fromTextAndUrl($title, $url)->toRenderable();
        }
      }  
    }
    return $data;
  }

  private function getMenuTree(){
    $name = $this->configuration['smenu'];

    $menu_tree = \Drupal::menuTree();
    $menu_tree_parameters = new MenuTreeParameters();
    //$menu_tree_parameters->minDepth = 1;
    $menu_tree_parameters->excludeRoot();

    $tree = \Drupal::menuTree()->load($name, $menu_tree_parameters);
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);
    return $tree;
  }

  private function getUserMenu(){
    
    $data['#prefix'] = '<span class="sadmin-menu-user">';
    $data['#suffix'] = '</span>';

    

    $user = \Drupal::currentUser();
    $name = $user->getUsername();

    $url = Url::fromRoute('user.page');
    $opt = [
      'attributes' => [
        'class' => [
          'a-sadmin', 'a-sadmin-user', 'a-sadmin-user-name'
        ],
      ],
    ];
    $url->setOptions($opt);
    $data['user'] = Link::fromTextAndUrl($name, $url)->toRenderable();

    $url = Url::fromRoute('user.logout.http');
    $opt = [
      'attributes' => [
        'class' => [
          'a-sadmin', 'a-sadmin-user',
        ],
      ],
    ];
    $url->setOptions($opt);
    $markup = Markup::create('<i class="material-icons">exit_to_app</i>');
    $data['logout'] = Link::fromTextAndUrl($markup, $url)->toRenderable();
    return $data;
  }

  private function getMenuOptionList(){
    $options = $this->getMenuList();
    return $options;
  }

  private function getMenuList() {
    $all_menus = Menu::loadMultiple();
    $menus = array();
    foreach ($all_menus as $id => $menu) {
      $menus[$id] = $menu->label();
    }
    asort($menus);
    return $menus;
  }

}

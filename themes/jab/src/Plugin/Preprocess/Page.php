<?php

namespace Drupal\jab\Plugin\Preprocess;

use Drupal\bootstrap\Utility\Element;
use Drupal\bootstrap\Utility\Variables;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Menu\MenuTreeParameters;
use \Drupal\bootstrap\Plugin\Preprocess\Page as DrupalPreprocessPage;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Pre-processes variables for the "page" theme hook.
 *
 * @ingroup plugins_preprocess
 *
 * @BootstrapPreprocess("page")
 */
class Page extends DrupalPreprocessPage {

  // It should be noted that you do not need all three methods here.
  // This is to just show you the different examples of how this plugin
  // works and how they can be tailored to your needs.

  protected $pathMatcher;
  protected $languageManager;
  private $currentPath;
  protected $menuLinkManager;

  /**
   * Page constructor.
   *
   * @param array $vars
   * @param $hook
   * @param array $info
   */
  function __construct(array &$vars, $hook, array $info) {
    parent::__construct($vars, $hook, $info);

    $this->pathMatcher = \Drupal::service('path.matcher');
    $this->languageManager = \Drupal::service('language_manager');
    $this->currentPath = \Drupal::service('path.current')->getPath();
    $this->menuLinkManager = \Drupal::service('plugin.manager.menu.link');
  }

  public function preprocess(array &$variables, $hook, array $info) {
    parent::preprocess($variables, $hook, $info);

    //kint($variables['page']['content']);

    if ($variables['is_front']) {
      $home = new Home();
      $home->preprocess($variables);
    }
  }
  /*
  public function preprocess(array &$variables, $hook, array $info) {

    $this->addLogo($variables);
    //$this->addUserMenu($variables);
    $this->addRightMenu($variables);

    $this->addSearchInMemu($variables);
    $this->addLanguageMenu($variables);

    if ($variables['is_front']){
      $home = new Home();
      $home->preprocess($variables);
    }

    $this->addFooterMenu($variables, $info);

    // If you are extending and overriding a preprocess method from the base
    // theme, it is imperative that you also call the parent (base theme) method
    // at some point in the process, typically after you have finished with your
    // preprocessing.
    parent::preprocess($variables, $hook, $info);
  }

  private function addLogo(&$variables){

    global $base_url;
    $src = $base_url . '/' . drupal_get_path('theme', 'jab') . '/img/logo-falpi.svg';

    $logo = [
      '#markup' => '<a href="/" title="Homepage"><img src="' . $src . '" class="header-logo img-responsive"/></a>',
    ];

    $variables['page']['logo'] = $logo;
  }

  private function addUserMenu(&$variables){
    // Add Edit
    $user = \Drupal::currentUser();

    if ($user->isAnonymous()){
      $url = Url::fromRoute('user.login');
      $link = Link::fromTextAndUrl('Log in', $url)->toRenderable();
      //$variables['page']['user_menu'] = $link;

    } else {

      $name = $user->getAccountName();
      $url = Url::fromRoute('user.page');
      $markup = Markup::create('<i class="material-icons">person</i> ' . $name);
      $o = [
        'attributes' => [
          'class' => 'a-with-icon',
        ],
      ];
      $url->setOptions($o);
      $link = Link::fromTextAndUrl($markup, $url)->toRenderable();

      $variables['page']['user_menu']['user'] = [
        '#prefix' => '<li>',
        '#suffix' => '</li>',
        'data' => $link,
      ];

      //$markup = Markup::create('<i class="material-icons">done</i> Log out');
      $url = Url::fromRoute('user.logout');
      $link = Link::fromTextAndUrl('Esci', $url)->toRenderable();
      $variables['page']['user_menu']['out'] = [
        '#prefix' => '<li>',
        '#suffix' => '</li>',
        'data' => $link,
      ];

    }
  }

  private function addRightMenu(&$vars){
    $list = $this->loadRightMenu();
    foreach ($list as $key => $data) {
      $link = Link::fromTextAndUrl($data['title'], $data['url'])->toRenderable();
      $vars['page']['menu_right'][$key] = [
        '#prefix' => '<li>',
        '#suffix' => '</li>',
        'data' => $link,
      ];
    }

    // Add access menu item
    $build = [
      '#theme' => 'access_dropdown',
    ];
    $vars['page']['menu_access'] = $build;
  }

  private function addLanguageMenu(&$vars){
    $build = [];
    $route_name = $this->pathMatcher->isFrontPage() ? '<front>' : '<current>';
    $links = $this->languageManager->getLanguageSwitchLinks('language_interface', Url::fromRoute($route_name));
    $current_language_id = \Drupal::languageManager()->getCurrentLanguage()->getId();

    global $base_url;
    $src = $base_url . '/' . drupal_get_path('theme', 'jab') . '/img/ico-' . $current_language_id . '.svg';
    $img = '<img src="' . $src . '" class="img-language"/>';
    $current['#markup'] = $img;

    //@TODO: riabilitare il francese
    unset($links->links['fr']);

    $list = [
      '#theme' => 'links',
      '#links' => $links->links,
      '#attributes' => [
        'class' => [
          'language-switcher-navbar', 'dropdown-menu'
        ],
      ],
      '#set_active_class' => TRUE,
    ];

    $build = [
      '#theme' => 'language_dropdown',
      '#list' => $list,
      '#current' => $current,
    ];

    $vars['page']['language_ddm'] = $build;
  }

  /**
   * Aggiungo un link alla ricerca in archivio
   * /
  private function addSearchInMemu(&$vars){
    if ($this->currentPath !== '/products/archive'){
      $url = Url::fromRoute('archive.archive_controller_archive');
      $url->setOptions([
        'query' => [
          'reset' => 'true',
          'focus' => 'true',
        ],
        'attributes' => [
          'class' => [
            'a-search-nb',
          ],
        ],
      ]);
      $markup = Markup::create('<span class="search-nb"></span>');
      $link = Link::fromTextAndUrl($markup, $url)->toRenderable();

      $data = [
        '#prefix' => '<li>',
        '#suffix' => '</li>',
        'link' => $link,
      ];

     $vars['page']['search'] = $data;
     $vars['page']['mobile_search'] = $data['link'];
    }
  }

  private function loadRightMenu(){
    $menu_tree = \Drupal::menuTree();
    $menu_tree_parameters = new MenuTreeParameters();
    //$menu_tree_parameters->minDepth = 1;
    $menu_tree_parameters->excludeRoot();

    $tree = $menu_tree->load('main-menu-dx', $menu_tree_parameters);
    $manipulators = array(
      array('callable' => 'menu.default_tree_manipulators:checkAccess'),
      array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
    );
    $tree = $menu_tree->transform($tree, $manipulators);

    $menu = $menu_tree->build($tree);

    $data = false;
    if (isset($menu['#items'])){
      $list = $menu['#items'];
      foreach ($list as $key => $item) {

        $data[$key]['url'] = $item['url'];
        $data[$key]['title'] = $item['title'];
      }
    }

    return $data;
  }

  private function addFooterMenu(&$vars){
    $view = false;
    if ($vars['is_front']){
      $view = true;
    }

    if (isset($vars['node'])){

      $node = $vars['node'];
      if ($node->getType() == 'product'){
        $view = true;
      }

      if ($node->getType() == 'page'){
        $view = true;
        if ($node->id() == '1886'){
          $view = false;
        }
      }

      if ($view == true){
        $current_path = \Drupal::service('path.current')->getPath();
        $chunks = explode('/', $current_path);
        if (isset($chunks[3])){
          $view = false;
        }
      }

    }

    if (!$view){
      return;
    }

    // Link al catalogo
    $url = Url::fromRoute('entity.node.canonical', ['node' => 1886]);
    $url->setOptions([
      'attributes' => [
        'class' => ['btn', 'btn-default'],
      ],
    ]);
    $link = Link::fromTextAndUrl('Richiedi la tua copia', $url);

    $data = [
      '#theme' => 'footer_menu',
      '#catalog' => [
        'more' => $link,
      ],
    ];

    $list = $this->getFooterMenuList();
    $this_nid = \Drupal::routeMatch()->getRawParameter('node');
    foreach ($list as $key => $value) {

      $group = [];
      $p = $value['p_item'];
      $group['key'] = $key;
      $group['title'] = $p->getTitle();

      foreach ($value['tree'] as $k => $mitem) {
        $group['list'][$k]['url'] = $this->createLink($mitem, $this_nid);

        $children = false;
        if ($mitem->hasChildren){
          $children = $mitem->subtree;
          foreach ($children as $s => $sitem) {

            $ss = [
              '#prefix' => '<li class="footer-menu-li">',
              '#suffix' => '</li>',
              'link' => $this->createLink($sitem, $this_nid),
            ];

            $group['list'][$k]['sub'][$s] = $ss;
          }
        }

      }
      $data['#list'][$key] = $group;
    }
    $vars['page']['footer_menu'] = $data;
  }

  private function getFooterMenuList(){
    $list['products']['p_id'] = 'menu_link_content:425e89c1-453a-41d5-9ecb-c1a683c10925';
    $list['tech']['p_id'] = 'menu_link_content:189d477e-eaba-4af6-b62b-18b646f9cc35';
    $list['systems']['p_id'] = 'menu_link_content:f9921879-526b-4f32-b91d-615d42a5ee57';

    // Menu Link Manager
    $mlm = $this->menuLinkManager;

    // Load the main menu
    $menu_tree = \Drupal::menuTree();
    $menu_tree_parameters = new MenuTreeParameters();
    $menu_tree_parameters->excludeRoot();

    foreach ($list as $key => $group){
      $pid = $group['p_id'];
      $list[$key]['p_item'] = $mlm->createInstance($pid);
      $menu_tree_parameters->setRoot($pid);
      $tree = \Drupal::menuTree()->load('main', $menu_tree_parameters);
      $manipulators = array(
        array('callable' => 'menu.default_tree_manipulators:checkAccess'),
        array('callable' => 'menu.default_tree_manipulators:generateIndexAndSort'),
      );
      $tree = $menu_tree->transform($tree, $manipulators);
      $list[$key]['tree'] = $tree;
    }
    return $list;
  }

  private function createLink($mitem, $current_nid = false){
    $link = $mitem->link;

    $text = $link->getTitle();
    $route = $link->getRouteName();

    // Questo purtroppo non è un oggetto Url ben creato per qualche motivo
    $ur = $link->getUrlObject();
    $uri = $ur->toUriString();
    $url = Url::fromUri($uri);

    $opt['attributes'] = [
      'class' => ['a-angle'],
    ];

    if ($current_nid){
      $parameters = $url->getRouteParameters();
      if (isset($parameters['node'])){
        if ($parameters['node'] == $current_nid){
          $opt['attributes']['class'][] = 'text-green';
        }
      }
    }

    if (!isset($parameters['node'])){
      if ($route == 'archive.archive_controller_archive'){
        $opt['query']['reset'] = true;
      }
    }

    $url->setOptions($opt);
    $data = Link::fromTextAndUrl($text, $url)->toRenderable();

    return $data;
  }
  */

}
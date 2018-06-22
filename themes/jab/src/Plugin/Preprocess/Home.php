<?php

/**
 * @file
 * Contains \Drupal\jab\Plugin\Preprocess\Home.
 */

namespace Drupal\jab\Plugin\Preprocess;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;

use Drupal\archive\Data\LinkCount;


/**
 * Preprocess Home page
 */
class Home
{

  /**
   * @param array $variables
   */
  public function preprocess(array &$variables) {
    //$this->addCover($variables);
    //$this->addBigCard($variables);
    //
    //dpm($variables["page"]);

    // Rimuovo il blocco content in home page
    unset($variables['page']['content']['jab_content']);


  }

  /*
  private function addCover(&$variables){

    $path = drupal_get_path('theme', 'jab') . '/video/';
    $video = [
      'mp4' => $path . 'home.mp4',
      //'webm' => $path . 'ocean.webm',
      //'ogv' => $path . 'ocean.ogv',
      'poster' => $path . 'home.jpg',
    ];

    foreach ($video as $key => $path) {
      $videopaths[] = $key . ': ' . $path;
    }

    $videopath = implode($videopaths, ', ');

    $content = [
      'title' => [
        '#markup' => 'Falpi.',
      ],
      'sub' => 'Design for cleaning',
      'videopath' => $videopath,
    ];

    $cover = [
      '#theme' => 'home_cover',
      '#content' => $content,
    ];
    $variables['page']['home']['cover'] = $cover;
  }

  private function addBigCard(&$variables){
    $content = [
      'products' => $this->addProducts(),
      'eco' => $this->addEco(),
      'block' => $this->addBlock(),
      //'cart' => $this->addCart(),
      //'textile' => $this->addTextile(),
      //'tools' => $this->addTools(),
      'webform' => $this->addWebform(),
    ];

    $variables['page']['home']['card'] = [
      '#theme' => 'home_card',
      '#content' => $content,
    ];
  }

  private function addProducts(){

    $url_p_teaser = Url::fromRoute('archive.archive_controller_products');
    $url_archive = Url::fromRoute('archive.archive_controller_archive');
    $lk = new LinkCount([]);

    //t($item['name'], [], ['context' => 'page:product:system']);

    $content['products'] = [
      '#theme' => 'block_list',
      '#list' => [
        0 => [
          'title' => t('Falpi range'),
          'href' => $url_p_teaser->toString(),
        ],
        1 => [
          'title' => t('All products'),
          'href' => $url_archive->toString(),
          'count' => $lk->getCount(true),
        ],
      ],
    ];

    $title = t('Products');
    $content['products_more'] = Link::fromTextAndUrl($title, $url_p_teaser);

    $url_s_teaser = Url::fromRoute('archive.archive_controller_systems');
    $content['systems'] = [
      '#theme' => 'block_list',
      '#list' => [
        0 => [
          'title' => t('All systems'),
          'href' => $url_s_teaser->toString(),
        ],
        1 => [
          'title' => t('Configurator'),
          'href' => 'http://configuratore.jab.com/app/',
          'attributes' => [
            '#markup' => 'target="_blank"',
          ],
        ],
      ],
    ];

    $title = t('Systems');
    $content['systems_more'] = Link::fromTextAndUrl($title, $url_s_teaser);

    $data = [
      '#theme' => 'home_products',
      '#content' => $content,
    ];
    return $data;
  }

  private function addEco(){
    $content = [
      'image_path' => drupal_get_path('theme', 'jab') . '/img/falpi-green.jpg',
    ];

    // Current Language ID
    $cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();

    // CAM GPP
    $url = Url::fromRoute('archive.archive_controller_archive');
    $query = [
      'cert' => 130,
    ];
    $url->setRouteParameters($query);
    $lk = new LinkCount($query);

    $content['cam']['green_circle'] = [
      '#theme' => 'green_circle',
      '#number' => '100',
      '#number_suffix' => '%',
      '#sub' => t('GPP'),
      '#desc' => t('(Green Public Procurement) products'),
      '#url' => $url->toString(),
      '#lang' => $cLangId,
    ];
    $content['cam_url']['#markup'] = $url->toString();

    // Ecolabel
    $url = Url::fromRoute('archive.archive_controller_archive');
    $query = [
      'type' => 95,
      'cert' => 10,
    ];
    $url->setRouteParameters($query);
    $lk = new LinkCount($query);

    $content['ecolabel']['green_circle'] = [
      '#theme' => 'green_circle',
      '#number' => '160',
      '#sub' => t('EU Ecolabel'),
      '#desc' => t('certified textiles'),
      '#url' => $url->toString(),
      '#lang' => $cLangId,
    ];
    $content['ecolabel_url']['#markup'] = $url->toString();

    // Carrelli certificati
    $url = Url::fromRoute('archive.archive_controller_archive');
    $query = [
      'type' => 94,
      'cert' => 9,
    ];
    $url->setRouteParameters($query);
    $lk = new LinkCount($query);

    $content['epd']['green_circle'] = [
      '#theme' => 'green_circle',
      '#number' => '46',
      '#sub' => t('EPD'),
      '#desc' => t('(Environmental Product Declaration) certified trolleys'),
      '#url' => $url->toString(),
      '#lang' => $cLangId,
    ];
    $content['epd_url']['#markup'] = $url->toString();

    $data = [
      '#theme' => 'home_eco',
      '#content' => $content,
    ];
    return $data;
  }

  private function addBlock(){
    $content = [
      'title' => [
        '#markup' => 'test',
      ]
    ];

    $block = [
      '#theme' => 'home_block',
      '#content' => $content,
    ];
    return $block;
  }

  private function addWebform(){

    $text = t('Contact us at', [], ['context' => 'contact']);
    $text .= ' <a href="mailto:info@jakabadambalazs.com">info@jakabadambalazs.com</a> ';
    $text .= t('or call us at', [], ['context' => 'contact']);

    $webform = [
      '#theme' => 'magic_form',
      '#subject' => [
        '#markup' => $text,
      ],
      '#webform' => $this->getWebForm(),
      '#id' => 'magic-form-product',
    ];
    return $webform;
  }

  private function getWebForm(){
    return [
      '#type' => 'webform',
      '#webform' => 'contact',
      '#default_data' => [
        'info' => 'Home page',
      ],
    ];
  }


  // ** OLD STUFF **
  // ---------------

  private function addCart(){
    $content = [
      'image_path' => drupal_get_path('theme', 'jab') . '/img/home-cart.jpg',
    ];

    $content['green_circle'] = [
      '#theme' => 'green_circle',
      '#number' => '46',
      '#desc' => 'carrelli certificati',
      '#sub' => 'EPD',
      '#url' => '/products/carts?cert=9',
    ];

    $cart_list = [
      0 => [
        'name' => 'Carrelli chiusi',
        'vid' => 'cat',
        'tid' => 135,
      ],
      1 => [
        'name' => 'Carrelli Hospital',
        'vid' => 'cat',
        'tid' => 134,
      ],
      2 => [
        'name' => 'Carrelli Hotel',
        'vid' => 'cat',
        //'tid' => 134,
      ],
      3 => [
        'name' => 'Carrelli mop',
        'vid' => 'cat',
        'tid' => 133,
      ],
      4 => [
        'name' => 'Carrelli di servizio',
        'vid' => 'cat',
        //'tid' => 133,
      ],
      5 => [
        'name' => 'Carrelli EPD',
        'vid' => 'cert',
        'tid' => 9,
      ],
    ];

    $split = 'a';
    foreach ($cart_list as $key => $cart) {

      if ($key >= 2){ $split = 'b'; }
      if ($key >= 4){ $split = 'c'; }

      $href = '#';
      if (isset($cart['vid']) && isset($cart['tid'])){
        $href = '/products/carts?' . $cart['vid'] . '=' . $cart['tid'];
      }

      $data[$split][$key] = [
        '#prefix' => '<li><span class="hover"></span>',
        '#suffix' => '</li>',
        '#markup' => '<a href="' . $href . '" class="h5">' . $cart['name'] . '<i class="material-icons">keyboard_arrow_right</i></a>',
      ];
    }

    foreach ($data as $split => $list) {
      $data[$split]['#prefix'] = '<ul class="block-list">';
      $data[$split]['#suffix'] = '</ul>';
    }

    $content['product_list'] = $data;
    $content['main_path']['#markup'] = '/products/carts?reset=true';

    $cart = [
      '#theme' => 'home_cart',
      '#content' => $content,
    ];
    return $cart;
  }

  private function addTextile(){
    $content = [
      'image_path' => drupal_get_path('theme', 'jab') . '/img/home-textile.jpg',
    ];

    $content['green_circle'] = [
      '#theme' => 'green_circle',
      '#number' => '142',
      '#desc' => 'prodotti tessili certificati',
      '#sub' => 'Ecolabel EU',
      '#url' => '/products/textiles?cert=10',
    ];
    $content['main_path']['#markup'] = '/products/textiles-equipments?reset=true';

    $data = [
      '#theme' => 'home_textile',
      '#content' => $content,
    ];
    return $data;
  }

  private function addTools(){
    $content = [
      'image_path' => drupal_get_path('theme', 'jab') . '/img/home-tools-square.jpg',
    ];

    $tool_list = [
      0 => [
        'name' => 'Sweeping',
        'path' => 'products/textiles-equipments',
        'vid' => 'system',
        'tid' => 160,
      ],
      1 => [
        'name' => 'Washing',
        'path' => 'products/textiles-equipments',
        'vid' => 'system',
        'tid' => 136,
      ],
      2 => [
        'name' => 'Tessili Ecolabel EU',
        'path' => 'products/textiles',
        'vid' => 'cert',
        'tid' => 10,
      ],
      3 => [
        'name' => 'Linea vetri',
        'path' => 'products/textiles-equipments',
        'vid' => 'system',
        'tid' => 163,
      ],
      4 => [
        'name' => 'Monouso',
        'path' => 'products/textiles-equipments',
        'vid' => 'system',
        'tid' => 164,
      ],
      5 => [
        'name' => 'Linea Light',
        'path' => 'products/textiles',
        'vid' => 'system',
        'tid' => 165,
      ],
      6 => [
        'name' => 'Accessori',
        'path' => 'products/tools',
      ],
    ];

    $split = 'a';
    foreach ($tool_list as $key => $tool) {

      if ($key >= 3){ $split = 'b'; }
      if ($key >= 6){ $split = 'c'; }

      $href = $tool['path'];
      if (isset($tool['vid']) && isset($tool['tid'])){
        $href = $tool['path'] . '?' . $tool['vid'] . '=' . $tool['tid'];
      }

      $data[$split][$key] = [
        '#prefix' => '<li><span class="hover"></span>',
        '#suffix' => '</li>',
        '#markup' => '<a href="' . $href . '" class="h5">' . $tool['name'] . '<i class="material-icons">keyboard_arrow_right</i></a>',
      ];
    }

    foreach ($data as $split => $list) {
      $data[$split]['#prefix'] = '<ul class="block-list">';
      $data[$split]['#suffix'] = '</ul>';
    }

    $content['list'] = $data;
    $content['main_path']['#markup'] = '/products/equipments?reset=true';

    $data = [
      '#theme' => 'home_tools',
      '#content' => $content,
    ];
    return $data;
  }
  */
}
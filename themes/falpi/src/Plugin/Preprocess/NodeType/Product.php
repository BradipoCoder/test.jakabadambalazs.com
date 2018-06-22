<?php

namespace Drupal\falpi\Plugin\Preprocess\NodeType;

use Drupal\falpi\Common\Pagination;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Element;
use Drupal\file\Entity\File;
use Drupal\views\Views;

use Drupal\archive\Data\ArchiveData;

/**
 * Preprocess Node Product
 */
class Product extends NodeType{

  //protected $entityTypeManager;
  //protected $dateFormatter;
  
  // Current Language Id
  private $cLangId = false;

  // Codice prodotto (default o primo variante)
  private $pCode = false;

  // Pagination object
  private $pagination = false;

  protected $entityTypeManager;

  protected $archiveData;

  protected $variants;

  protected $family = false;

  protected $mainCode = false;

  function __construct(array &$vars, $hook, array $info) {
    parent::__construct($vars, $hook, $info);

    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager();
    // Try to use also \Drupal::service('id')
    $this->dateFormatter = \Drupal::service('date.formatter');

    // See ArchiveController
    $this->type_list = \Drupal::state()->get('archive_tipe_list');

    // Current Language ID
    $this->cLangId = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $this->archiveData = new ArchiveData();
  }

  public function preprocess(){
    $this->loadTech();

    if ($this->vars['view_mode'] == 'full'){
      $this->preprocessFull();
    }

    if ($this->vars['view_mode'] == 'teaser'){
      $this->preprocessTeaser();
    }
  }

  private function preprocessFull(){
    if ($this->vars['tech']){
      $tech = $this->vars['tech'];

      $view = [
        'label' => 'hidden',
        'type' => 'colorbox',
        'settings' => [
          'colorbox_node_style' => 'product',
          'colorbox_caption' => 'auto',
          'colorbox_gallery' => 'page',
        ],
      ];

      $vb = $this->entityTypeManager->getViewBuilder('node');

      // Image
      if (!$tech->get('field_imgs')->isEmpty()){
        $img = $vb->viewField($tech->field_imgs, $view);
        $this->vars['content']['img']['#attached'] = $img['#attached'];
        $this->vars['content']['img'][0] = $img[0];
      }

      // Titolo
      $title = $tech->getTitle();
      $this->vars['content']['title'] = [
        '#prefix' => '<h1 class="title replaced-title tech-title" id="page-title">',
        '#suffix' => '</h1>',
        '#markup' => $title,
      ];

      // Sottotitolo
      if (!$tech->get('field_subtitle')->isEmpty()){
        $field_subtitle = $tech->get('field_subtitle');
        // https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21EntityViewBuilderInterface.php/function/EntityViewBuilderInterface%3A%3AviewField/8.2.x
        $build = $field_subtitle->view(['label' => 'hidden']);
        $this->vars['content']['subtitle'] = $build[0];
        $this->vars['content']['subtitle']['#prefix'] = '<p class="product-subtitle lead">';
        $this->vars['content']['subtitle']['#suffix'] = '</p>';
      }

      // Description
      if (!$tech->get('body')->isEmpty()){
        $body = $tech->get('body');
        $build = $body->view(['label' => 'hidden']);
        $new = $build[0]['#text'];
        $new = str_replace('<p>&nbsp;</p>', '', $new);
        $new = str_replace('<p><br />', '<p>', $new);
        $new = str_replace('&nbsp;', ' ', $new);
        $build[0]['#text'] = $new;

        $this->vars['content']['body'] = $build[0];
      }

      $this->createProductHead();
      $this->createTechLink();
      
      $this->createProductTable();
      $this->createProductDetails();
      $this->createVariant();

      $this->createCatalabLink();
      $this->createImgAndThumbs();

      $this->addConfiguratorLink();

      $this->addHeaderCerts();
      $this->addWebform();
      $this->addPagination();
      $this->addRelated();
    }
  }

  private function createProductHead(){
    $node = $this->vars['node'];
    $tech = $this->vars['tech'];
    $tech_type_id = $this->vars['tech_type_id'];

    $et = $this->entityTypeManager;
    $taxonomy = $et->getStorage('taxonomy_term');
    $nodes = $et->getStorage('node');

    $archiveData = $this->archiveData;

    // Type
    $url = $archiveData->createArchiveUrl($tech_type_id);
    if ($url){
      $type = $taxonomy->load($tech_type_id);
      $link = Link::fromTextAndUrl($type->getName(), $url)->toRenderable();
      $data['sub']['type'] = [
        '#prefix' => t('In',[],['context' => 'product']) . ' <span class="item">',
        '#suffix' => '</span>',
        'link' => $link,
        '#weight' => 0,
      ];  
    }

    // Tipologia
    if (!$node->get('field_ref_cat')->isEmpty()){
      $value = $node->get('field_ref_cat')->getValue();
      $tid = $value[0]['target_id'];
      $term = $taxonomy->load($tid);
      $name = $term->getName();
      $url = $archiveData->createArchiveUrl();
      if ($url){
        $opt = [
          'query' => [
            'cat' => $term->id(),
          ],
        ];
        $url->setOptions($opt);
        $link = Link::fromTextAndUrl($name, $url)->toRenderable();
        $data['sub']['cat'] = [
          '#prefix' => '<span class="divider">–</span><span class="item"><span class="product-full-label">' . t('Type',[],['context' => 'product']). '</span> ',
          '#suffix' => '</span>',
          'link' => $link,
          '#weight' => 1,
        ];
      }
    }

    // Family
    if (!$node->get('field_ref_family')->isEmpty()){
      $value = $node->get('field_ref_family')->getValue();
      $nid = $value[0]['target_id'];
      $family = $nodes->load($nid);
      $this->family = $family;

      $url = $archiveData->createArchiveUrl();
      $opt = [
        'query' => [
          'family' => $family->id(),
        ],
      ];
      $url->setOptions($opt);
      $link = Link::fromTextAndUrl($family->getTitle(), $url)->toRenderable();
      $data['sub']['family'] = [
        '#prefix' => '<span class="divider">–</span><span class="item"><span class="product-full-label">' . t('Product family',[],['context' => 'product']). '</span> ',
        '#suffix' => '</span>',
        'link' => $link,
        '#weight' => 2,
      ];
    }

    $data['sub']['#prefix'] = '<div class="wrapper-product-head copy"><p class="small">';
    $data['sub']['#suffix'] = '</p></div>';

    $this->vars['content']['head'] = $data;
  }

  private function createTechLink(){
    $is_anonym = \Drupal::currentUser()->isAnonymous();
    if (!$is_anonym) {
      if ($this->vars['tech']){
        $tech = $this->vars['tech'];
        $url = $tech->toUrl();
        $url->setOptions([
          'attributes' => [
            'class' => ['btn', 'btn-xs', 'btn-primary'],
          ],
        ]);
        $text = t('Technical data sheet',[], ['context' => 'product']);
        $this->vars['content']['p_links']['tech'] = Link::fromTextAndUrl($text, $url)->toRenderable();
      }
    } 
  }

  private function createCatalabLink(){
    if ($this->mainCode){

      $code = $this->mainCode;
      if (strpos($this->mainCode, ".")){
        $code = substr($this->mainCode, 0, strpos($this->mainCode, "."));  
      }

      $url = URL::fromRoute('catalab.archive');
      $url->setOptions([
        'attributes' => [
          'class' => ['btn', 'btn-xs', 'btn-default'],
        ],
        'query' => [
          'query' => $code,
        ],
      ]);
      $this->vars['content']['p_links']['catalab'] = Link::fromTextAndUrl('Guarda su Catalab', $url)->toRenderable(); 
      $this->vars['content']['p_links']['catalab']['#prefix'] = ' ';
    }
  }

  private function addConfiguratorLink(){
    $family = $this->family;
    if ($family){

      // Se esiste la traduzione della variante
      if ($family->getTranslationStatus($this->cLangId)){
        $family = $family->getTranslation($this->cLangId);  
      }

      if (!$family->get('field_config_url')->isEmpty()){
        $value = $family->get('field_config_url')->getValue();
        $url = $value[0]['uri'];
        if ($url !== ''){

          global $base_url;
          $path = $base_url . '/' . drupal_get_path('theme', 'falpi') . '/img/';

          $src = $path . 'ico-configura.svg';
          $value = '<span class="config"><img src="' . $src . '"/></span> ' . t('Configure');

          $this->vars['content']['config_link'] = [
            '#prefix' => '<a href="' . $url . '" target="_blank" class="btn btn-default">',
            '#suffix' => '</a>',
            '#markup' => $value,
          ];
        }
      }
    }
  }

  private function createProductTable(){
    $node = $this->vars['node'];   

    if (isset($this->vars['content']['field_ref_solution'])){
      $field = $this->vars['content']['field_ref_solution'];
      $table['solution'] = $this->alterTaxonomyFieldUrl($field, 'solution');
    }

    if (isset($this->vars['content']['field_ref_system'])){
      $field = $this->vars['content']['field_ref_system'];
      $table['system'] = $this->alterTaxonomyFieldUrl($field, 'system');  
    }

    if (isset($this->vars['content']['field_ref_where'])){
      $table['where'] = $this->vars['content']['field_ref_where'];  
    }

    if (isset($table)){
      $this->vars['content']['table'] = [
        '#prefix' => '<div class="product-table copy">',
        '#suffix' => '</div>',
        'table' => $table,
      ];  
    }
  }

  /**
   * Passando un campo costruito e il vocabolario
   * sostituisce i link puntando all'archivio
   */
  private function alterTaxonomyFieldUrl($field, $vid){
    $tech_type_id = $this->vars['tech_type_id'];
    $archiveData = $this->archiveData;

    $children = Element::children($field);
    foreach ($children as $key) {
      $item = $field[$key];
      $url = $item['#url'];
      $param = $url->getRouteParameters();
      if (isset($param['taxonomy_term'])){
        $tid = $param['taxonomy_term'];
        $url = $archiveData->createArchiveUrl();
        $par[$vid] = $tid;
        $url->setRouteParameters($par);
        $field[$key]['#url'] = $url;
      }
    }
    return $field;
  }

  private function createProductDetails(){
    $node = $this->vars['node'];
    $tech = $this->vars['tech'];

    global $base_url;
    $path = $base_url . '/' . drupal_get_path('theme', 'falpi') . '/img/';

    // Codice colore
    if (!$node->get('field_color_code')->isEmpty()){
      $value = $node->get('field_color_code')->getValue();
      $value = $value[0]['value'];
      if ($value){
        // TO DO: translate
        $label = 'Codice colore';
        $src = $path . 'ico-codice-colore.svg';
        $value = '<span class="code-color"><img src="' . $src . '"/></span> disponibile';
        $data['color_code'] = $this->createMarkupForDetailCustom('color_code', $label, $value, 1);
      }
    }
    
    // Caratteristiche tecniche
    $dimension = $this->findTermInsideTech('65');
    if ($dimension){
      $data['dimension'] = $this->createMarkupForDetail('65', $dimension, 2);
    }

    // Colori disponibili
    $colors = $this->findTermInsideTech('13');
    if ($colors){
      $data['colors'] = $this->createMarkupForDetail('13', $colors, 3);
    }

    // Composizione
    $composition = $this->findTermInsideTech('57');
    if ($composition){
      $data['composition'] = $this->createMarkupForDetail('57', $composition, 4);
    }

    if (isset($data['colors'])){
      if (!$node->get('field_color_custom')->isEmpty()){
        $value = $node->get('field_color_custom')->getValue();
        $value = $value[0]['value'];
        if ($value){
          // TO DO: tradurre
          $src = $path . '/ico-varianti-colore.svg';
          $html = '<div class="add-on-color-custom"><span class="color-custom"><img src="' . $src . '"/></span> varianti personalizzate</span></div>';
          $data['colors']['value']['#markup'] .= $html;
        }
      }  
    }

    if (isset($data)){
      $this->vars['content']['details'] = $data;
      $this->vars['content']['details']['#prefix'] = '<div class="wrapper-details">';
      $this->vars['content']['details']['#suffix'] = '</div>';
    }
  }

  private function createVariant(){
    $tech = $this->vars['tech'];
    $node = $this->vars['node'];

    $et = $this->entityTypeManager;

    // Codice base
    if (!$tech->get('field_code')->isEmpty()){
      $code = $tech->get('field_code')->getValue();
      $data['code'] = [
        '#prefix' => '<div id="#product-default-code" class="product-default-code">',
        '#suffix' => '</div>',
        '#markup' => $code[0]['value'],
      ];
      $this->mainCode = $code[0]['value'];
    }

    // Variant
    if (!$tech->get('field_ref_variant')->isEmpty()){
      $field_ref_variant = $tech->get('field_ref_variant');
      $variants = $field_ref_variant->getValue();
      foreach ($variants as $key => $value) {
        $nids[$key] = $value['target_id'];
      }
      $nodes = $et->getStorage('node')->loadMultiple($nids);
      $language = $this->cLangId;

      $this->variants = $nodes;

      $n = 0;
      foreach ($nodes as $key => $node) {

        // Se esiste la traduzione della variante
        if ($node->getTranslationStatus($this->cLangId)){
          $node = $node->getTranslation($this->cLangId);  
        }

        $title = $node->get('title')->getValue();

        $code = $node->get('field_code')->getValue();
        $m_code = trim(str_replace('.', '', $code[0]['value']));

        $list[$key] = [
          'code' => [
            '#markup' => trim($code[0]['value']),
          ],
          'm_code' => [
            '#markup' => $m_code
          ],
          'title' => [
            '#markup' => $title[0]['value'],
          ],
          'active' => '',
          'checked' => '',
        ];

        if ($n == 0){
          $list[$key]['active'] = 'active';
          $list[$key]['checked'] = 'checked';
          $this->pCode = $code[0]['value'] . ' ' . $title[0]['value'];
        }

        $n++;
      }

      $data = [
        '#theme' => 'variant_radio',
        '#list' => $list,
      ];
    }

    $this->vars['content']['variant'] = $data;
  }

  /**
   * Crea le thumbnails per le varianti
   */
  private function createImgAndThumbs(){
    $variants = $this->variants;

    $tech = $this->vars['tech'];
    $node = $this->vars['node'];

    $et = $this->entityTypeManager;
    $vb = $et->getViewBuilder('node');

    // Definisco due view (sm e big)
    $view['sm'] = [
      'label' => 'hidden',
      'settings' => [
        'image_style' => 'product_xs',
      ],
    ];

    // Impostazioni colorbox
    $view['big'] = [
      'label' => 'hidden',
      'type' => 'colorbox',
      'settings' => [
        'colorbox_node_style' => 'product',
        'colorbox_caption' => 'auto',
        'colorbox_gallery' => 'page',
      ],
    ];

    // Prendo l'immagine di default
    if (!$tech->get('field_imgs')->isEmpty()){
      // Thumbs default
      $img = $vb->viewField($tech->field_imgs, $view['sm']);
      $default_thumbs = $img[0];
      
      // Main image colorbox
      $img = $vb->viewField($tech->field_imgs, $view['big']);
      $default_cbox['#attached'] = $img['#attached'];
      $tmp['#attached'] = $img['#attached'];
      $default_cbox[0] = $img[0];

      // Main code
      $code = $tech->get('field_code')->getValue();
      $main_default_m_code = trim(str_replace('.', '', $code[0]['value']));
    }

    if ($variants){
      // Conto le immagini particolari delle varianti
      // Se non ce ne sono, non ha senso creare le thumbnail
      $count = 0;

      // Colleziono le varianti
      foreach ($variants as $key => $variant) {

        // Codice della variante (mandatory)
        $code = $variant->get('field_code')->getValue();
        $m_code = trim(str_replace('.', '', $code[0]['value']));

        // Immagine specifica della variante
        if (!$variant->get('field_img')->isEmpty()){
          // Thumbnail
          $build = $vb->viewField($variant->field_img, $view['sm']);
          $img = $build[0];
          
          // Colleziono l'immagine grande della variante
          $build = $vb->viewField($variant->field_img, $view['big']);
          $build[0]['#item']->set('title', $node->getTitle());
          $cboxs[$key]['cbox'] = $build[0];
          $cboxs[$key]['cbox']['#attached'] = $tmp['#attached'];
          
          $count++;
        } else {
          
          // Se il codice della variante è uguale al codice del prodotto
          // Assegno l'immagine principale a questa variante
          if ($m_code == $main_default_m_code){
            $cboxs[$key]['cbox'] = $default_cbox;  
          }

          $img = $default_thumbs;
        }

        $cboxs[$key]['m_code'] = $m_code;

        $list[$key] = [
          'code' => [
            '#markup' => $m_code,
          ],
          'img' => $img,
        ];
      }

      // Add a wrapper to the colorboxes previews
      $n = 0;
      foreach ($cboxs as $k => $item) {
        
        $m_code = $item['m_code'];

        $classes = ['cbox-item'];
        if ($n !== 0){
          $classes[] = 'hide';
        }

        $n++;

        if (isset($item['cbox'])){
          $data_cboxs[$k] = [
            '#prefix' => '<div id="cbox-item-' . $m_code . '" class="' . implode($classes, ' ') . '">',
            '#suffix' => '</div>',
            'data' => $item['cbox'],
          ];
        }
      }

      // Se esistono le thumbnails le visualizzo
      if ($count){
        $this->vars['content']['thumbs'] = [
          '#theme' => 'thumbs',
          '#list' => $list,
        ];
      }
    }

    // Se il data cboxs è vuoto significa che non c'è nessuna variante che ha il codice uguale al main
    // Oppure se esiste solo un'immagine cbox
    // Azzero la variavile data_boxs con l'immagine di default
    if (!isset($data_cboxs) || count($data_cboxs) == 1){
      // A questo punto creo solo un item colorbox senza riferimento al codice
      $data_cboxs = [
        '#prefix' => '<div id="cbox-item-main" class="cbox-item">',
        '#suffix' => '</div>',
        'data' => $default_cbox,
      ];
    }

    $this->vars['content']['main'] = $data_cboxs; 
  }

  private function addHeaderCerts(){
    global $base_url;
    $path = $base_url . '/' . drupal_get_path('theme', 'falpi') . '/img/';

    $tech = $this->vars['tech'];
    $archiveData = $this->archiveData;

    // Certificazioni
    if (!$tech->get('field_ref_cert')->isEmpty()){
      $values = $tech->get('field_ref_cert')->getValue();
      foreach ($values as $key => $value) {
        $tid = $value['target_id'];
        $tids[$tid] = $tid;
      }

      $url = $archiveData->createArchiveUrl();

      // CAM
      if (isset($tids[130])){
        $opt = [
          'query' => [
            'cert' => 130,
          ],
        ];
        $url->setOptions($opt);
        $img = '<img src="' . $path . 'cam.svg" class="img-responsive"/>';
        $markup = Markup::create($img);
        $link = Link::fromTextAndUrl($markup, $url)->toRenderable();

        $head['cert']['cam'] = [
          '#prefix' => '<div class="full-cert full-cert-cam">',
          '#suffix' => '</div>',
          'link' => $link,
        ];  
      }

      // Ecolabel
      if (isset($tids['10'])){
        $opt = [
          'query' => [
            'cert' => 10,
          ],
        ];
        $url->setOptions($opt);
        $img = '<img src="' . $path . 'ecolabel-n-reg-vertical.jpg" class="img-responsive"/>';
        $markup = Markup::create($img);
        $link = Link::fromTextAndUrl($markup, $url)->toRenderable();

        $head['cert']['ecolabel'] = [
          '#prefix' => '<div class="full-cert full-cert-ecolabel">',
          '#suffix' => '</div>',
          'link' => $link,
        ];
      }

      // Epd
      if (isset($tids['9'])){
        $opt = [
          'query' => [
            'cert' => 9,
          ],
        ];
        $url->setOptions($opt);
        $img = '<img src="' . $path . 'epd.jpg" class="img-responsive"/>';
        $markup = Markup::create($img);
        $link = Link::fromTextAndUrl($markup, $url)->toRenderable();

        $head['cert']['epd'] = [
          '#prefix' => '<div class="full-cert full-cert-epd">',
          '#suffix' => '</div>',
          'link' => $link,
        ];
      }
    }

    $this->vars['main_img_class'] = '';
    if (isset($head['cert'])){
      $this->vars['main_img_class'] = 'has-certs';
      $head['cert']['#prefix'] = '<div class="wrapper-full-product-certs">';
      $head['cert']['#suffix'] = '</div>';
      $this->vars['content']['img_head'] = $head;  
    }
  }

  private function findTermInsideTech($tid){
    $tech = $this->vars['tech'];
    $value = false;
    // Campo 1
    if (!$tech->get('field_tech_1')->isEmpty()){
      $tech1 = $tech->get('field_tech_1')->getValue();
      foreach ($tech1 as $key => $val) {
        if (isset($val['target_id']) && $val['target_id'] == $tid){
          $value = $val['text'];
          break;
        }
      }
    }

    if ($value){
      return $value;
    }

    // Campo 2
    if (!$tech->get('field_tech_2')->isEmpty()){
      $tech1 = $tech->get('field_tech_2')->getValue();
      foreach ($tech1 as $key => $val) {
        if (isset($val['target_id']) && $val['target_id'] == $tid){
          $value = $val['text'];
          break;
        }
      }
    }

    return $value;
  }

  private function createMarkupForDetail($tid, $value, $weight = false){
    $et = $this->entityTypeManager;
    $taxonomy = $et->getStorage('taxonomy_term');

    $term = $taxonomy->load($tid);

    $term = $term->getTranslation($this->cLangId);

    $name = $term->get('name')->getValue();

    $data = [
      '#prefix' => '<div class="wrapper-detail wrapper-detail-' . $tid . '">',
      '#suffix' => '</div>',
      'label' => [
        '#prefix' => '<div class="product-detail-label">',
        '#suffix' => '</div>',
        '#markup' => $name[0]['value'],
      ],
      'value' => [
        '#prefix' => '<div class="product-detail-value">',
        '#suffix' => '</div>',
        '#markup' => nl2br($value),
      ],
    ];

    if ($weight){
      $data['#weight'] = $weight;  
    }

    return $data;
  }

  private function createMarkupForDetailCustom($id, $label, $value, $weight = false){
    $data = [
      '#prefix' => '<div class="wrapper-detail wrapper-detail-' . $id . '">',
      '#suffix' => '</div>',
      'label' => [
        '#markup' => '<div class="product-detail-label">' . $label . '</div>',
      ],
      'value' => [
        '#markup' => '<div class="product-detail-value">' . $value . '</div>',
      ],
    ];

    if ($weight){
      $data['#weight'] = $weight;  
    }

    return $data;
  }

  private function preprocessTeaser(){
    if ($this->vars['tech']){

      global $base_url;
      $path = $base_url . '/' . drupal_get_path('theme', 'falpi') . '/img/';
      
      $tech = $this->vars['tech'];
      
      // Image
      if (!$tech->get('field_imgs')->isEmpty()){
        $values = $tech->get('field_imgs')->getValue();
        $fid = $values[0]['target_id'];
        $file = File::load($fid);
        $uri = $file->getFileUri();

        $this->vars['content']['img'] = [
          '#prefix' => '<div class="product-teaser-img">',
          '#suffix' => '</div>',
          '#theme' => 'image_style',
          '#style_name' => 'product_sm',
          '#uri' => $uri,
          '#weight' => -1,
        ];
      }

      // Certificazioni
      if (!$tech->get('field_ref_cert')->isEmpty()){
        $values = $tech->get('field_ref_cert')->getValue();
        foreach ($values as $key => $value) {
          $tid = $value['target_id'];
          $tids[$tid] = $tid;
        }

        // EPD
        if (isset($tids['9'])){
          $img = '<img src="' . $path . 'epd.jpg"/>';
          $head['cert']['epd'] = [
            '#prefix' => '<span class="teaser-cert teaser-cert-epd">',
            '#suffix' => '</span>',
            '#markup' => $img,
          ];
        }

        // Ecolabel
        if (isset($tids['10'])){
          $img = '<img src="' . $path . 'ecolabel-n-reg-vertical.jpg"/>';
          $head['cert']['ecolabel'] = [
            '#prefix' => '<span class="teaser-cert teaser-cert-ecolabel">',
            '#suffix' => '</span>',
            '#markup' => $img,
          ];
        }

        // CAM
        if (isset($tids['130'])){
          $img = '<img src="' . $path . 'cam.svg"/>';
          $head['cert']['cam'] = [
            '#prefix' => '<span class="teaser-cert teaser-cert-cam">',
            '#suffix' => '</span>',
            '#markup' => $img,
          ];  
        }
      }

      if (isset($head['cert'])){
        $head['cert']['#prefix'] = '<span class="wrapper-teaser-product-certs">';
        $head['cert']['#suffix'] = '</span>';
        $this->vars['content']['img_head'] = $head;  
      }

      if (!$tech->get('field_code')->isEmpty()){
        $code = $tech->get('field_code')->first()->getValue();
        $code = $code['value'];
        $this->vars['content']['code']['#markup'] = '<span class="product-teaser-code">' . $code . '</span>';
      }

      // Titolo
      $title = $tech->getTitle();
      $this->vars['content']['title'] = [
        '#prefix' => '<h4 class="margin-v-0">',
        '#suffix' => '</h4>',
        '#markup' => $title,
      ];

      if (!$tech->get('field_subtitle')->isEmpty()){
        $subtitle = $tech->get('field_subtitle')->getValue();
        $subtitle = $subtitle[0]['value'];
        $this->vars['content']['subtitle']['#markup'] = ucfirst($subtitle);
      }
    }
 
    // URL Localized (siccome è possibile che la traduzione del nodo non esista)
    $url = Url::fromUri('entity:node/' . $this->vars['node']->id());
    $this->vars['url'] = $url->toString();

    //$this->addDebugInfo();
  }

  private function addDebugInfo(){
    $this->vars['content']['debug'] = [
      '#prefix' => '<div class="product-debug">',
      '#suffix' => '</div>',
    ];
    //if (isset($this->vars['content']['field_ref_solution'])){
    //  $this->vars['content']['debug']['solution'] = $this->vars['content']['field_ref_solution'];  
    //}
    //if (isset($this->vars['content']['field_ref_solution'])){
    //  $this->vars['content']['debug']['solution'] = $this->vars['content']['field_ref_solution'];  
    //}
    //if (isset($this->vars['content']['field_ref_family'])){
    //  $this->vars['content']['debug']['field_ref_family'] = $this->vars['content']['field_ref_family'];  
    //}
    //if (isset($this->vars['content']['field_ref_where'])){
    //  $this->vars['content']['debug']['field_ref_where'] = $this->vars['content']['field_ref_where'];  
    //}
    //if (isset($this->vars['content']['field_ref_system'])){
    //  $this->vars['content']['debug']['field_ref_system'] = $this->vars['content']['field_ref_system'];  
    //}
    //if (isset($this->vars['content']['field_color_code'])){
    //  $this->vars['content']['debug']['field_color_code'] = $this->vars['content']['field_color_code'];  
    //}
    //if (isset($this->vars['content']['field_color_custom'])){
    //  $this->vars['content']['debug']['field_color_custom'] = $this->vars['content']['field_color_custom'];  
    //}
  }

  private function loadTech(){
    $node = $this->vars['node']; 
    $this->vars['tech'] = false;

    if (!$node->get('field_ref_tech')->isEmpty()){
      $values = $node->get('field_ref_tech')->getValue();
      $nid = $values[0]['target_id'];

      $et = $this->entityTypeManager;
      $tech = $et->getStorage('node')->load($nid);

      if ($tech){
        // Se esiste la traduzione della scheda tecnica
        if ($tech->getTranslationStatus($this->cLangId)){
          $tech = $tech->getTranslation($this->cLangId);
        }

        $this->vars['tech'] = $tech;

        // Type Id
        if (!$tech->get('field_ref_type')->isEmpty()){
          $values = $tech->get('field_ref_type')->getValue();
          $type_id = $values['0']['target_id'];
          $this->vars['tech_type_id'] = $type_id;
        }
      }      
    }
  }

  private function addWebform(){
    $node = $this->vars['node'];

    $description = $node->getTitle();
    if ($this->mainCode){
      $description .= ' <span class="small">(<span id="subject-code">' . $this->mainCode . '</span>)</span>';
    }

    $this->vars['content']['webform'] = [
      '#theme' => 'magic_form',
      '#subject' => [
        '#markup' => t('on', [], ['context' => 'contact']) . ' ' . $description,
      ],
      '#webform' => $this->getWebForm(),
      '#id' => 'magic-form-product',
    ];
  }

  private function getWebForm(){
    $node = $this->vars['node'];

    return [
      '#type' => 'webform',
      '#webform' => 'contact',
      '#default_data' => [
        'info' => 'Scheda prodotto: ' . $node->getTitle(),
        'code' => $this->pCode,
      ],
    ];
  }

  private function addPagination(){
    $node = $this->vars['node'];

    // Family
    if (!$node->get('field_ref_family')->isEmpty()){
      $value = $node->get('field_ref_family')->getValue();
      $family_nid = $value[0]['target_id'];

      $options = [
        //'type' => 'family',
        'family_nid' => $family_nid,
      ];

      $this->pagination = new Pagination($node->id(), $options);
      $data = $this->pagination->getData();
      if ($data){

        $prev = $data['prev'];
        $next = $data['next'];

        $build = [
          '#theme' => 'pagination_arrow',
          '#prev_id' => $prev['nid'],
          '#prev_url' => $prev['url']->toString(),
          '#prev_title' => $prev['node']->getTitle(),
          '#next_id' => $next['nid'],
          '#next_url' => $next['url']->toString(),
          '#next_title' => $next['node']->getTitle(),
        ];

        $this->vars['content']['pagination'] = $build;
      }
    }
  }

  private function addRelated(){
    if ($this->pagination){
      $node = $this->vars['node'];

      $related_nids = $this->pagination->getNidList();
    
      if ($related_nids){

        $et = $this->entityTypeManager;
        $related = $et->getStorage('node')->loadMultiple($related_nids);
        $build = $et->getViewBuilder('node')->viewMultiple($related, 'teaser');

        $slider = [
          '#theme' => 'lightslider',
          '#content' => $build,
          '#lsid' => 'related',
        ];

        $options = array(
          'item' =>  4,
          'mode' => 'slide',
          //'loop' => true,
          'slideMargin' => 0,
          'galleryMargin' => 0,
          'slideMove' => 4,
          'slideEndAnimation' => false,
          'auto' => false,
          'speed' => 2000,
          'pause' => 10000,
          //'controls' => false,
          'prevHtml' => '<i class="material-icons">keyboard_arrow_left</i>',
          'nextHtml' => '<i class="material-icons">keyboard_arrow_right</i>',
          'responsive' => array(
            array(
              'breakpoint' => 1192,
              'settings' => array(
                'item' => 3,
                'slideMove' => 3,
              ),
            ),
            array(
              'breakpoint' => 768,
              'settings' => array(
                'item' => 2,
                'slideMove' => 2,
                //'pager' => false,
              ),
            ),
          ),
        );

        // Unfortunatily is not working in preprocess function
        $slider['#attached']['drupalSettings']['lightslider']['related'] = $options;

        $data = [
          '#theme' => 'related_products',
          '#content' => $slider,
          '#title' => [
            '#markup' => t('Related products for') . ' ' . $this->family->getTitle(),
          ],
        ];

        $this->vars['content']['related'] = $data;
      }  
    }
  }
}
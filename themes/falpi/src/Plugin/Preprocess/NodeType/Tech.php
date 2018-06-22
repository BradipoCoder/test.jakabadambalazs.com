<?php

namespace Drupal\falpi\Plugin\Preprocess\NodeType;

use Drupal\falpi\Utils\Helper;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\Element;
use Drupal\file\Entity\File;
use Drupal\views\Views;

/**
 * Preprocess Node Tech
 */
class Tech extends NodeType{

  protected $entityTypeManager;
  protected $dateFormatter;
  protected $entityQuery;

  protected $productNid = false;
  protected $product = false;

  function __construct(array &$vars, $hook, array $info) {
    parent::__construct($vars, $hook, $info);

    // This is a tricks to use services here
    $this->entityTypeManager = \Drupal::entityTypeManager();
    // Try to use also \Drupal::service('id')
    $this->dateFormatter = \Drupal::service('date.formatter');

    $this->entityQuery = \Drupal::service('entity.query');

    // Set product Nid and Product
    $this->setProductReference();
  }

  public function preprocess(){
    
    $this->imageFromUrl();
    //$this->alterTitleReg();

    if ($this->vars['view_mode'] == 'full' || $this->vars['view_mode'] == 'pdf'){
      $this->addHeader();
      $this->addFabrCert();
      $this->orderCert();
      $this->addFooter();
      $this->falpiProduct();
      $this->alterCodeByVariant();
      $this->addAdminLink();
      $this->addCertRows();
      $this->addEditDate();
      $this->addImgMargin();
      $this->spacingClass();
    }

    if ($this->vars['view_mode'] == 'pdf'){
      $this->absoluteImage();
    }
  }

  private function imageFromUrl(){
    $node = $this->vars['node']; 
    if (!$node->get('field_url_img')->isEmpty()){
      $values = $node->get('field_url_img')->getValue();
      if (isset($values[0]['uri'])){
        $uri = $values[0]['uri'];
        $this->vars['content']['image'] = array(
          '#markup' => '<img src="' . $uri . '" class="img-responsive"/>',
        );
      }
    }
  }

  private function absoluteImage(){
    $node = $this->vars['node'];
    if (!$node->get('field_imgs')->isEmpty()){
      $values = $node->get('field_imgs')->getValue();

      $this->vars['content']['field_imgs'] = [];
      foreach ($values as $n => $value) {
        $fid = $value['target_id'];
        $file = File::load($fid);
        $uri = $file->getFileUri();
        $url = URL::fromUri(file_create_url($uri), ['absolute' => true]);
        $src = $url->toString();
        $this->vars['content']['field_imgs'][$n] = [
          '#markup' => '<img src="' . $src . '"/>',
        ];
      }

    }
  }

  private function addImgMargin(){
    $node = $this->vars['node'];
    
    $scale = 10;
    $margins = [];

    if (!$node->get('field_img_margin_left')->isEmpty()){
      $margin = $node->get('field_img_margin_left')->first()->getValue();
      if ($margin['value'] !== 0){
        $margins['left'] = intval($margin['value']) * $scale;
      }
    }

    if (!$node->get('field_img_margin_right')->isEmpty()){
      $margin = $node->get('field_img_margin_right')->first()->getValue();
      if ($margin['value'] !== 0){
        $margins['right'] = intval($margin['value']) * $scale;
      }
    }

    if (!$node->get('field_img_margin_top')->isEmpty()){
      $margin = $node->get('field_img_margin_top')->first()->getValue();
      if ($margin['value'] !== 0){
        $margins['top'] = intval($margin['value']) * $scale;
      }
    }

    $bottom = false;
    if (!$node->get('field_img_margin_bottom')->isEmpty()){
      $margin = $node->get('field_img_margin_bottom')->first()->getValue();
      if ($margin['value'] !== 0){
        $bottom = 'margin-bottom:' . (intval($margin['value']) * $scale) . 'px;';
      }
    }

    $style = '';
    if (!empty($margins)){
      foreach ($margins as $key => $margin) {
        $style .= 'margin-' . $key . ': ' . $margin . 'px; ';
      }
      $this->vars['content']['img_style']['#markup'] = $style;
    }

    if ($bottom){
      $this->vars['content']['wrapper_img_style']['#markup'] = $bottom;  
    }    
  }

  private function addFabrCert(){
    global $base_url;
    $content = [
      'path' => [
        '#markup' => $base_url . '/' . drupal_get_path('theme', 'falpi') . '/img',
      ],
    ];

    $this->vars['content']['fabr_cert'] = [
      '#theme' => 'fabr_certification',
      '#content' => $content,
    ];  
  }

  private function addHeader(){
    global $base_url;
    $path = $base_url . '/' . drupal_get_path('theme', 'falpi') . '/img/';
    
    $head = [
      'logo' => [
        '#prefix' => '<div class="wrapper-tech-logo">',
        '#suffix' => '</div>',
        '#markup' => '<img src="' . $path . 'falpi-logo.svg" class="img-responsive"/>',
      ],
    ];

    $node = $this->vars['node'];

    // Certificazioni
    if (!$node->get('field_ref_cert')->isEmpty()){
      $values = $node->get('field_ref_cert')->getValue();
      foreach ($values as $key => $value) {
        $tid = $value['target_id'];
        $tids[$tid] = $tid;
      }

      // CAM
      if (isset($tids[130])){
        $head['cert']['cam'] = [
          '#prefix' => '<div class="wrapper-cert-logo wrapper-cert-logo-cam">',
          '#suffix' => '</div>',
          '#markup' => '<img src="' . $path . 'cam.svg" class="img-responsive"/>',
        ];  
      }

      // Ecolabel
      if (isset($tids['10'])){
        $head['cert']['ecolabel'] = [
          '#prefix' => '<div class="wrapper-cert-logo wrapper-cert-logo-ecolabel">',
          '#suffix' => '</div>',
          '#markup' => '<img src="' . $path . 'ecolabel-n-reg-vertical.jpg" class="img-responsive"/>',
        ];
      }

      // EPD
      if (isset($tids['9'])){
        $head['cert']['epd'] = [
          '#prefix' => '<div class="wrapper-cert-logo wrapper-cert-logo-epd">',
          '#suffix' => '</div>',
          '#markup' => '<img src="' . $path . 'epd.jpg" class="img-responsive"/>',
        ];
      }
    }

    if (isset($head['cert'])){
      $head['cert']['#prefix'] = '<div class="wrapper-head-certs">';
      $head['cert']['#suffix'] = '</div>';  
    }

    $this->vars['content']['head'] = $head;
  }

  private function alterTitleReg(){
    $needle = '&reg;';
    $node = $this->vars['node'];

    if (!$node->get('title')->isEmpty()){
      $values = $node->get('title')->getValue();
      $title = $values[0]['value'];

      $title = str_replace('®', '<sup class="copyright">®</sup>', $title);

      $this->vars['content']['my_title'] = $title . 'luca';
    }
  }

  private function falpiProduct(){
    $node = $this->vars['node'];

    $this->vars['falpi_product'] = false;
    if (!$node->get('field_falpi_product')->isEmpty()){ 
      $values = $node->get('field_falpi_product')->getValue();
      if ($values[0]['value']){
        $this->vars['falpi_product'] = true;
      }
    }
  }

  private function alterCodeByVariant(){
    $node = $this->vars['node'];

    // Codice prodotto
    $code = false;
    if (!$node->get('field_code')->isEmpty()){
      $code = $node->get('field_code')->first()->getValue();
      $code = $code['value'];
    }

    // Esiste una variante con lo stesso codice del prodotto
    $exist = false;

    // Codice varianti
    if ($code){
      $vcode = false;
      if (!$node->get('field_ref_variant')->isEmpty()){
        $variants = $node->get('field_ref_variant')->referencedEntities();
        foreach ($variants as $nid => $variant) {
          if (!$variant->get('field_code')->isEmpty()){
            $vcode = $variant->get('field_code')->first()->getValue();
            $vcode = $vcode['value'];

            if ($vcode == $code){
              $exist = true;
            }
          }
        }
      }  
    }
    
    $this->vars['show_product_code'] = true;
    if ($exist){
      $this->vars['show_product_code'] = false;
    }
  }

  /**
   * Ordino le certificazioni in base al peso che hanno in tassonomia
   */
  private function orderCert(){
    $node = $this->vars['node'];

    if (!$node->get('field_ref_cert')->isEmpty()){
      $terms = $node->get('field_ref_cert')->referencedEntities();

      $this->vars['content']['field_ref_cert']['#attached']['library'][] = 'sameh/sameh';

      foreach ($terms as $n => $term) {
        $tid = $term->id();
        $weight = $term->getWeight();
        $this->vars['content']['field_ref_cert'][$n]['#weight'] = $weight;
      }
    }
  }

  private function addAdminLink(){
    $node = $this->vars['node'];

    $data['pdf']['#prefix'] = '<div class="btn-group">';
    $data['pdf']['#suffix'] = '</div>';

    // Print
    $url = Url::fromRoute('cpdf.pdf_controller_make',[
      'nid' => $node->id(),
      'inline' => 'true',
    ]);
    $opt = [
      'attributes' => [
        'class' => [
          'btn', 'btn-primary',
        ],
        'target' => '_blank',
      ],
    ];
    $url->setOptions($opt);
    $data['pdf']['view'] = Link::fromTextAndUrl('PDF', $url)->toRenderable();

    $url = Url::fromRoute('cpdf.pdf_controller_make',[
      'nid' => $node->id(),
      'inline' => 'false',
    ]);
    $opt = [
      'attributes' => [
        'class' => [
          'btn', 'btn-primary', 'btn-download',
        ],
      ],
    ];
    $url->setOptions($opt);
    $markup = Markup::create('<i class="material-icons">file_download</i>');
    $data['pdf']['download'] = Link::fromTextAndUrl($markup, $url)->toRenderable();

    // Debug
    // entity_print.view.debug
    $url = Url::fromRoute('cpdf.pdf_controller_debug',[
      'nid' => $node->id(),  
    ]);
    $opt = [
      'attributes' => [
        'class' => [
          'btn', 'btn-default',
        ],
        'target' => '_blank',
      ],
    ];
    $url->setOptions($opt);
    //$data['debug'] = Link::fromTextAndUrl('Debug PDF', $url)->toRenderable();

    // Duplica
    // entity_print.view.debug
    $url = Url::fromRoute('techclone.clone_controller_clone',[
      'nid' => $node->id(),  
    ]);
    $opt = [
      'attributes' => [
        'class' => [
          'btn', 'btn-default',
        ],
      ],
    ];
    $url->setOptions($opt);
    $data['clone'] = Link::fromTextAndUrl('Duplica', $url)->toRenderable();

    // Guarda prodotto
    if ($this->productNid){
      $product = $this->product;
      $url = $product->toUrl();
      $opt = [
        'attributes' => [
          'class' => [
            'btn', 'btn-default',
          ],
        ],
      ];
      $url->setOptions($opt);
      $data['product'] = Link::fromTextAndUrl('Prodotto', $url)->toRenderable();
    }

    $data['#prefix'] = '<div class="wrapper-tech-links margin-b-05">';
    $data['#suffix'] = '</div>';

    $this->vars['content']['print_links'] = $data;
  }

  /**
   * Divido le certificazioni in righe, senza usare il sameh
   * @return [type] [description]
   */
  private function addCertRows(){
    $node = $this->vars['node'];

    if (!$node->get('field_ref_cert')->isEmpty()){
      $terms = $node->get('field_ref_cert')->referencedEntities();

      // Rimuovo il termine CAM (già visualizzato nell'header)
      foreach ($terms as $key => $term) {
        $tid = $term->id();
        if ($tid == 130){
          unset($terms[$key]);
        }
      }

      $vb = $this->entityTypeManager->getViewBuilder('taxonomy_term');

      $this->vars['content']['field_ref_cert'] = [
        '#prefix' => '<div class="wrapper-field-ref-cert">',
        '#suffix' => '</div>',
      ];

      $split = array_chunk($terms, 3);
      foreach ($split as $n => $row) {
        $data[$n] = [
          '#prefix' => '<div class="field--name-field-ref-cert">',
          '#suffix' => '</div>',
        ];
        foreach ($row as $key => $term) {
          $build = $vb->view($term, 'default');
          $data[$n][$key] = [
            '#prefix' => '<div class="field--item">',
            '#suffix' => '</div>',
            'term' => $build,
          ];
        }
      }
      $this->vars['content']['field_ref_cert']['data'] = $data;
    }
  }

  private function addEditDate(){
    $node = $this->vars['node'];
    // Data di modifica
    $edit_timestamp = $node->getChangedTime();
    $date = $this->dateFormatter->format($edit_timestamp, 'plain_date');
    $this->vars['content']['edit_date']['#markup'] = $date;
  }

  private function addFooter(){
    $node = $this->vars['node'];

    // Certificazioni
    if (!$node->get('field_ref_cert')->isEmpty()){
      $values = $node->get('field_ref_cert')->getValue();
      foreach ($values as $key => $value) {
        $tid = $value['target_id'];
        $tids[$tid] = $tid;
      }
      if (isset($tids[130])){
        $this->vars['content']['cam']['#markup'] = '<br/>' . t('The product meets the requirements of MD 18/10/2016 - standard CAM ');  
      }
    }
  }

  private function spacingClass(){
    $node = $this->vars['node'];

    // Spacing
    if (!$node->get('field_ref_space')->isEmpty()){
      $value = $node->get('field_ref_space')->first()->getValue();
      $tid = $value['target_id'];

      switch ($tid) {
        case '79':
          // Medium
          $class = 'spacing-m';
          break;

        case '80':
          // Medium
          $class = 'spacing-l';
          break;
        
        default:
          # code...
          break;
      }

      $this->vars['content']['spacing_class']['#markup'] = $class;
    }
  }

  private function setProductReference(){
    $tech = $this->vars['node'];
    $tech_nid = $tech->id();

    $eq = $this->entityQuery;

    $query = $eq->get('node');
    $query->condition('type', 'product');
    $query->condition('field_ref_tech', $tech_nid);
    $nids = $query->execute();
    
    // #todo: is working?
    if ($nids){
      $first = reset($nids);
      $this->productNid = $first;

      $et = $this->entityTypeManager;
      $product = $et->getStorage('node')->load($first);
      $this->product = $product;  
    }
  }
}
<?php
/**
 * @file
 * Contains \Drupal\archive\Data\LinkTeaserData.
 */
 
namespace Drupal\archive\Data;

/**
 * Elenco di link utilizzati nelle pagine teaser
 */
class LinkTeaserData{
 
  /**
   * Link sezione Prodotti
   */
  protected $products = [
    'textile' => 1384,
    'tool' => 1385,
    'cart' => 1386,
    'eco' => 1390,
  ];

  /**
   * Link sezione Sistemi
   */
  protected $systems = [
    'sweeping' => 1387,
    'washing' => 1388,
    'glasses' => 1389,
    'hospital' => 1391,
    'hotel' => 1392,
    'informatic' => 1880,
  ];

  /**
   * Link sezione Tessili
   */
  protected $textile = [
    0 => [
      'name' => 'All textiles',
      'query' => [
        'type' => 95,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Ecolabel textiles',
      'query' => [
        'type' => 95,
        'cert' => 10,
      ],
      'count' => '160',
    ],
    2 => [
      'name' => 'For sweeping',
      'query' => [
        'type' => 95,
        'system' => 165,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'For washing',
      'query' => [
        'type' => 95,
        'system' => 160,
      ],
      'count' => true,
    ],
    4 => [
      'name' => 'Light range',
      'query' => [
        'type' => 95,
        'solution' => 184,
      ],
      'count' => true,
    ],
    //5 => [
    //  'name' => 'Yarn and fabrics',
    //  'query' => [
    //    'type' => 95,
    //  ],
    //  'count' => true,
    //],
  ];

   /**
   * Link sezione Tool
   */
  protected $tool = [
    0 => [
      'name' => 'All tools',
      'query' => [
        'type' => 96,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Handles and frames',
      'query' => [
        'type' => 96,
        'cat' => 141,
      ],
      'count' => true,
    ],
    2 => [
      'name' => 'Windows range',
      'query' => [
        'type' => 96,
        'solution' => 158,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'Buckets and lids',
      'query' => [
        'cat' => 142,
      ],
      'count' => true,
    ],
    4 => [
      'name' => 'Accessories',
      'query' => [
        'type' => 97,
      ],
      'count' => true,
    ],
  ];

  /**
   * Link sezione Carrelli
   */
  protected $cart = [
    0 => [
      'name' => 'All trolleys',
      'query' => [
        'type' => 94,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'EPD trolleys',
      'query' => [
        'type' => 94,
        'cert' => 9,
      ],
      'count' => '46',
    ],
    2 => [
      'name' => 'Closed trolleys',
      'query' => [
        'type' => 94,
        'cat' => 192,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'Hospital trolleys',
      'query' => [
        'type' => 94,
        'cat' => 190,
      ],
      'count' => true,
    ],
    4 => [
      'name' => 'Hotel trolleys',
      'query' => [
        'type' => 94,
        'cat' => 191,
      ],
      'count' => true,
    ],
    5 => [
      'name' => 'Mop and service trolleys',
      'query' => [
        'type' => 94,
        'cat' => 189,
      ],
      'count' => true,
    ],
  ];

  /**
   * Link sezione Eco Friendly
   */
  protected $eco = [
    0 => [
      'name' => 'GPP compliant',
      'query' => [
        'cert' => 130,
      ],
      'small_circle' => true,
      'count' => '100%',
    ],
    1 => [
      'name' => 'Ecolabel textiles',
      'query' => [
        'type' => 95,
        'cert' => 10,
      ],
      'small_circle' => true,
      'count' => '160',
    ],
    2 => [
      'name' => 'EPD trolleys',
      'query' => [
        'type' => 94,
        'cert' => 9,
      ],
      'small_circle' => true,
      'count' => '46',
    ],
  ];

  /**
   * Link sezione Sweeping
   */
  protected $sweeping = [
    0 => [
      'name' => 'All solutions',
      'query' => [
        'system' => 165,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Sweeping',
      'query' => [
        'system' => 171,
      ],
      'count' => true,
    ],
    2 => [
      'name' => 'Damp sweeping',
      'query' => [
        'system' => 167,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'Surface dusting',
      'query' => [
        'system' => 173,
      ],
      'count' => true,
    ],
  ];

  /**
   * Link sezione Washing
   */
  protected $washing = [
    0 => [
      'name' => 'All solutions',
      'query' => [
        'system' => 160,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Mop washing',
      'query' => [
        'system' => 164,
      ],
      'count' => true,
    ],
    2 => [
      'name' => 'Flat washing',
      'query' => [
        'system' => 172,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'Rapid flat washing',
      'query' => [
        'system' => 161,
      ],
      'count' => true,
    ],
    4 => [
      'name' => 'Pre-impregnated mops',
      'query' => [
        'system' => 162,
      ],
      'count' => true,
    ],
  ];

  /**
   * Link sezione Pulizia vetri
   */
  protected $glasses = [
    0 => [
      'name' => 'All solutions',
      'query' => [
        'system' => 163,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Bill system',
      'query' => [
        'solution' => 159,
      ],
      'count' => true,
    ],
    2 => [
      'name' => 'Squeegees',
      'query' => [
        'cat' => 143,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'Sleeves ',
      'query' => [
        'cat' => 146,
      ],
      'count' => true,
    ],
  ];

  /**
   * Link sezione Hospital
   */
  protected $hospital = [
    0 => [
      'name' => 'Rapid system',
      'query' => [
        'solution' => 156,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Microrapid system',
      'query' => [
        'solution' => 157,
      ],
      'count' => true,
    ],
    2 => [
      'name' => 'Clean rooms',
      'query' => [
        'solution' => 178,
      ],
      'count' => true,
    ],
    3 => [
      'name' => 'Disposable kits',
      'query' => [
        'solution' => 185,
      ],
      'count' => true,
    ],
  ];

  /**
   * Link sezione Hotel
   */
  protected $hotel = [
    0 => [
      'name' => 'SOlight trolleys',
      'query' => [
        'type' => 94,
        'family' => 1622,
      ],
      'count' => true,
    ],
    1 => [
      'name' => 'Configure your trolley',
      'path' => 'http://configuratore.falpi.com/app/housekeeping_ita/',
      'localized' => [
        'en' => 'http://configuratore.falpi.com/app/housekeeping_eng/',
      ],
      'attributes' => 'target="_blank"',
    ],
  ];

  /**
   * Link sezione Sistemi informatici
   */
  protected $informatic = [
    0 => [
      'name' => 'Catalab',
      'nid' => '1881',
    ],
    1 => [
      'name' => 'Configurator',
      'nid' => '1882',
    ],
    2 => [
      'name' => 'CollegaMe',
      'nid' => '1883',
    ],
  ];

  public function getLinkList(){
    $list = [
      'products',
      'systems',
      'cart',
      'textile',
      'tool',
      'eco',
      'sweeping',
      'washing',
      'glasses',
      'hospital',
      'hotel',
      'informatic',
    ];

    // Loop all'interno di tutti i link per tradurre i testi
    foreach ($list as $key => $name) {
      $group = $this->$name;

      $data[$name] = $group;

      foreach ($group as $k => $item) {
        if (isset($item['name'])){
          $data[$name][$k]['name'] = t($item['name'], [], ['context' => 'page:product:system']);
        }
      }
    }

    return $data;
  }
}

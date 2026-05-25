<?php

declare(strict_types=1);

return array (
  'common' => 
  array (
    'RATE_POINTS' => '4',
    'PERCENT_POINTS' => '4',
    'DEFAULT_CONNECTION' => 'common',
    'DEFAULT_COUNT_METHOD' => 'CALC_FOUND_ROWS',
    'CONNECTIONS' => 
    array (
      'common' => 'common',
      'counter' => 'counter',
    ),
  ),
  'entity' => 
  array (
    'common\\member' => 
    array (
      'encrypt_id_key' => 'drld',
    ),
    'common\\file_data' => 
    array (
      'ALLOW_INFO_FILE' => '1',
      'ALLOW_CLEAR_INFO' => '1',
      'INFO_FILE_PATH' => '{TEMP}/file_data/file_info/',
      'file_store' => '{PROJECT}/data/file_store/',
      'file_ext' => 'df',
      'path_with_connection' => '',
      'encrypt_id_key' => 'jhf7Ahh6kt',
    ),
    'common\\image' => 
    array (
      'ALLOW_QUICK_NAIL' => '1',
      'TEMPLATE_PATH' => '{PROJECT}/data/special_templates/show_image.tpl',
      'img' => 
      array (
        'urn_prefix' => '/img_',
        'urn_suffix' => '.img',
      ),
      'nail' => 
      array (
        'urn_prefix' => '/nail_',
        'urn_suffix' => '_{width}-{height}.img',
      ),
      'blowup' => 
      array (
        'urn_prefix' => '/blowup/id-',
        'urn_suffix' => '.html',
      ),
      'signature' => 
      array (
        'position' => 'bottom',
      ),
      'css_class' => 
      array (
        'adv_box' => 'adv_img',
        'top_sign' => 'top_sign',
        'bot_sign' => 'bot_sign',
        'blowup1' => 'blowup1',
        'blowup2' => 'blowup2',
        'link' => 'link',
      ),
    ),
    'common\\page' => 
    array (
      'UPDATE_PERIOD' => '43200',
      'CRAWLER_UA' => 'OUR CRAWLER - 1DR5n89S4 - X',
      'TAG_RATES' => 
      array (
        'h1' => '60',
        'h2' => '50',
        'h3' => '40',
        'h4' => '30',
        'h5' => '20',
        'h6' => '10',
      ),
    ),
    'common\\page_application' => 
    array (
      'MAIN_DOMAIN' => 
      array (
        'www_global' => 'www.copayco.int',
      ),
      'ALIAS_DOMAIN' => 
      array (
        'www_global' => 
        array (
          0 => 'copayco.int',
        ),
      ),
    ),
    'common\\country' => 
    array (
      'ADD_TBL_FIELDS' => 
      array (
        0 => 'id_country',
        1 => 'full_name',
        2 => 'short_name',
      ),
      'COPY_OTHERS_DB' => 
      array (
        0 => 'credit',
        1 => 'exchange',
        2 => 'eboard',
      ),
    ),
    'mysql\\counter\\counter_key_tmp' => 
    array (
      'COUNTER_TYPE' => 'full',
    ),
  ),
);

<?php

declare(strict_types=1);

/*
 * Tags array
 */
return [
  '/a' =>
  [
    'tag' => '</a>',
  ],
  'br' =>
  [
    'tag' => '<br/>',
  ],
  '/b' =>
  [
    'tag' => '</b>',
  ],
  'a-contact' =>
  [
    'tag' => '<a href="{service|tab:getURI:/contact_us.html}">',
    'link' => '/a',
    'isFunc' => true,
  ],
  'b' =>
  [
    'tag' => '<b>',
    'link' => '/b',
  ],
  'combi_part' =>
  [
    'tag' => '{service|translation:getCombiPart:}',
    'isFunc' => true,
  ],
  'fc-size' =>
  [
    'tag' => '{\fan\app\frontend\main\contact_us:getMaxFileSize:}',
    'isFunc' => true,
  ],
];

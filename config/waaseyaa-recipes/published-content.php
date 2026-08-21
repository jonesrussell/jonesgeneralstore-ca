<?php

declare(strict_types=1);

return array (
  0 => 
  array (
    'bundle' => 'page',
    'fields' => 
    array (
      0 => 'title',
      1 => 'slug',
      2 => 'summary',
      3 => 'body',
      4 => 'status',
      5 => 'changed',
    ),
    'listing_id' => 'page_index',
    'page_size' => 24,
    'access_ops' => 
    array (
      0 => 'view',
    ),
    'detail_route' => '/{slug}',
    'index_route' => '/',
    'schema_type' => 'WebPage',
  ),
);

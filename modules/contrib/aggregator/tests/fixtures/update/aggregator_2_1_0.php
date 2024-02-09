<?php

/**
 * @file
 * Contains database additions for testing Aggregator 2.1.0 upgrade paths.
 */

use Drupal\Core\Database\Database;

$connection = Database::getConnection();

$connection->schema()->dropTable('aggregator_feed');
$connection->schema()->dropTable('aggregator_item');

$connection->schema()->createTable('aggregator_feed', [
  'fields' => [
    'fid' => [
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ],
    'uuid' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '128',
    ],
    'langcode' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '12',
    ],
    'title' => [
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '255',
    ],
    'url' => [
      'type' => 'varchar',
      'not null' => TRUE,
      'length' => '2048',
    ],
    'refresh' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ],
    'checked' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ],
    'queued' => [
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
    ],
    'link' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '2048',
    ],
    'description' => [
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
    ],
    'image' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '2048',
    ],
    'etag' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '255',
    ],
    'modified' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
    ],
  ],
  'primary key' => [
    'fid',
  ],
  'unique keys' => [
    'aggregator_feed_field__uuid__value' => [
      'uuid',
    ],
  ],
  'indexes' => [
    'aggregator_feed_field__title' => [
      [
        'title',
        '191',
      ],
    ],
    'aggregator_feed_field__url' => [
      [
        'url',
        '191',
      ],
    ],
    'aggregator_feed_field__refresh__value' => [
      'refresh',
    ],
    'aggregator_feed_field__queued' => [
      'queued',
    ],
  ],
  'mysql_character_set' => 'utf8mb4',
]);

$connection->schema()->createTable('aggregator_item', [
  'fields' => [
    'iid' => [
      'type' => 'serial',
      'not null' => TRUE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ],
    'langcode' => [
      'type' => 'varchar_ascii',
      'not null' => TRUE,
      'length' => '12',
    ],
    'fid' => [
      'type' => 'int',
      'not null' => FALSE,
      'size' => 'normal',
      'unsigned' => TRUE,
    ],
    'title' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '255',
    ],
    'link' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '2048',
    ],
    'author' => [
      'type' => 'varchar',
      'not null' => FALSE,
      'length' => '255',
    ],
    'description' => [
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
    ],
    'timestamp' => [
      'type' => 'int',
      'not null' => TRUE,
      'size' => 'normal',
    ],
    'guid' => [
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
    ],
    'uuid' => [
      'type' => 'varchar_ascii',
      'not null' => FALSE,
      'length' => '128',
    ],
  ],
  'primary key' => [
    'iid',
  ],
  'unique keys' => [
    'aggregator_item_field__uuid__value' => [
      'uuid',
    ],
  ],
  'indexes' => [
    'aggregator_item_field__fid__target_id' => [
      'fid',
    ],
    'aggregator_item_field__timestamp' => [
      'timestamp',
    ],
  ],
  'mysql_character_set' => 'utf8mb4',
]);

$connection->delete('config')
  ->condition('name', 'aggregator.settings')
  ->execute();
$connection->delete('config')
  ->condition('name', 'views.view.aggregator_rss_feed')
  ->execute();

$connection->insert('config')
  ->fields([
    'collection',
    'name',
    'data',
  ])
  ->values([
    'collection' => '',
    'name' => 'aggregator.settings',
    'data' => 'a:6:{s:5:"_core";a:1:{s:19:"default_config_hash";s:43:"oqQElnvorzLoHb68o4B3zFkGuMV5_GQEGi7pHfUseVM";}s:7:"fetcher";s:10:"aggregator";s:6:"parser";s:10:"aggregator";s:10:"processors";a:1:{i:0;s:10:"aggregator";}s:5:"items";a:1:{s:6:"expire";i:9676800;}s:6:"source";a:1:{s:8:"list_max";i:3;}}',
  ])
  ->values([
    'collection' => '',
    'name' => 'views.view.aggregator_rss_feed',
    'data' => 'a:11:{s:8:"langcode";s:2:"en";s:6:"status";b:1;s:12:"dependencies";a:2:{s:6:"config";a:1:{i:0;s:45:"core.entity_view_mode.aggregator_item.summary";}s:6:"module";a:2:{i:0;s:10:"aggregator";i:1;s:4:"user";}}s:2:"id";s:19:"aggregator_rss_feed";s:5:"label";s:19:"Aggregator RSS feed";s:6:"module";s:10:"aggregator";s:11:"description";s:0:"";s:3:"tag";s:10:"aggregator";s:10:"base_table";s:15:"aggregator_item";s:10:"base_field";s:3:"iid";s:7:"display";a:2:{s:7:"default";a:6:{s:2:"id";s:7:"default";s:13:"display_title";s:7:"Default";s:14:"display_plugin";s:7:"default";s:8:"position";i:0;s:15:"display_options";a:17:{s:5:"title";s:19:"Aggregator RSS feed";s:6:"fields";a:0:{}s:5:"pager";a:2:{s:4:"type";s:4:"full";s:7:"options";a:7:{s:6:"offset";i:0;s:14:"items_per_page";i:10;s:11:"total_pages";i:0;s:2:"id";i:0;s:4:"tags";a:4:{s:4:"next";s:8:"Next ›";s:8:"previous";s:12:"‹ Previous";s:5:"first";s:8:"« First";s:4:"last";s:7:"Last »";}s:6:"expose";a:7:{s:14:"items_per_page";b:0;s:20:"items_per_page_label";s:14:"Items per page";s:22:"items_per_page_options";s:13:"5, 10, 25, 50";s:26:"items_per_page_options_all";b:0;s:32:"items_per_page_options_all_label";s:7:"- All -";s:6:"offset";b:0;s:12:"offset_label";s:6:"Offset";}s:8:"quantity";i:9;}}s:12:"exposed_form";a:2:{s:4:"type";s:5:"basic";s:7:"options";a:7:{s:13:"submit_button";s:5:"Apply";s:12:"reset_button";b:0;s:18:"reset_button_label";s:5:"Reset";s:19:"exposed_sorts_label";s:7:"Sort by";s:17:"expose_sort_order";b:1;s:14:"sort_asc_label";s:3:"Asc";s:15:"sort_desc_label";s:4:"Desc";}}s:6:"access";a:2:{s:4:"type";s:4:"perm";s:7:"options";a:1:{s:4:"perm";s:17:"access news feeds";}}s:5:"cache";a:2:{s:4:"type";s:3:"tag";s:7:"options";a:0:{}}s:5:"empty";a:0:{}s:5:"sorts";a:1:{s:9:"timestamp";a:13:{s:2:"id";s:9:"timestamp";s:5:"table";s:15:"aggregator_item";s:5:"field";s:9:"timestamp";s:12:"relationship";s:4:"none";s:10:"group_type";s:5:"group";s:11:"admin_label";s:0:"";s:11:"entity_type";s:15:"aggregator_item";s:12:"entity_field";s:9:"timestamp";s:9:"plugin_id";s:4:"date";s:5:"order";s:4:"DESC";s:6:"expose";a:2:{s:5:"label";s:0:"";s:16:"field_identifier";s:0:"";}s:7:"exposed";b:0;s:11:"granularity";s:6:"second";}}s:9:"arguments";a:0:{}s:7:"filters";a:0:{}s:5:"style";a:1:{s:4:"type";s:7:"default";}s:3:"row";a:1:{s:4:"type";s:22:"entity:aggregator_item";}s:5:"query";a:2:{s:4:"type";s:11:"views_query";s:7:"options";a:5:{s:13:"query_comment";s:0:"";s:19:"disable_sql_rewrite";b:0;s:8:"distinct";b:0;s:7:"replica";b:0;s:10:"query_tags";a:0:{}}}s:13:"relationships";a:0:{}s:6:"header";a:0:{}s:6:"footer";a:0:{}s:17:"display_extenders";a:0:{}}s:14:"cache_metadata";a:4:{s:7:"max-age";i:-1;s:8:"contexts";a:4:{i:0;s:26:"languages:language_content";i:1;s:28:"languages:language_interface";i:2;s:14:"url.query_args";i:3;s:16:"user.permissions";}s:4:"tags";a:0:{}s:9:"cacheable";b:0;}}s:10:"feed_items";a:6:{s:2:"id";s:10:"feed_items";s:13:"display_title";s:4:"Feed";s:14:"display_plugin";s:4:"feed";s:8:"position";i:1;s:15:"display_options";a:1:{s:3:"row";a:2:{s:4:"type";s:14:"aggregator_rss";s:7:"options";a:2:{s:12:"relationship";s:4:"none";s:9:"view_mode";s:7:"summary";}}}s:14:"cache_metadata";a:4:{s:7:"max-age";i:-1;s:8:"contexts";a:3:{i:0;s:26:"languages:language_content";i:1;s:28:"languages:language_interface";i:2;s:16:"user.permissions";}s:4:"tags";a:0:{}s:9:"cacheable";b:0;}}}}',
  ])
  ->execute();

$connection->delete('key_value')
  ->condition('collection', 'entity.definitions.installed')
  ->condition('name', 'aggregator_feed.entity_type')
  ->execute();
$connection->delete('key_value')
  ->condition('collection', 'entity.definitions.installed')
  ->condition('name', 'aggregator_feed.field_storage_definitions')
  ->execute();
$connection->delete('key_value')
  ->condition('collection', 'entity.definitions.installed')
  ->condition('name', 'aggregator_item.entity_type')
  ->execute();
$connection->delete('key_value')
  ->condition('collection', 'entity.definitions.installed')
  ->condition('name', 'aggregator_item.field_storage_definitions')
  ->execute();
$connection->delete('key_value')
  ->condition('collection', 'entity.storage_schema.sql')
  ->condition('name', 'aggregator_feed.field_schema_data.hash')
  ->execute();
$connection->delete('key_value')
  ->condition('collection', 'system.schema')
  ->condition('name', 'aggregator')
  ->execute();

$connection->insert('key_value')
  ->fields([
    'collection',
    'name',
    'value',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'aggregator_feed.entity_type',
    'value' => 'O:36:"Drupal\Core\Entity\ContentEntityType":40:{s:5:" * id";s:15:"aggregator_feed";s:8:" * class";s:29:"Drupal\aggregator\Entity\Feed";s:11:" * provider";s:10:"aggregator";s:15:" * static_cache";b:1;s:15:" * render_cache";b:0;s:19:" * persistent_cache";b:1;s:14:" * entity_keys";a:8:{s:2:"id";s:3:"fid";s:5:"label";s:5:"title";s:8:"langcode";s:8:"langcode";s:4:"uuid";s:4:"uuid";s:8:"revision";s:0:"";s:6:"bundle";s:0:"";s:16:"default_langcode";s:16:"default_langcode";s:29:"revision_translation_affected";s:29:"revision_translation_affected";}s:16:" * originalClass";s:29:"Drupal\aggregator\Entity\Feed";s:11:" * handlers";a:7:{s:7:"storage";s:29:"Drupal\aggregator\FeedStorage";s:14:"storage_schema";s:35:"Drupal\aggregator\FeedStorageSchema";s:12:"view_builder";s:33:"Drupal\aggregator\FeedViewBuilder";s:6:"access";s:42:"Drupal\aggregator\FeedAccessControlHandler";s:10:"views_data";s:41:"Drupal\aggregator\AggregatorFeedViewsData";s:4:"form";a:3:{s:7:"default";s:26:"Drupal\aggregator\FeedForm";s:6:"delete";s:37:"Drupal\aggregator\Form\FeedDeleteForm";s:12:"delete_items";s:42:"Drupal\aggregator\Form\FeedItemsDeleteForm";}s:14:"route_provider";a:1:{s:4:"html";s:39:"Drupal\aggregator\FeedHtmlRouteProvider";}}s:19:" * admin_permission";N;s:25:" * permission_granularity";s:11:"entity_type";s:8:" * links";a:3:{s:9:"canonical";s:37:"/aggregator/sources/{aggregator_feed}";s:9:"edit-form";s:47:"/aggregator/sources/{aggregator_feed}/configure";s:11:"delete-form";s:44:"/aggregator/sources/{aggregator_feed}/delete";}s:21:" * bundle_entity_type";N;s:12:" * bundle_of";N;s:15:" * bundle_label";N;s:13:" * base_table";s:15:"aggregator_feed";s:22:" * revision_data_table";N;s:17:" * revision_table";N;s:13:" * data_table";N;s:11:" * internal";b:0;s:15:" * translatable";b:0;s:19:" * show_revision_ui";b:0;s:8:" * label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:15:"Aggregator feed";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:19:" * label_collection";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:16:"Aggregator feeds";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:17:" * label_singular";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:15:"aggregator feed";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:15:" * label_plural";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:16:"aggregator feeds";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:14:" * label_count";a:3:{s:8:"singular";s:22:"@count aggregator feed";s:6:"plural";s:23:"@count aggregator feeds";s:7:"context";N;}s:15:" * uri_callback";N;s:8:" * group";s:7:"content";s:14:" * group_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:7:"Content";s:12:" * arguments";a:0:{}s:10:" * options";a:1:{s:7:"context";s:17:"Entity type group";}}s:22:" * field_ui_base_route";s:25:"aggregator.admin_overview";s:26:" * common_reference_target";b:0;s:22:" * list_cache_contexts";a:0:{}s:18:" * list_cache_tags";a:1:{i:0;s:20:"aggregator_feed_list";}s:14:" * constraints";a:1:{s:26:"EntityUntranslatableFields";N;}s:13:" * additional";a:0:{}s:14:" * _serviceIds";a:0:{}s:18:" * _entityStorages";a:0:{}s:20:" * stringTranslation";N;s:25:" * revision_metadata_keys";a:1:{s:16:"revision_default";s:16:"revision_default";}}',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'aggregator_feed.field_storage_definitions',
    'value' => "a:13:{s:3:\"fid\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:8:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:7:\"Feed ID\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:9:\"read-only\";b:1;s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:30:\"The ID of the aggregator feed.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:3:\"fid\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:18:\"field_item:integer\";s:8:\"settings\";a:6:{s:8:\"unsigned\";b:1;s:4:\"size\";s:6:\"normal\";s:3:\"min\";s:0:\"\";s:3:\"max\";s:0:\"\";s:6:\"prefix\";s:0:\"\";s:6:\"suffix\";s:0:\"\";}}s:18:\" * fieldDefinition\";r:2;}s:7:\" * type\";s:7:\"integer\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:3:\"int\";s:8:\"unsigned\";b:1;s:4:\"size\";s:6:\"normal\";}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:4:\"uuid\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:8:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:4:\"UUID\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:9:\"read-only\";b:1;s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:25:\"The aggregator feed UUID.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:4:\"uuid\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:15:\"field_item:uuid\";s:8:\"settings\";a:3:{s:10:\"max_length\";i:128;s:8:\"is_ascii\";b:1;s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:40;}s:7:\" * type\";s:4:\"uuid\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:13:\"varchar_ascii\";s:6:\"length\";i:128;s:6:\"binary\";b:0;}}s:11:\"unique keys\";a:1:{s:5:\"value\";a:1:{i:0;s:5:\"value\";}}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:8:\"langcode\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:8:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:13:\"Language code\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:7:\"display\";a:2:{s:4:\"view\";a:1:{s:7:\"options\";a:1:{s:6:\"region\";s:6:\"hidden\";}}s:4:\"form\";a:1:{s:7:\"options\";a:2:{s:4:\"type\";s:15:\"language_select\";s:6:\"weight\";i:2;}}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:23:\"The feed language code.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:8:\"langcode\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:19:\"field_item:language\";s:8:\"settings\";a:0:{}}s:18:\" * fieldDefinition\";r:77;}s:7:\" * type\";s:8:\"language\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:2:{s:4:\"type\";s:13:\"varchar_ascii\";s:6:\"length\";i:12;}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:5:\"title\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:10:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:5:\"Title\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:69:\"The name of the feed (or the name of the website providing the feed).\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"required\";b:1;s:7:\"display\";a:1:{s:4:\"form\";a:2:{s:7:\"options\";a:2:{s:4:\"type\";s:16:\"string_textfield\";s:6:\"weight\";i:-5;}s:12:\"configurable\";b:1;}}s:11:\"constraints\";a:1:{s:9:\"FeedTitle\";N;}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:5:\"title\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:17:\"field_item:string\";s:8:\"settings\";a:3:{s:10:\"max_length\";i:255;s:8:\"is_ascii\";b:0;s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:115;}s:7:\" * type\";s:6:\"string\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:7:\"varchar\";s:6:\"length\";i:255;s:6:\"binary\";b:0;}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:3:\"url\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:10:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:3:\"URL\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:36:\"The fully-qualified URL of the feed.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"required\";b:1;s:7:\"display\";a:1:{s:4:\"form\";a:2:{s:7:\"options\";a:2:{s:4:\"type\";s:3:\"uri\";s:6:\"weight\";i:-3;}s:12:\"configurable\";b:1;}}s:11:\"constraints\";a:1:{s:7:\"FeedUrl\";N;}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:3:\"url\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:14:\"field_item:uri\";s:8:\"settings\";a:2:{s:10:\"max_length\";i:2048;s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:158;}s:7:\" * type\";s:3:\"uri\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:7:\"varchar\";s:6:\"length\";i:2048;s:6:\"binary\";b:0;}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:7:\"refresh\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:10:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:15:\"Update interval\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:95:\"The length of time between feed updates. Requires a correctly configured cron maintenance task.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:13:\"default_value\";a:1:{i:0;a:1:{s:5:\"value\";i:3600;}}s:8:\"required\";b:1;s:7:\"display\";a:1:{s:4:\"form\";a:2:{s:7:\"options\";a:2:{s:4:\"type\";s:14:\"options_select\";s:6:\"weight\";i:-2;}s:12:\"configurable\";b:1;}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:7:\"refresh\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:23:\"field_item:list_integer\";s:8:\"settings\";a:3:{s:14:\"allowed_values\";a:16:{i:900;s:6:\"15 min\";i:1800;s:6:\"30 min\";i:3600;s:6:\"1 hour\";i:7200;s:7:\"2 hours\";i:10800;s:7:\"3 hours\";i:21600;s:7:\"6 hours\";i:32400;s:7:\"9 hours\";i:43200;s:8:\"12 hours\";i:64800;s:8:\"18 hours\";i:86400;s:5:\"1 day\";i:172800;s:6:\"2 days\";i:259200;s:6:\"3 days\";i:604800;s:6:\"1 week\";i:1209600;s:7:\"2 weeks\";i:2419200;s:7:\"4 weeks\";i:0;O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:5:\"Never\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}}s:23:\"allowed_values_function\";s:0:\"\";s:8:\"unsigned\";b:1;}}s:18:\" * fieldDefinition\";r:200;}s:7:\" * type\";s:12:\"list_integer\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:1:{s:4:\"type\";s:3:\"int\";}}s:7:\"indexes\";a:1:{s:5:\"value\";a:1:{i:0;s:5:\"value\";}}s:11:\"unique keys\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:7:\"checked\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:9:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:7:\"Checked\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:1:{s:7:\"context\";s:8:\"Examined\";}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:60:\"Last time feed was checked for new items, as Unix timestamp.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:13:\"default_value\";a:1:{i:0;a:1:{s:5:\"value\";i:0;}}s:7:\"display\";a:1:{s:4:\"view\";a:2:{s:7:\"options\";a:3:{s:5:\"label\";s:6:\"inline\";s:4:\"type\";s:13:\"timestamp_ago\";s:6:\"weight\";i:1;}s:12:\"configurable\";b:1;}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:7:\"checked\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:20:\"field_item:timestamp\";s:8:\"settings\";a:0:{}}s:18:\" * fieldDefinition\";r:263;}s:7:\" * type\";s:9:\"timestamp\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:1:{s:4:\"type\";s:3:\"int\";}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:6:\"queued\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:8:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:6:\"Queued\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:60:\"Time when this feed was queued for refresh, 0 if not queued.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:13:\"default_value\";a:1:{i:0;a:1:{s:5:\"value\";i:0;}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:6:\"queued\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:20:\"field_item:timestamp\";s:8:\"settings\";a:0:{}}s:18:\" * fieldDefinition\";r:303;}s:7:\" * type\";s:9:\"timestamp\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:1:{s:4:\"type\";s:3:\"int\";}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:4:\"link\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:8:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:3:\"URL\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:21:\"The link of the feed.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:7:\"display\";a:1:{s:4:\"view\";a:2:{s:7:\"options\";a:2:{s:5:\"label\";s:6:\"inline\";s:6:\"weight\";i:4;}s:12:\"configurable\";b:1;}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:4:\"link\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:14:\"field_item:uri\";s:8:\"settings\";a:2:{s:10:\"max_length\";i:2048;s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:335;}s:7:\" * type\";s:3:\"uri\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:7:\"varchar\";s:6:\"length\";i:2048;s:6:\"binary\";b:0;}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:11:\"description\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:7:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:11:\"Description\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:86:\"The parent website's description that comes from the @description element in the feed.\";s:12:\" * arguments\";a:1:{s:12:\"@description\";s:13:\"<description>\";}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:11:\"description\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:22:\"field_item:string_long\";s:8:\"settings\";a:1:{s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:374;}s:7:\" * type\";s:11:\"string_long\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:2:{s:4:\"type\";s:4:\"text\";s:4:\"size\";s:3:\"big\";}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:5:\"image\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:7:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:5:\"Image\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:31:\"An image representing the feed.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:5:\"image\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:14:\"field_item:uri\";s:8:\"settings\";a:2:{s:10:\"max_length\";i:2048;s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:406;}s:7:\" * type\";s:3:\"uri\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:7:\"varchar\";s:6:\"length\";i:2048;s:6:\"binary\";b:0;}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:4:\"etag\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:7:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:4:\"Etag\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:59:\"Entity tag HTTP response header, used for validating cache.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:4:\"etag\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:17:\"field_item:string\";s:8:\"settings\";a:3:{s:10:\"max_length\";i:255;s:8:\"is_ascii\";b:0;s:14:\"case_sensitive\";b:0;}}s:18:\" * fieldDefinition\";r:439;}s:7:\" * type\";s:6:\"string\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:3:{s:4:\"type\";s:7:\"varchar\";s:6:\"length\";i:255;s:6:\"binary\";b:0;}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}s:8:\"modified\";O:37:\"Drupal\\Core\\Field\\BaseFieldDefinition\":5:{s:13:\" * definition\";a:7:{s:5:\"label\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:8:\"Modified\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:11:\"description\";O:48:\"Drupal\\Core\\StringTranslation\\TranslatableMarkup\":3:{s:9:\" * string\";s:53:\"When the feed was last modified, as a Unix timestamp.\";s:12:\" * arguments\";a:0:{}s:10:\" * options\";a:0:{}}s:8:\"provider\";s:10:\"aggregator\";s:10:\"field_name\";s:8:\"modified\";s:11:\"entity_type\";s:15:\"aggregator_feed\";s:6:\"bundle\";N;s:13:\"initial_value\";N;}s:17:\" * itemDefinition\";O:51:\"Drupal\\Core\\Field\\TypedData\\FieldItemDataDefinition\":2:{s:13:\" * definition\";a:2:{s:4:\"type\";s:20:\"field_item:timestamp\";s:8:\"settings\";a:0:{}}s:18:\" * fieldDefinition\";r:473;}s:7:\" * type\";s:9:\"timestamp\";s:9:\" * schema\";a:4:{s:7:\"columns\";a:1:{s:5:\"value\";a:1:{s:4:\"type\";s:3:\"int\";}}s:11:\"unique keys\";a:0:{}s:7:\"indexes\";a:0:{}s:12:\"foreign keys\";a:0:{}}s:10:\" * indexes\";a:0:{}}}",
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'aggregator_item.entity_type',
    'value' => 'O:36:"Drupal\Core\Entity\ContentEntityType":40:{s:5:" * id";s:15:"aggregator_item";s:8:" * class";s:29:"Drupal\aggregator\Entity\Item";s:11:" * provider";s:10:"aggregator";s:15:" * static_cache";b:1;s:15:" * render_cache";b:0;s:19:" * persistent_cache";b:1;s:14:" * entity_keys";a:7:{s:2:"id";s:3:"iid";s:5:"label";s:5:"title";s:8:"langcode";s:8:"langcode";s:8:"revision";s:0:"";s:6:"bundle";s:0:"";s:16:"default_langcode";s:16:"default_langcode";s:29:"revision_translation_affected";s:29:"revision_translation_affected";}s:16:" * originalClass";s:29:"Drupal\aggregator\Entity\Item";s:11:" * handlers";a:5:{s:7:"storage";s:29:"Drupal\aggregator\ItemStorage";s:14:"storage_schema";s:35:"Drupal\aggregator\ItemStorageSchema";s:12:"view_builder";s:33:"Drupal\aggregator\ItemViewBuilder";s:6:"access";s:42:"Drupal\aggregator\FeedAccessControlHandler";s:10:"views_data";s:41:"Drupal\aggregator\AggregatorItemViewsData";}s:19:" * admin_permission";N;s:25:" * permission_granularity";s:11:"entity_type";s:8:" * links";a:0:{}s:21:" * bundle_entity_type";N;s:12:" * bundle_of";N;s:15:" * bundle_label";N;s:13:" * base_table";s:15:"aggregator_item";s:22:" * revision_data_table";N;s:17:" * revision_table";N;s:13:" * data_table";N;s:11:" * internal";b:0;s:15:" * translatable";b:0;s:19:" * show_revision_ui";b:0;s:8:" * label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:20:"Aggregator feed item";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:19:" * label_collection";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:21:"Aggregator feed items";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:17:" * label_singular";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:20:"aggregator feed item";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:15:" * label_plural";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:21:"aggregator feed items";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:14:" * label_count";a:3:{s:8:"singular";s:27:"@count aggregator feed item";s:6:"plural";s:28:"@count aggregator feed items";s:7:"context";N;}s:15:" * uri_callback";s:39:"Drupal\aggregator\Entity\Item::buildUri";s:8:" * group";s:7:"content";s:14:" * group_label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:7:"Content";s:12:" * arguments";a:0:{}s:10:" * options";a:1:{s:7:"context";s:17:"Entity type group";}}s:22:" * field_ui_base_route";N;s:26:" * common_reference_target";b:0;s:22:" * list_cache_contexts";a:0:{}s:18:" * list_cache_tags";a:1:{i:0;s:20:"aggregator_feed_list";}s:14:" * constraints";a:1:{s:26:"EntityUntranslatableFields";N;}s:13:" * additional";a:0:{}s:14:" * _serviceIds";a:0:{}s:18:" * _entityStorages";a:0:{}s:20:" * stringTranslation";N;s:25:" * revision_metadata_keys";a:1:{s:16:"revision_default";s:16:"revision_default";}}',
  ])
  ->values([
    'collection' => 'entity.definitions.installed',
    'name' => 'aggregator_item.field_storage_definitions',
    'value' => 'a:10:{s:3:"iid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:18:"Aggregator item ID";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:9:"read-only";b:1;s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:24:"The ID of the feed item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:3:"iid";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:18:"field_item:integer";s:8:"settings";a:6:{s:8:"unsigned";b:1;s:4:"size";s:6:"normal";s:3:"min";s:0:"";s:3:"max";s:0:"";s:6:"prefix";s:0:"";s:6:"suffix";s:0:"";}}s:18:" * fieldDefinition";r:2;}s:7:" * type";s:7:"integer";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:3:"int";s:8:"unsigned";b:1;s:4:"size";s:6:"normal";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:8:"langcode";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:13:"Language code";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:7:"display";a:2:{s:4:"view";a:1:{s:7:"options";a:1:{s:6:"region";s:6:"hidden";}}s:4:"form";a:1:{s:7:"options";a:2:{s:4:"type";s:15:"language_select";s:6:"weight";i:2;}}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:28:"The feed item language code.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:8:"langcode";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:19:"field_item:language";s:8:"settings";a:0:{}}s:18:" * fieldDefinition";r:40;}s:7:" * type";s:8:"language";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:2:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:12;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:3:"fid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:9:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:11:"Source feed";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"required";b:1;s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:53:"The aggregator feed entity associated with this item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:7:"display";a:2:{s:4:"view";a:1:{s:7:"options";a:3:{s:5:"label";s:6:"hidden";s:4:"type";s:22:"entity_reference_label";s:6:"weight";i:0;}}s:4:"form";a:2:{s:7:"options";a:1:{s:6:"region";s:6:"hidden";}s:12:"configurable";b:1;}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:3:"fid";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:27:"field_item:entity_reference";s:8:"settings";a:3:{s:11:"target_type";s:15:"aggregator_feed";s:7:"handler";s:7:"default";s:16:"handler_settings";a:0:{}}}s:18:" * fieldDefinition";r:78;}s:7:" * type";s:16:"entity_reference";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:9:"target_id";a:3:{s:11:"description";s:28:"The ID of the target entity.";s:4:"type";s:3:"int";s:8:"unsigned";b:1;}}s:7:"indexes";a:1:{s:9:"target_id";a:1:{i:0;s:9:"target_id";}}s:11:"unique keys";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:5:"title";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:7:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:5:"Title";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:27:"The title of the feed item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:5:"title";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}s:18:" * fieldDefinition";r:125;}s:7:" * type";s:6:"string";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:4:"link";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:4:"Link";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:26:"The link of the feed item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:7:"display";a:1:{s:4:"view";a:2:{s:7:"options";a:1:{s:6:"region";s:6:"hidden";}s:12:"configurable";b:1;}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:4:"link";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:14:"field_item:uri";s:8:"settings";a:2:{s:10:"max_length";i:2048;s:14:"case_sensitive";b:0;}}s:18:" * fieldDefinition";r:159;}s:7:" * type";s:3:"uri";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:2048;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:6:"author";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:6:"Author";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:28:"The author of the feed item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:7:"display";a:1:{s:4:"view";a:2:{s:7:"options";a:2:{s:5:"label";s:6:"hidden";s:6:"weight";i:3;}s:12:"configurable";b:1;}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:6:"author";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:17:"field_item:string";s:8:"settings";a:3:{s:10:"max_length";i:255;s:8:"is_ascii";b:0;s:14:"case_sensitive";b:0;}}s:18:" * fieldDefinition";r:197;}s:7:" * type";s:6:"string";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:7:"varchar";s:6:"length";i:255;s:6:"binary";b:0;}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:11:"description";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:7:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:11:"Description";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:26:"The body of the feed item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:11:"description";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:22:"field_item:string_long";s:8:"settings";a:1:{s:14:"case_sensitive";b:0;}}s:18:" * fieldDefinition";r:237;}s:7:" * type";s:11:"string_long";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:2:{s:4:"type";s:4:"text";s:4:"size";s:3:"big";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:9:"timestamp";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:8:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:9:"Posted on";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:50:"Posted date of the feed item, as a Unix timestamp.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:7:"display";a:1:{s:4:"view";a:2:{s:7:"options";a:3:{s:5:"label";s:6:"hidden";s:4:"type";s:13:"timestamp_ago";s:6:"weight";i:1;}s:12:"configurable";b:1;}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:9:"timestamp";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:18:"field_item:created";s:8:"settings";a:0:{}}s:18:" * fieldDefinition";r:268;}s:7:" * type";s:7:"created";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:1:{s:4:"type";s:3:"int";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:4:"guid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:7:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:4:"GUID";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:11:"description";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:36:"Unique identifier for the feed item.";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:8:"provider";s:10:"aggregator";s:10:"field_name";s:4:"guid";s:11:"entity_type";s:15:"aggregator_item";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:22:"field_item:string_long";s:8:"settings";a:1:{s:14:"case_sensitive";b:0;}}s:18:" * fieldDefinition";r:304;}s:7:" * type";s:11:"string_long";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:2:{s:4:"type";s:4:"text";s:4:"size";s:3:"big";}}s:11:"unique keys";a:0:{}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}s:4:"uuid";O:37:"Drupal\Core\Field\BaseFieldDefinition":5:{s:13:" * definition";a:7:{s:5:"label";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:9:" * string";s:4:"UUID";s:12:" * arguments";a:0:{}s:10:" * options";a:0:{}}s:9:"read-only";b:1;s:10:"field_name";s:4:"uuid";s:11:"entity_type";s:15:"aggregator_item";s:8:"provider";s:10:"aggregator";s:6:"bundle";N;s:13:"initial_value";N;}s:17:" * itemDefinition";O:51:"Drupal\Core\Field\TypedData\FieldItemDataDefinition":2:{s:13:" * definition";a:2:{s:4:"type";s:15:"field_item:uuid";s:8:"settings";a:3:{s:10:"max_length";i:128;s:8:"is_ascii";b:1;s:14:"case_sensitive";b:0;}}s:18:" * fieldDefinition";r:335;}s:7:" * type";s:4:"uuid";s:9:" * schema";a:4:{s:7:"columns";a:1:{s:5:"value";a:3:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:128;s:6:"binary";b:0;}}s:11:"unique keys";a:1:{s:5:"value";a:1:{i:0;s:5:"value";}}s:7:"indexes";a:0:{}s:12:"foreign keys";a:0:{}}s:10:" * indexes";a:0:{}}}',
  ])
  ->values([
    'collection' => 'entity.storage_schema.sql',
    'name' => 'aggregator_item.field_schema_data.uuid',
    'value' => 'a:1:{s:15:"aggregator_item";a:2:{s:6:"fields";a:1:{s:4:"uuid";a:4:{s:4:"type";s:13:"varchar_ascii";s:6:"length";i:128;s:6:"binary";b:0;s:8:"not null";b:0;}}s:11:"unique keys";a:1:{s:34:"aggregator_item_field__uuid__value";a:1:{i:0;s:4:"uuid";}}}}',
  ])
  ->values([
    'collection' => 'system.schema',
    'name' => 'aggregator',
    'value' => 'i:8605;',
  ])
  ->execute();

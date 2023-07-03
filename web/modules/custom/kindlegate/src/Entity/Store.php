<?php

namespace Drupal\kindlegate\Entity;

use Drupal\kindlegate\Entity\StoreInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Store entity.
 *
 * @ContentEntityType(
 *   id = "commerce_store",
 *   label = @Translation("Store"),
 *   bundle_label = @Translation("Store type"),
 *   handlers = {
 *     "storage_schema" = "Drupal\custom_commerce_store\StoreSchema",
 *     "access" = "Drupal\commerce_store\StoreAccessControlHandler",
 *     "views_data" = "Drupal\commerce\CommerceEntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_store\Form\StoreForm",
 *       "add" = "Drupal\commerce_store\Form\StoreForm",
 *       "edit" = "Drupal\commerce_store\Form\StoreForm",
 *       "delete" = "Drupal\commerce\Form\EntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_store\StoreHtmlRouteProvider",
 *     },
 *     "list_builder" = "Drupal\commerce_store\StoreListBuilder",
 *   },
 *   admin_permission = "administer commerce_store",
 *   fieldable = TRUE,
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "store_id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "published" = "status",
 *   },
 *   bundle_entity_type = "commerce_store_type",
 *   field_ui_base_route = "entity.commerce_store_type.edit_form",
 * )
 */
class Store extends ContentEntityBase implements StoreInterface {
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getDisplayName() {
    // Implement any additional methods specific to the Store entity.
    return new TranslatableMarkup('Store: @name', ['@name' => $this->label()]);
  }
}

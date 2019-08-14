<?php

class Smartassistant_Smartassistant_Helper_Attribute extends Mage_Core_Helper_Abstract
{
    /**
     * @var array Additional attribute list
     */
    private $_additionalAttributes = array(
        'entity_id'     => 'Product Id',
        'is_in_stock'   => 'Is In Stock',
        'qty'           => 'Qty',
        'image'         => 'Image',
        'category'      => 'Category Name',
        'category_id'   => 'Category Id',
        'final_price'   => 'Final Price',
        'store_price'   => 'Store Price',
        'url'           => 'Product url',
    );

    /**
     * Retreive available attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->getProductAttributes();
        foreach ($this->_additionalAttributes as $code => $label) {
            $attributes[$code] = array(
                'label' => Mage::helper('smartassistant')->__($label),
                'value' => $code,
            );
        }
        return $attributes;
    }

    /**
     * Retreive product attributes list
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $result = array();

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('attribute_code', array('nin' => array('gallery', 'media_gallery')));

        foreach ($attributes as $attribute) {
            if ($attribute->getFrontendLabel()) {
                $label = $attribute->getFrontendLabel();
                $code  = $attribute->getAttributeCode();
                $result[$code] = array(
                    'label' => $label,
                    'value' => $code,
                );
            }
        }

        return $result;
    }

    /**
     * Prepare array with grouped attributes
     *
     * @param array $attributes
     * @return array
     */
    public function getGroupedAttributes($attributes)
    {
        $groupedAttributes = array();

        $group = '';

        $primary = array(
            'entity_id',
            'full_description',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'name',
            'short_description',
            'description',
            'sku',
            'status',
            'visibility',
        );

        $stock = array(
            'is_in_stock',
            'qty'
        );

        $price = array(
            'special_from_date',
            'special_to_date',
        );

        foreach ($attributes as $attribute) {

            $attributeCode = $attribute['value'];
            if (substr($attributeCode, 0, strlen('custom:')) == 'custom:') {
                $group = Mage::helper('smartassistant')->__('Custom Attributes');
                $groupKey = '1';
            } elseif (substr($attributeCode, 0, strlen('mapping:')) == 'mapping:') {
                $group = Mage::helper('smartassistant')->__('Mapping');
                $groupKey = '2';
            } elseif (strpos($attributeCode, 'ammeta') !== false ) {
                $group = Mage::helper('smartassistant')->__('Amasty Meta Tags');
                $groupKey = '3';
            } elseif (in_array($attributeCode, $primary)) {
                $group = Mage::helper('smartassistant')->__('Primary Attributes');
                $groupKey = '4';
            } elseif (in_array($attributeCode, $stock)) {
                $group = Mage::helper('smartassistant')->__('Stock Attributes');
                $groupKey = '5';
            } elseif (in_array($attributeCode, $price) || strpos($attributeCode, 'price') !== false) {
                $group = Mage::helper('smartassistant')->__('Prices & Taxes');
                $groupKey = '6';
            } elseif (strpos($attributeCode, 'image') !== false || strpos($attributeCode, 'thumbnail') !== false) {
                $group = Mage::helper('smartassistant')->__('Images');
                $groupKey = '7';
            }  elseif (strpos($attributeCode, 'category') !== false ) {
                $group = Mage::helper('smartassistant')->__('Category');
                $groupKey = '8';
            } else {
                $group = Mage::helper('smartassistant')->__('Others Attributes');
                $groupKey = '9';
            }

            if (! isset($groupedAttributes[$groupKey])) {
                $groupedAttributes[$groupKey] = array(
                    'label' => $group,
                    'items' => array(),
                );
            }

            $groupedAttributes[$groupKey]['items'][] = $attribute;
        }

        ksort($groupedAttributes);

        return $groupedAttributes;
    }

    /**
     * Return value of attribute for given product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    public function getAttributeValue($product, $field)
    {
        $code = $field->getAttributeCode();
        $getter = 'get' . uc_words($code, '');

        if (method_exists($this, $getter)) {
            $value = $this->$getter($product, $field);
            $value = empty($value) ? $field->getWhenEmpty() : $value;
            return $value;
        }

        $getter = 'get' . uc_words($code, '');
        $value = $product->$getter();
        $value = empty($value) ? $field->getWhenEmpty() : $value;

        return $value;
    }

    /**
     * Get value of entity_id attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    private function getEntityId($product, $field)
    {
        return $product->getId();
    }

    /**
     * Get value of is_in_stock attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    private function getIsInStock($product, $field)
    {
        $stockItem = $product->getStockItem();
        $isInStock = $stockItem->getIsInStock();
        return $isInStock;
    }

    /**
     * Get value of qty attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    private function getQty($product, $field)
    {
        $stockItem = $product->getStockItem();
        $qty = $stockItem->getQty();
        return $qty;
    }

    /**
     * Get value of image url attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return string|null
     */
    private function getImage($product, $field)
    {
        $image = $product->getImage();
        if (strtolower($image) != 'no_selection') {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $image;
            return $url;
        }
        return null;
    }

    /**
     * Get value of small image url attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return string|null
     */
    private function getSmallImage($product, $field)
    {
        $image = $product->getSmallImage();
        if (strtolower($image) != 'no_selection') {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $image;
            return $url;
        }
        return null;
    }

    /**
     * Get value of thumbnail url attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return string|null
     */
    private function getThumbnail($product, $field)
    {
        $image = $product->getThumbnail();
        if (strtolower($image) != 'no_selection') {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $image;
            return $url;
        }
        return null;
    }

    /**
     * Get value of categories attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    private function getCategory($product, $field)
    {
        $items = array();
        $collection = $product->getCategoryCollection();
        foreach ($collection as &$item) {
            $item = Mage::getModel('catalog/category')->load($item->getId());
            $items[] = $item->getName();
        }

        $value = implode(', ', $items);
        return $value;
    }

    /**
     * Get value of category ids attribute id for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    private function getCategoryId($product, $field)
    {
        $ids = $product->getCategoryIds();
        $ids = is_array($ids) ? $ids : array();
        return implode(',', $ids);
    }

    /**
     * Get product site url for given item
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Smartassistant_Smartassistant_Model_Exportfield $field
     * @return mixed
     */
    private function getUrl($product, $field)
    {
        $storeId = $field->getConfig()->getStoreId();
        $visibility = $product->getVisibility();
        $mayBeChild = in_array($product->getTypeId(), array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
        ));

        if ($visibility != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE || ! $mayBeChild) {
            $url = $product->setStoreId($storeId)->getProductUrl();
            return $url;
        }

        $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
        if (! $parentIds) {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        }
        if (! $parentIds) {
            $parentIds = Mage::getModel('bundle/product_type')->getParentIdsByChild($product->getId());
        }

        if($parentIds) {
            $parent = Mage::getModel('catalog/product')->load($parentIds[0]);
            $url = $parent->setStoreId($storeId)->getProductUrl();
            return $url;
        }

        return null;
    }
}

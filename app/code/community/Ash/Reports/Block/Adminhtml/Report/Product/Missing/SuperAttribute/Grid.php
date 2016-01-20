<?php
/**
 * Add reports for the following:
 *
 * + configurable products missing children (associated) products
 * + configurable products with mis-configured attributes that don't belong to the attribute set
 *
 * @category    Ash
 * @package     Ash_Reports
 * @copyright   Copyright (c) 2015 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ash_Reports_Block_Adminhtml_Report_Product_Missing_SuperAttribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // need to set this otherwise you're going to have a bad time
    protected $_blockGroup = 'ash_reports';

    // protected $_saveParametersInSession = true;

    public function __construct()
    {
        $this->_blockGroup = 'ash_reports';
        parent::__construct();
        $this->setId('gridMissingSuperAttribute');
        $this->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds   = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId    = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds   = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId    = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        /** @var $collection Mage_Reports_Model_Resource_Product_Collection  */
        $collection = Mage::getResourceModel('reports/product_collection');
        $collection->addAttributeToSelect(array('entity_id', 'sku', 'name', 'eav_attribute_set.attribute_set_name'));
        $collection->setStoreId($storeId);

        // Get the Zend_Db_Select b/c it's easier to work with
        $select = $collection->getSelect();
        $select->columns("GROUP_CONCAT(`eav_attribute`.`attribute_id` SEPARATOR ', ') AS attribute_ids");
        $select->columns("GROUP_CONCAT(`eav_attribute`.`attribute_code` SEPARATOR ', ') AS attribute_codes");
        $select->columns("GROUP_CONCAT(`eav_attribute`.`frontend_label` SEPARATOR ', ') AS attribute_frontend_labels");
        $select->join(
                'catalog_product_super_attribute',
                'catalog_product_super_attribute.product_id = e.entity_id',
                array()
            )
            ->join(
                'eav_attribute',
                'eav_attribute.attribute_id = catalog_product_super_attribute.attribute_id',
                array('attribute_id', 'frontend_label')
            )
            ->join(
                'eav_attribute_set',
                'eav_attribute_set.attribute_set_id = e.attribute_set_id',
                array('attribute_set_name')
            );


        $subquery = new Zend_Db_Expr("SELECT eav_entity_attribute.attribute_id FROM eav_entity_attribute WHERE eav_entity_attribute.attribute_set_id = e.attribute_set_id");

        $select->where('eav_attribute.attribute_id NOT IN (' . $subquery . ')');

        $select->group('e.entity_id');

        // Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
        // Mage::log('REPORT SQL QUERY: ' . print_r($select->__toString(), true));

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('ash_reports')->__('Product ID'),
            'sortable'  => false,
            'index'     => 'entity_id',
            // 'filter'    => 'adminhtml/widget_grid_column_filter_range',
            'filter'    => false,
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('ash_reports')->__('SKU'),
            'sortable'  => false,
            'filter'    => false,
            'index'     => 'sku'
        ));

        $this->addColumn('attribute_set_name', array(
            'header'    => Mage::helper('ash_reports')->__('Attribute Set Name'),
            'sortable'  => false,
            'filter'    => false,
            'index'     => 'attribute_set_name'
        ));

        $this->addColumn('attribute_ids', array(
            'header'    => Mage::helper('ash_reports')->__('Attribute IDs'),
            'sortable'  => false,
            'filter'    => false,
            'index'     => 'attribute_ids',
        ));

        $this->addColumn('attribute_codes', array(
            'header'    => Mage::helper('ash_reports')->__('Attribute Codes Missing'),
            'sortable'  => false,
            'filter'    => false,
            'index'     => 'attribute_codes',
        ));

        $this->addColumn('attribute_frontend_labels', array(
            'header'    => Mage::helper('ash_reports')->__('Attribute Frontend Labels'),
            'sortable'  => false,
            'filter'    => false,
            'index'     => 'attribute_frontend_labels',
        ));


        $this->addExportType('*/*/exportMissingattributeCsv', Mage::helper('ash_reports')->__('CSV'));

        return parent::_prepareColumns();
    }
}

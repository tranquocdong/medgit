<?php

require_once '../app/Mage.php';

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$baseImageUrl = 'http://netpris.dk/media/catalog/product';

$type = $_GET['type'];

if (!isset($type)) {
    $type = 'magmi';
}

$netprisCvs = 'csv/export_all_products_thang_all_fields.csv';

$oldProducts = csv_to_array($netprisCvs, ',');

$newProducts = array();

foreach ($oldProducts as $op) {
    
    $np = array();
    
    if ($type=='magmi') {
        
        $np['websites'] = 'base';
        $np['store'] = 'admin';     
        $np['sku'] = $op['sku'];
        $np['deal_start_time'] = $op['deal_start_time'];
        $np['deal_stop_time'] = $op['deal_stop_time'];
        $np['price_unit'] = $op['price_unit'];
        $np['price_unit_origin'] = $op['price_unit_origin'];
        $np['market_price'] = $op['market_price'];        
        $np['landing_page'] = $op['landing_page'];
        $np['product_unit'] = $op['product_unit'];
        $np['tilbud'] = $op['tilbud'];
        $np['netpris'] = $op['netpris'];
        $np['calculated_unit'] = $op['calculated_unit'];
        $np['weight_unit'] = $op['weight_unit'];
        $np['has_deal'] = $op['has_deal'];
        $np['product_name'] = $op['product_name'];
        $np['aktuelt'] = $op['aktuelt'];
        $np['retter'] = $op['retter'];
        $np['pant_sku'] = $op['pant_sku'];
        $np['alphabet'] = $op['alphabetAtribute'];
        
        // categories
        $np['categories_text'] = $op['categories_text'];
        
        
        // images
        $na['image'] = $baseImageUrl . $op['image'];
        $na['small_image'] = $baseImageUrl . $op['small_image'];
        $na['thumbnail'] = $baseImageUrl . $op['thumbnail'];
        
        // tax
        
        
        
    }
    else {
        
        $np['websites'] = 'base';
        $np['store'] = 'admin';
        $np['attribute_set'] = 'Default';
        $np['type'] = $op['type'];
        $np['sku'] = $op['sku'];

        $np['name'] = $op['name'];


        $np['price'] = $op['price'];
        $np['special_price'] = $op['special_price'];
        $np['cost'] = $op['cost'];
        $np['weight'] = $op['weight'];
        $np['status'] = $op['status'];
        $np['tax_class_id'] = $op['tax_class_id'];
        $np['visibility'] = $op['visibility'];
        $np['special_from_date'] = $op['special_from_date'];
        $np['description'] = $op['description'];
        $np['short_description'] = $op['short_description'];
        $np['meta_keyword'] = $op['meta_keyword'];
        $np['qty'] = $op['qty'];
        
        $np['meta_title'] = $op['meta_title'];
        $np['meta_description'] = $op['meta_description'];

    }
        
    
    $newProducts[] = $np;
    
    echo 'Processed ' . $np['sku'] . '<br/>';
    
}

$fp = fopen('csv/new_products_generated_'.$type.'_at_' . date("ymdh") . '.csv', 'w');

fputcsv($fp, array_keys($newProducts[0]), ",");                

foreach ($newProducts as $row) {
    fputcsv($fp, $row, ",");    
}          
    
fclose($fp);

exit;


function csv_to_array($filename='', $delimiter=',')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;
 
    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
        {
            if(!$header) {
                $header = $row;
            }   
            else {
                $data[] = array_combine($header, $row);
            }
        }
        fclose($handle);
    }
    return $data;
}


function getAttributSetName($product) {
    
    $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
    $attributeSetModel->load($product->getAttributeSetId());
    $attributeSetName  = $attributeSetModel->getAttributeSetName();
    return $attributeSetName;

}

function getConfigurationAttributes($p) {
    
    $configurableAttributes = array();
    
    if ($p->getTypeId()=='configurable') {
                
        $configAttributes = Mage::getModel('catalog/product_type_configurable')
                            ->getConfigurableAttributes($p);

        if (count($configAttributes)) {
            
            foreach ($configAttributes as $cfg) {
                $configurableAttributes[] = $cfg->getProductAttribute()->getAttributeCode();
            }   
        }
    }    
    
    return $configurableAttributes;
}

function getSimpleSkus($p) {
    
    $simpleSkus = array();
    
    if ($p->getTypeId()=='configurable') {

        $childProducts = Mage::getModel('catalog/product_type_configurable')
                            ->getUsedProducts(array('name', 'sku'), $p);


        if (isset($childProducts) && (count($childProducts)>0)) {
            $simpleSkus = array();
            foreach ($childProducts as $child) {
                $simpleSkus[] = $child->getSku();
            }
        }          
       
    }
    
    return $simpleSkus;
}

function getCategoryNamesOfProduct($p) {

	$ignoreCats = array('Root Catalog', 'Default Category');

    $cats = $p->getCategoryCollection()
            ->addAttributeToSelect('*');

    $catNames = array();

    foreach ($cats as $cat) {
        
        $pathNames = array();
        
        if ($cat->getLevel()==1)
            continue;
		
		$pathInStore = $cat->getPathInStore();
		$pathIds = array_reverse(explode(',', $pathInStore));

		$categories = $cat->getParentCategories();

		// add category path breadcrumb
		foreach ($pathIds as $categoryId) {
			if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                $_catName = $categories[$categoryId]->getName();
				if (!in_array($_catName, $ignoreCats)) {
                    $pathNames[] = str_replace('/', '-', $_catName); // for import with Magmi
                }
			}
		}
		
		$catNames[] = implode('/', $pathNames);
				
    }    
    
    return $catNames;
}

function applyAllCatalogPromotionRules() {
    Mage::getModel('catalogrule/rule')->applyAll();
    echo "All catalog promotion rules have been applied" . "<br />\n";
}

function flushAllCache() {

    // flush all Magento Cache
    Mage::app()->getCacheInstance()->flush();

    // flush all zoom cache
    // Mage::helper('ezzoom')->flushCache();

    echo "Flush all caches" . "<br />\n";
    
}
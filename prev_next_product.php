<?php

/**
 * @package Joomla.JoomShopping.Products
 * @version 1.7.0
 * @author Beagler
 * @website http://dell3r.ru/
 * @email support@dell3r.ru
 * @copyright Copyright by Beagler. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class plgJshoppingProductsPrev_Next_Product extends JPlugin {

	function addLinkToProducts(&$products, $default_category_id = 0, $useDefaultItemId = 0){
		$jshopConfig = JSFactory::getConfig();
		foreach($products as $key=>$value){
			$category_id = (!$default_category_id)?($products[$key]->category_id):($default_category_id);
			if (!$category_id) $category_id = 0;
			$products[$key]->product_link = SEFLink('index.php?option=com_jshopping&controller=product&task=view&category_id='.$category_id.'&product_id='.$products[$key]->product_id, $useDefaultItemId);
		} 
		return $products;
	}

    function onBeforeDisplayProductView(&$view) {
        $category_id = $view->category_id;
        $product_id = JRequest::getInt('product_id'); 
        $mainframe = & JFactory::getApplication();
        $jshopConfig = &JSFactory::getConfig();
        $order = $mainframe->getUserStateFromRequest('jshoping.list.front.productorder', 'order', $jshopConfig->product_sorting, 'int');
        $orderby = $mainframe->getUserStateFromRequest('jshoping.list.front.productorderby', 'orderby', $jshopConfig->product_sorting_direction, 'int');
        $orderbyq = getQuerySortDirection($order, $orderby);
        $field_order = $jshopConfig->sorting_products_field_select[$order];
        $order_query = "";
        $order_original = $field_order;
        $multyCurrency = count(JSFactory::getAllCurrency());
        if ($multyCurrency > 1 && $field_order == "prod.product_price") {
            if (strpos($adv_from, "jshopping_currencies") === false) {
                $adv_from .= " LEFT JOIN `#__jshopping_currencies` AS cr USING (currency_id) ";
            }
            if ($jshopConfig->product_list_show_min_price) {
                $field_order = "prod.min_price/cr.currency_value";
            } else {
                $field_order = "prod.product_price/cr.currency_value";
            }
        }
        if ($field_order == "prod.product_price" && $jshopConfig->product_list_show_min_price) {
            $field_order = "prod.min_price";
        }
        $order_query = " ORDER BY " . $field_order;
        if ($orderbyq) {
            $order_query .= " " . $orderbyq;
        }
        $db = & JFactory::getDBO();
        $query = "SELECT *, `prod`.`name_ru-RU` as name FROM `#__jshopping_products` AS prod
		 INNER JOIN `#__jshopping_products_to_categories` AS pr_cat ON `pr_cat`.`product_id` = `prod`.`product_id`
		 LEFT JOIN `#__jshopping_categories` AS cat ON `pr_cat`.`category_id` = `cat`.`category_id`
		 WHERE `prod`.`product_quantity`> 0 AND `prod`.`product_publish` = 1 AND `pr_cat`.`category_id` = " . $category_id . " " . $order_query;
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        $total = Count($rows);
        $rows = $this->addLinkToProducts($rows, 0, 1);
        
        foreach ($rows as $Key => $row) { 
            
                If ($row->product_id == $product_id) { 
                    if ($rows[$Key + 1]->product_link)
                        $view->Next = '<a class="next_product" href="' . $rows[$Key + 1]->product_link . '" title="' . substr($rows[$Key + 1]->product_ean, 0, strpos($rows[$Key + 1]->product_ean, ' ')) . ' ' . $rows[$Key + 1]->name . '">' . substr($rows[$Key + 1]->product_ean, 0, strpos($rows[$Key + 1]->product_ean, ' ')) . ' ' . $rows[$Key + 1]->name . ' ></a>';
					
                    if ($rows[$Key - 1]->product_link)
                        $view->Prev = '<a class="prev_product" href="' . $rows[$Key - 1]->product_link . '" title="' . substr($rows[$Key - 1]->product_ean, 0, strpos($rows[$Key - 1]->product_ean, ' ')) . ' ' . $rows[$Key - 1]->name . '">< ' . substr($rows[$Key - 1]->product_ean, 0, strpos($rows[$Key - 1]->product_ean, ' ')) . ' ' . $rows[$Key - 1]->name . '</a>';
						
                    Break;
                }
            
        }
    }

}
<?php defined('ABSPATH') or exit ?>

<!--Product Filter-->
<div class="wdr-filter-block" id="wdr-filter-block">
    <div class="wdr-block">
        <div class="wdr-conditions-relationship">
            <?php $condition_relationship = $collection->getRelationship('and'); ?>
            <label><b><?php _e('Conditions Relationship ', 'wdr-collections'); ?></b></label>&nbsp;&nbsp;&nbsp;&nbsp;
            <label><input type="radio" name="condition_relationship"
                          value="and" <?php echo ($condition_relationship == 'and') ? 'checked' : '' ?>
                ><?php _e('Match All', 'wdr-collections'); ?></label>
            <label><input type="radio" name="condition_relationship"
                          value="or" <?php echo ($condition_relationship == 'or') ? 'checked' : '' ?>><?php _e('Match Any', 'wdr-collections'); ?>
            </label>
        </div>
        <div class="wdr-row">
            <div class="wdr-filter-group-items">
                <input type="hidden" id="awdr_get_collection_linked_rules_nonce" value="<?php echo esc_attr(\Wdr\App\Helpers\Helper::create_nonce('wdr_ajax_get_collection_linked_rules')); ?>">
                <input type="hidden" name="edit_collection"
                       value="<?php echo ($collection->getId()) ? esc_attr($collection->getId()) : ''; ?>"><?php
                if ($collection->hasFilter()) {
                    $filters = $collection->getFilter();
                    $filter_row_count = 1;
                    foreach ($filters as $filter) {
                        ?>
                        <div class="wdr-grid wdr-filter-group" data-index="<?php echo esc_attr($filter_row_count); ?>">
                            <div class="wdr-filter-type">
                                <select name="filters[<?php echo esc_attr($filter_row_count); ?>][type]"
                                        class="wdr-product-filter-type"><?php
                                    if (isset($product_filters) && !empty($product_filters)) {
                                        foreach ($product_filters as $wdr_filter_key => $wdr_filter_value) {
                                            ?>
                                            <optgroup label="<?php esc_attr_e($wdr_filter_key, 'wdr-collections'); ?>" ><?php
                                            foreach ($wdr_filter_value as $key => $value) {
                                                ?>
                                                <option
                                                <?php
                                                if(isset($value['active']) && $value['active'] == false){
                                                    ?>
                                                    disabled="disabled"
                                                    <?php
                                                } else {
                                                    ?>
                                                    value="<?php echo esc_attr($key); ?>"
                                                    <?php
                                                }
                                                ?>
                                                <?php echo ($filter->type == $key) ? 'selected' : ''; ?>><?php _e($value['label'], 'wdr-collections'); ?></option><?php
                                            } ?>
                                            </optgroup><?php
                                        }
                                    } ?>
                                </select>
                            </div>
                            <?php if ($filter->type != 'all_products') {?>
                                <div class="products_group wdr-products_group"><?php
                                    if(in_array($filter->type, array('products'))){
                                        ?>
                                        <div class="wdr-product_filter_method">
                                            <select name="filters[<?php echo esc_attr($filter_row_count); ?>][method]">
                                                <option value="in_list"
                                                    <?php echo (isset($filter->method) && $filter->method == 'in_list') ? 'selected' : ''; ?>><?php _e('In List', 'wdr-collections'); ?></option>
                                                <option value="not_in_list" <?php echo (isset($filter->method) && $filter->method == 'not_in_list') ? 'selected' : ''; ?>><?php _e('Not In List', 'wdr-collections'); ?></option>
                                            </select>
                                        </div>
                                        <div class="awdr-product-selector">
                                            <?php
                                            $placeholder = '';
                                            $selected_options = '';
                                            if (!empty($filter->value) && is_array($filter->value)) {
                                                $item_name = '';
                                                foreach ($filter->value as $option) {
                                                    switch ($filter->type) {
                                                        case 'products':
                                                            $item_name = esc_attr('#'.$option.' '.\Wdr\App\Helpers\Woocommerce::getTitleOfProduct($option));
                                                            $placeholder = __('Products', 'wdr-collections');
                                                            break;
                                                    }
                                                    if (!empty($item_name)) {
                                                        $option_value = esc_attr($option);
                                                        $selected_options .= "<option value={$option_value} selected>{$item_name}</option>";
                                                    }
                                                }
                                            }
                                            ?>
                                            <select multiple
                                                    class="edit-filters awdr_validation"
                                                    data-list="<?php echo esc_attr($filter->type); ?>"
                                                    data-field="autocomplete"
                                                    data-placeholder="<?php esc_attr_e('Select ' . $placeholder, 'wdr-collections'); ?>"
                                                    name="filters[<?php echo esc_attr($filter_row_count); ?>][value][]">
                                                <?php echo $selected_options; ?>
                                            </select>
                                        </div>
                                        <?php
                                    }
                                    do_action('advanced_woo_discount_rules_admin_filter_fields', $collection, $filter, $filter_row_count);
                                    ?>
                                </div>
                            <?php } ?>
                            <div class="wdr-btn-remove wdr_filter_remove">
                                <span class="dashicons dashicons-no-alt remove-current-row wdr-filter-alert"></span>
                            </div><?php
                            switch($filter->type) {
                                case "products": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Choose products that get the discount using "In List". If you want to exclude a few products, choose "Not In List" and select the products you wanted to exclude from discount. (You can add multiple filters)', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                case "product_category": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Choose categories that get the discount using "In List". If you want to exclude a few categories, choose "Not In List" and select the categories you wanted to exclude from discount. (You can add multiple filters of same type)', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                case "product_attributes": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Choose attributes that get the discount using "In List". If you want to exclude a few attributes, choose "Not In List" and select the attributes you wanted to exclude from discount. (You can add multiple filters of same type)', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                case "product_tags": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Choose tags that get the discount using "In List". If you want to exclude a few tags, choose "Not In List" and select the tags you wanted to exclude from discount. (You can add multiple filters of same type)', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                case "product_sku": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Choose SKUs that get the discount using "In List". If you want to exclude a few SKUs, choose "Not In List" and select the SKUs you wanted to exclude from discount. (You can add multiple filters of same type)', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                case "product_on_sale": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Choose whether you want to include (or exclude) products on sale (those having a sale price) for the discount ', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                case "all_products": ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Discount applies to all eligible products in the store', 'wdr-collections'); ?></span></div>
                                    <?php break;
                                default:
                                    ?>
                                    <div class="wdr_filter_desc_text"><span><?php _e('Discount applies to custom taxonomy', 'wdr-collections'); ?></span></div>
                                    <?php break;
                            }
                            ?>
                        </div>
                        <?php
                        $filter_row_count++;
                    }
                } else { ?>
                    <div class="wdr-grid wdr-filter-group" data-index="1">
                        <div class="wdr-filter-type wdr-filter-all-product">
                            <select name="filters[1][type]" class="wdr-product-filter-type">
                                <option value="" selected disabled><?php esc_html_e('Choose', 'wdr-collections'); ?></option>
                                <?php
                                if (isset($product_filters) && !empty($product_filters)) {
                                    foreach ($product_filters as $wdr_filter_key => $wdr_filter_value) {
                                        ?>
                                        <optgroup label="<?php esc_attr_e($wdr_filter_key, 'wdr-collections'); ?>"><?php
                                        foreach ($wdr_filter_value as $key => $value) {
                                            ?>
                                            <option
                                            <?php
                                            if(isset($value['active']) && $value['active'] == false){
                                                ?>
                                                disabled="disabled"
                                                <?php
                                            } else {
                                                ?>
                                                value="<?php echo esc_attr($key); ?>"
                                                <?php
                                            }
                                            ?>
                                            ><?php _e($value['label'], 'wdr-collections'); ?></option><?php
                                        } ?>
                                        </optgroup><?php
                                    }
                                } ?>
                            </select>
                        </div>
                        <div class="wdr-btn-remove wdr_filter_remove">
                            <span class="dashicons dashicons-no-alt remove-current-row wdr-filter-alert"></span>
                        </div>
                        <div class="wdr_filter_desc_text">
                            <span>
                                <?php _e('Discount applies to all eligible products in the store', 'wdr-collections'); ?>
                            </span>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="wdr-add-condition add-condition-and-filters">
            <button type="button"
                    class="button add-product-filter"><?php _e('Add filter', 'wdr-collections'); ?></button>
        </div>
    </div>
</div>
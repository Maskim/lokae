<?php
//list all categories of a store
function rent_my_categories_list_shortcode()
{

    ob_start();
    $rentmy_categories = new RentMy_Category();

    if (!empty($_GET['uid'])) {
        $response = $rentmy_categories->children(trim($_GET['uid']));
    } else {
        $response = $rentmy_categories->categories();
    }

    if (!empty($response)) {
        $dataSet = $response;
        $rent_my_categories_list = $dataSet;
        echo rentmy_categories_template($rent_my_categories_list);
        return ob_get_clean();
    } else {
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    }
}

add_shortcode('rentmy-categories-list', 'rent_my_categories_list_shortcode');

function rentmy_categories_template($array)
{
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-plugin-categorytagsearch-area">
        <h3>Categories</h3>
        <?php if (!empty($array)) { ?>
        <?php if (!empty($array['sibling'])) { ?>
            <ul class="rentmy-product-categories">
                <?php
                $children_arr = $array['child']['children'];
                $siblings_arr = $array['sibling'];
                ?>

                <?php if (!empty($siblings_arr)) {
                    foreach ($siblings_arr as $siblings) { ?>
                        <li>
                            <a class="rent-my-list-link <?php echo ($siblings['uuid'] == $_GET['uid']) ? 'rentmy-toggle-menu' : ''; ?>"
                            data-id="<?php echo $siblings['id']; ?>" <?php if ($siblings['uuid'] == $_GET['uid']) { ?> style='font-weight: bold;' <?php } ?>
                            href="<?php echo ($siblings['uuid'] == $_GET['uid']) ? 'javascript:void(0)' : home_url('rentmy-products-list?uid=' . $siblings['uuid']); ?>">
                                <?php echo $siblings['name']; ?>

                                <?php if (!empty($siblings['child'])) { ?>
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <?php } else { ?>
                                    <span class="dashicons dashicons-arrow-right-alt"></span>
                                <?php } ?>
                            </a>
                            <?php if (!empty($siblings['child'])) { ?>
                                <ul class="rentmy-child-wrapper">
                                    <?php foreach ($siblings['child'] as $child) { ?>

                                        <li>
                                            <a class="rent-my-list-link" data-id="<?php echo $child['id']; ?>"
                                            href="<?php echo home_url('rentmy-products-list?uid=' . $child['uuid']); ?>">
                                                <?php echo $child['name']; ?>
                                            </a>
                                            <?php if (!empty($child['child'])) { ?>
                                                <ul class="rentmy-child-wrapper">
                                                    <?php foreach ($child['child'] as $c) { ?>
                                                        <li>
                                                            <a class="rent-my-list-link" data-id="<?php echo $c['id']; ?>"
                                                            href="<?php echo home_url('rentmy-products-list?uid=' . $c['uuid']); ?>">
                                                                <?php echo $c['name']; ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } ?>
                                        </li>

                                    <?php } ?>
                                </ul>
                            <?php } ?>
                        </li>
                    <?php }
                } ?>

                <?php if (!empty($children_arr)) { ?>
                    <li class="rentmy-parent-category" data-id="<?php echo $array['child']['id']; ?>">
                        <?php echo $array['child']['name']; ?>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </li>
                <?php } ?>
            </ul>
        <?php } else { ?>
            <?php if (is_array($array)) { ?>
                <ul class="rentmy-product-categories">
                    <?php foreach ($array as $arr) { ?>
                        <li class="rentmy-list-children-wrapper">
                            <a class="rent-my-list-link" data-id="<?php echo $arr['id']; ?>"
                            href="<?php echo home_url('rentmy-products-list?uid=' . $arr['uuid']); ?>">
                                <?php echo $arr['name']; ?>
                                <span class="dashicons dashicons-arrow-right-alt"></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        <?php } ?>
    <?php } else { ?>
        <span class="rentmy-errore-msg">No categories found</span>
    <?php } ?>
    </div>
</div>
    <?php
}

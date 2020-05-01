<?php
function rentmy_checkout_complete_template()
{
    $order_details = new RentMy_Order();
    $view_order = $order_details->viewOrderDetails($_SESSION['order_uid']);
    $order_data = !empty($view_order['data']) ? $view_order['data'] : null;
    $order_items = !empty($order_data['order_items']) ? $order_data['order_items'] : null;
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="checkout-complete">
            <ul class="rentmy-progressbar">
                <li>
                    <a class="btn btn-circle">1</a><br>
                    <?php echo $checkout_label['step_one']; ?>
                </li>
                <li>
                    <a class="btn btn-circle">2</a><br>
                    <?php echo $checkout_label['step_two']; ?>
                </li>
                <li>
                    <a class="btn btn-circle">3</a><br>
                    <?php echo $checkout_label['step_three']; ?>
                </li>
                <li class="active">
                    <a class="btn btn-circle">4</a><br>
                    <?php echo $checkout_label['step_four']; ?>
                </li>
            </ul>

            <?php if (!empty($order_data)): ?>
            <span class="rentmy-success-msg">Thank you for your order</span>
            <!-- <span class="rentmy-errore-msg">Errore Message</span> -->
            <div class="rentmy-table rentmy-cart-form">
                <table>

                    <tr>
                        <th></th>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Sales Tax</th>
                        <th>Subtotal</th>
                    </tr>
                    <tbody>
                    <?php foreach ($order_items as $order): ?>
                        <tr>
                            <td>
                                <?php if (!empty($order['product']['images'][0]['image_small'])): ?>
                                    <img width="150"
                                        src="<?php echo $GLOBALS['RentMy']::imageLink($order['product']['id'], $order['product']['images'][0]['image_small'], 'list'); ?>"
                                        alt="">
                                <?php endif; ?>
                            </td>
                            <td>
                                <p><?php echo $order['product']['name']; ?></p>
                            </td>
                            <td><?php echo $GLOBALS['RentMy']::currency($order['sub_total']); ?></td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td><?php echo $order['sales_tax']; ?>%</td>
                            <td><?php echo $GLOBALS['RentMy']::currency($order['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="cart-summery-area">
                    <h5 class="cart-total-title">Total</h5>
                    <table>
                        <tbody>
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-right"><strong><?php echo $GLOBALS['RentMy']::currency($order_data['sub_total']); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Shipping Charge</td>
                            <td class="text-right">
                                <?php echo !empty($order_data['delivery_charge']) ? $GLOBALS['RentMy']::currency($order_data['delivery_charge']) : $GLOBALS['RentMy']::currency(0); ?></td>
                        </tr>
                        <tr>
                            <td>Discount</td>
                            <td class="text-right"><?php echo $GLOBALS['RentMy']::currency($order_data['total_discount']); ?></td>
                        </tr>
                        <tr>
                            <td>Tax</td>
                            <td class="text-right"><?php echo $GLOBALS['RentMy']::currency($order_data['tax']); ?></td>
                        </tr>
                        <tr>
                            <td>Deposit Amount</td>
                            <td class="text-right"><?php echo $GLOBALS['RentMy']::currency($order_data['total_deposit']); ?></td>
                        </tr>
                        <tr>
                            <td>
                                <h4>Total</h4>
                            </td>
                            <td class="text-right">
                                <h4><?php echo $GLOBALS['RentMy']::currency($order_data['total']); ?></h4>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="download-receipt">
                        <a href="https://clientapi.rentmy.co/api/pages/pdf?order_id=<?php echo $order_data['id']; ?>">Download
                            Receipt</a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <span class="rentmy-errore-msg">No order found yet</span>
    <?php endif; ?>
    </div>
    <?php
}

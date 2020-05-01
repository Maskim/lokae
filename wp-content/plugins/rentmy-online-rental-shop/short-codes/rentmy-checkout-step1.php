<?php
function rentmy_checkout_info_template()
{
    $checkout_info = null;
    if(!empty($_SESSION['rentmy_checkout']['info'])){
        $checkout_info = $_SESSION['rentmy_checkout']['info'];
    }

    if(!empty($GLOBALS['checkout_labels'])){
        $checkout_label = $GLOBALS['checkout_labels'];
    }

    if(!empty($GLOBALS['signature'])){
        $signature = $GLOBALS['signature'];
    }

    if(!empty($GLOBALS['rm_custom_fields']['data'])){
        $custom_checkout_fields = $GLOBALS['rm_custom_fields']['data'];
    }

    if(!empty($GLOBALS['terms_condition'])){
        $terms_condition = $GLOBALS['terms_condition'];
    }
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-checkout-billing">
            <ul class="rentmy-progressbar">
                <li class="active">
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
                <li>
                    <a class="btn btn-circle">4</a><br>
                    <?php echo $checkout_label['step_four']; ?>
                </li>
            </ul>

            <form id="checkout-<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" class="" action=""
                method="post">
                <div class="checkout-header">
                    <h2>
                        <?php echo $checkout_label['title_contact']; ?>
                    </h2>
                </div>

                <div class="rentmy-form-row">
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_first_name']; ?></label>
                        <input type="text" class="form-control" name="first_name" autocomplete="billing given-name"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['first_name'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_lastname']; ?></label>
                        <input type="text" class="form-control" name="last_name" autocomplete="billing family-name"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['last_name'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_mobile']; ?> *</label>
                        <input required type="text" class="form-control" name="mobile" autocomplete="billing mobile"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['mobile'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_email']; ?> *</label>
                        <input required type="email" class="form-control" name="email" autocomplete="billing email"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['email'] : ''; ?>">
                    </div>
                </div>
                <div class="checkout-header">
                    <h2><?php echo $checkout_label['title_billing']; ?></h2>
                </div>
                <div class="rentmy-form-row">
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_country']; ?> *</label>
                        <select name="country" id="rm_country" class="form-control" required autocomplete="billing country">
                            <?php foreach ($GLOBALS['rm_countries'] as $country) { ?>
                                <option value="<?php echo $country['code'];?>" <?php if($country['code'] == (!empty($checkout_info) ? $checkout_info['country'] : 'US')){ ?> selected <?php } ?>><?php echo $country['name'];?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_address_line_1']; ?> *</label>
                        <input required type="text" class="form-control" name="address_line1" autocomplete="billing address-line1"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['address_line1'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_address_line_2']; ?></label>
                        <input type="text" class="form-control" name="address_line2" autocomplete="billing address-line2"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['address_line2'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_city']; ?> *</label>
                        <input required type="text" class="form-control" name="city" id="rm_city" autocomplete="billing address-level2"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['city'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_zipcode']; ?> *</label>
                        <input required type="text" class="form-control" name="zipcode" id="rm_zipcode" autocomplete="billing postal-code"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['zipcode'] : ''; ?>">
                    </div>
                    <div class="rentmy-form-group">
                        <label for=""><?php echo $checkout_label['lbl_state']; ?> *</label>
                        <input required type="text" class="form-control" name="state" id="rm_state" autocomplete="billing address-level1"
                        value="<?php echo !empty($checkout_info) ? $checkout_info['state'] : ''; ?>">
                    </div>
                </div>

                <?php if(!empty($custom_checkout_fields)): ?>
                    <div class="checkout-header">
                        <h2> Custom checkout information </h2>
                    </div>
                    <div class="rentmy-form-row" id="rentmy-custom-checkout-wrapper">
                        <?php foreach($custom_checkout_fields as $custom_field): ?>

                            <?php if($custom_field['field_type'] == '0'):?>
                                <div class="rentmy-form-group">
                                    <label for="<?php echo $custom_field['field_name']; ?>"><?php echo $custom_field['field_label']; ?> <?php echo !empty($custom_field['field_is_required']) ? '*' : ''; ?></label>
                                    <input <?php echo !empty($custom_field['field_is_required']) ? 'required' : ''; ?> data-field_label="<?php echo $custom_field['field_label']; ?>" data-field_type="<?php echo $custom_field['field_type']; ?>" data-field_id="<?php echo $custom_field['id']; ?>" type="text" class="form-control" name="<?php echo $custom_field['field_name']; ?>" autocomplete="off">
                                </div>
                            <?php elseif($custom_field['field_type'] == '1'):?>
                                <div class="rentmy-form-group">
                                <label for="<?php echo $custom_field['field_name']; ?>"><?php echo $custom_field['field_label']; ?> <?php echo !empty($custom_field['field_is_required']) ? '*' : ''; ?></label>
                                <select <?php echo !empty($custom_field['field_is_required']) ? 'required' : ''; ?> data-field_label="<?php echo $custom_field['field_label']; ?>" data-field_type="<?php echo $custom_field['field_type']; ?>" data-field_id="<?php echo $custom_field['id']; ?>" class="form-control" name="<?php echo $custom_field['field_name']; ?>">
                                    <?php $field_values = explode(';', $custom_field['field_values']); ?>
                                    <?php foreach($field_values as $option):?>
                                        <option value="<?php echo strtolower($option); ?>"><?php echo ucwords($option); ?></option>
                                    <?php endforeach;?>
                                </select>
                                </div>
                            <?php elseif($custom_field['field_type'] == '2'):?>
                                <div class="rentmy-form-group">
                                    <label for="<?php echo $custom_field['field_name']; ?>"><?php echo $custom_field['field_label']; ?> <?php echo !empty($custom_field['field_is_required']) ? '*' : ''; ?></label>
                                    <input <?php echo !empty($custom_field['field_is_required']) ? 'required' : ''; ?> data-field_label="<?php echo $custom_field['field_label']; ?>" data-field_type="<?php echo $custom_field['field_type']; ?>" data-field_id="<?php echo $custom_field['id']; ?>" type="file" class="form-control" name="<?php echo $custom_field['field_name']; ?>" autocomplete="off">
                                </div>
                            <?php else:?>
                                <p class="rentmy-errore-msg">This field type does not supports. Please contact admin.</p>
                            <?php endif; ?>

                        <?php endforeach;?>
                    </div>
                <?php endif; ?>

                <div class="rentmy-form-group-100">
                    <label for="agreementCheckProceed" class="checkbox-container custom-control-label">
                        <input required class="custom-control-input agreementCheckProceed" id="agreementCheckProceed" type="checkbox"
                            value="agreed"/>
                        <p>
                            <?php echo strip_tags($checkout_label['terms_and_condition']); ?>
                            <a class="rentmy-terms-and-condition" href="javascript:void(0)">terms & conditions</a>
                        </p>
                        <span class="checkmark"></span>
                    </label>
                </div>

                <!--      terms of condition popup starts      -->
                <?php if(!empty($terms_condition)): ?>
                <div class="pop-up-content">
                    <div class="content">
                        <div class="popup-title">
                            <h1><?php echo !empty($terms_condition['heading']) ? $terms_condition['heading'] : $terms_condition['name']; ?></h1>
                            <span class="close">&#10005</span>
                        </div>
                        <div class="popup-container">
                            <div class="popup-conetent-pragraph">
                                <div class="popup-conetent-pragraph-inner">
                                    <?php echo $terms_condition['contents']['content']; ?>
                                </div>
                            </div>
                            <div class="popup-footer">
                                <div class="popup-checkbox-area">
                                    <label class="popup-checkbox-container">
                                        I have read and agree with the Terms & Conditions.
                                        <input type="checkbox" class="agreementCheckProceed">
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <div class="popup-btn-area">
                                    <button class="popup-back-btn">Back</button>
                                    <button type="button" class="popup-continue-btn popup-continue-didabledbtn" disabled>Continue</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!--      ends here      -->

                <?php if(!empty($signature['online']) && $signature['online'] == '1'): ?>
                    <div class="rentmy-form-row signature-pad-wrapper" style="display: none">
                        <canvas id="signature-pad" class="signature-pad" width=500 height=200></canvas>
                        <p>
                            <span class="clear-signature">Clear</span>
                            <span class="">Signature</span>
                        </p>
                        <input type="hidden" readonly id="signature" name="signature">
                    </div>
                <?php endif; ?>

                <div class="checkout-back-continue">
                    <a class="back-continue-btn back-btn" name="rentmy-checkout"
                    href="<?php echo home_url('/rentmy-cart'); ?>"><?php echo $checkout_label['btn_back']; ?></a>
                    <input disabled type="submit" class="back-continue-btn checkout-continue-btn"
                        id="rentmy-btn-checkout-billing"
                        data-succeredirect="<?php echo home_url('/rentmy-checkout/?step=fulfillment'); ?>"
                        data-step="<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" name="rentmy-checkout"
                        value="Proceed to checkout"/>
                </div>
            </form>
        </div>
    </div>
    <?php
}

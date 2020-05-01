<div class="date-time-picker rnb-component-wrapper">
    <?php
    $labels = redq_rental_get_settings(get_the_ID(), 'labels', array('pickup_date'));
    $displays = redq_rental_get_settings(get_the_ID(), 'display');
    $labels = $labels['labels'];
    $displays = $displays['display'];
    ?>

    <?php if (isset($displays['pickup_date']) && $displays['pickup_date'] !== 'closed'): ?>
        <h5><?php echo esc_attr($labels['pickup_datetime']); ?></h5>
        <span class="pick-up-date-picker">
			<i class="fa fa-calendar"></i>
			<input type="text" name="pickup_date" id="pickup-date"
                   placeholder="<?php echo esc_attr($labels['pickup_date']); ?>" value="" readonly>
		</span>
    <?php endif; ?>

    <?php if (isset($displays['pickup_time']) && $displays['pickup_time'] !== 'closed'): ?>
        <span class="pick-up-time-picker">
			<i class="fa fa-clock-o"></i>
			<input type="text" name="pickup_time" id="pickup-time"
                   placeholder="<?php echo esc_attr($labels['pickup_time']); ?>" value="" readonly>
		</span>
    <?php endif; ?>
</div>

<div id="pickup-modal-body" style="display: none;">
    <h5 class="pick-modal-title"><?php echo esc_attr($labels['pickup_datetime']); ?></h5>
    <div id="mobile-datepicker"></div>
    <span id="cal-close-btn">
		<i class="fa fa-times"></i>
	</span>
    <button type="button" id="cal-submit-btn">
        <i class="fa fa-check-circle"></i>
        <?php echo esc_html__('Submit', 'redq-rental'); ?>
    </button>
</div>


<div class="date-time-picker rnb-component-wrapper">
    <?php
    $labels = redq_rental_get_settings(get_the_ID(), 'labels', array('return_date'));
    $displays = redq_rental_get_settings(get_the_ID(), 'display');
    $labels = $labels['labels'];
    $displays = $displays['display'];
    ?>
    <?php if (isset($displays['return_date']) && $displays['return_date'] !== 'closed'): ?>
        <h5><?php echo esc_attr($labels['return_datetime']); ?></h5>
        <span class="drop-off-date-picker">
			<i class="fa fa-calendar"></i>
			<input type="text" name="dropoff_date" id="dropoff-date"
                   placeholder="<?php echo esc_attr($labels['return_date']); ?>" value="" readonly>
		</span>
    <?php endif; ?>

    <?php if (isset($displays['return_time']) && $displays['return_time'] !== 'closed'): ?>
        <span class="drop-off-time-picker">
			<i class="fa fa-clock-o"></i>
			<input type="text" name="dropoff_time" id="dropoff-time"
                   placeholder="<?php echo esc_attr($labels['return_time']); ?>" value="" readonly>
		</span>
    <?php endif; ?>
</div>

<div id="dropoff-modal-body" style="display: none;">
    <h5 class="drop-modal-title"><?php echo esc_attr($labels['return_datetime']); ?></h5>
    <div id="drop-mobile-datepicker"></div>
    <span id="drop-cal-close-btn">
		<i class="fa fa-times"></i>
	</span>
    <button type="button" id="drop-cal-submit-btn">
        <i class="fa fa-check-circle"></i>
        <?php echo esc_html__('Submit', 'redq-rental'); ?>
    </button>
</div>

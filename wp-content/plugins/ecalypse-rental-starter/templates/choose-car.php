<?php
/**
 * Choose car - filter
 *
 * @package WordPress
 * @subpackage EcalypseRental
 * @since EcalypseRental 3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
get_header(); ?>
<div class="ecalypse-rental-custom-theme">
	<section class="content">
		<div class="container">
			<ul class="steps columns-4 no-space">
				<li>
					<a href="<?= home_url(); ?>">
						<span class="steps-number">1</span> <?= EcalypseRental::t('Create request') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
				<li class="active">
					<a href="javascript:void(0);">
						<span class="steps-number">2</span> <?= EcalypseRental::t('Choose a car') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);">
						<span class="steps-number">3</span> <?= EcalypseRental::t('Services &amp; book') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);">
						<span class="steps-number">4</span> <?= EcalypseRental::t('Summary') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
			</ul>
		
		<?php include(EcalypseRentalTheme::get_file_template_path('choose-car-content.php')); ?>
		
	</div>
	<!-- .container -->
	
</section>
<!-- .content -->	
</div>
		
<?php get_footer(); ?>
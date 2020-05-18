<?php

class WPML_TM_AMS_ATE_Console_Section_Factory implements IWPML_TM_Admin_Section_Factory {

	/**
	 * Returns an instance of a class implementing \IWPML_TM_Admin_Section.
	 *
	 * @return \IWPML_TM_Admin_Section
	 */
	public function create() {
		if ( WPML_TM_ATE_Status::is_enabled() && wpml_tm_ams_ate_factories()->is_ate_active() ) {
			return WPML\Container\make( 'WPML_TM_AMS_ATE_Console_Section' );
		}

		return null;
	}
}

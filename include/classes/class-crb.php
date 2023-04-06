<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Theme_CF
{
    public function __construct(){
        add_action( 'after_setup_theme', array ( $this , 'crb_load' ) );
        add_action( 'carbon_fields_register_fields', array( $this , 'ag_settings_panel' ) );
        // add_action( 'carbon_fields_register_fields', array( $this , 'ag_tax_select' ) );
    }

    public function crb_load() {
        require_once ( TJR_DIR .'/include/lib/vendor/autoload.php' );
        \Carbon_Fields\Carbon_Fields::boot();
    }

    public function ag_settings_panel() {

        Container::make( 'theme_options','tjr', __( 'TajerZone Dashboard' ) )
        
        ->add_tab(
            __( 'General Options', 'tjr' ),
            array(
                Field::make( 'text', 'ship_end_date', __( 'Days Limit The order turns into delivered automatically', 'tjr' ) ),
                // Field::make( 'text', 'client_secret', __( 'options 1', 'tjr' ) ),
            )
        )
        // ->add_tab( __( 'Email Template' ), array(
        //     Field::make( 'textarea', 'email_template', __( 'options 1', 'tjr' ) ),
        // ) )

        
        // ->add_tab(
        //     __( 'APP Options', 'ag' ),
        //     array(
        //         Field::make( 'file', 'ag_logo', __( 'app logo' ) )
	    //         ->set_type( array( 'image' ) )->set_value_type( 'url' ),
        //         Field::make( 'file', 'ag_reload_gif', __( 'app reload gif' ) )
	    //         ->set_type( array( 'image' ) )->set_value_type( 'url' ),
        //         Field::make( 'file', 'ag_json', __( 'app json' ) )
	    //         ->set_type( array( 'json' ) )->set_value_type( 'url' ),
                
               
        //     )
        // )
        ;

            //  // Display container on Book Category taxonomy
            // Container::make( 'term_meta', __( 'Icon Font' ) )
            // ->where( 'term_taxonomy', '=', 'property_type' )
            // ->add_fields( array( 
            //     Field::make( 'icon', 'property_type_icon', __( 'Property Icon', 'crb' ) ),
            //  ) );

    }

}

new Theme_CF();
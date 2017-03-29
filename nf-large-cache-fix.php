<?php if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Plugin Name: Ninja Forms - Large Cache Fix
 * Description: Resolves the issue of large forms crashing on publish due to cache size.
 * Version: 0.0.1
 * Author: Kyle B. Johnson
 * Author URI: http://kylebjohnson.me
 */

final class NF_LargeCacheFix
{
    const CHUNK_SIZE  = 65535; // MySQL TEXT Type

    public function __construct()
    {
        add_action( 'ninja_forms_loaded', array( $this, 'init' ) );
    }

    public function init()
    {
        if( ! function_exists( 'Ninja_Forms' ) ) return;
        foreach( Ninja_Forms()->form()->get_forms() as $form ){
            add_filter( 'pre_option_nf_form_' . $form->get_id(), array( $this, 'pre_option' ), 10, 1 );
            add_filter( 'pre_update_option_nf_form_' . $form->get_id(), array( $this, 'pre_update_option' ), 10, 2 );
        }
    }

    public function pre_option( $value )
    {
        $filter = str_replace( 'pre_option_', '', current_filter() );
        $flag = $filter . '_chunks';
        if( ! get_option( $flag ) ) return $value;

        $new_value = '';
        $options = explode( ',', get_option( $flag ) );
        foreach( $options as $option ){
            $new_value .= get_option( $option );
        }

        return maybe_unserialize( $new_value );
    }

    public function pre_update_option( $new_value, $old_value )
    {
        if( is_array( $new_value ) ){
            $new_value = maybe_serialize( $new_value );
        }

        if ( self::CHUNK_SIZE > strlen($new_value) ) return $new_value;

        $filter = str_replace( 'pre_update_option_', '', current_filter() );

        $new_options = array();
        $chunks = explode("\r\n", chunk_split($new_value, self::CHUNK_SIZE));
        foreach ($chunks as $key => $value) {
            if( '' == $value ) continue;
            $option = $filter . '_' . $key;
            update_option($option, $value);
            $new_options[] = $option;
        }

        $flag = $filter . '_chunks';
        update_option($flag, implode(',', $new_options));
        return $flag;
    }

}

new NF_LargeCacheFix();

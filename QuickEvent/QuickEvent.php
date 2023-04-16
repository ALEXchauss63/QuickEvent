<?php
/*
Plugin Name: QuickEvent
Plugin URI: https://alexchauss63.github.io/Portfolio-/
Description: Un plugin pour créer et afficher des événements sur votre site web.
Version: 1.0
Author: Alex Chaussaroux
Author URI: https://alexchauss63.github.io/Portfolio-/
*/

// Enregistrer le type de message personnalisé pour les événements
function quickevent_create_post_type() {
    register_post_type( 'quickevent',
        array(
            'labels' => array(
                'name' => __( 'Events' ),
                'singular_name' => __( 'Event' )
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' )
        )
    );
}
add_action( 'init', 'quickevent_create_post_type' );

// Ajout d'une méta-boîte personnalisée pour la date de l'événement
function quickevent_add_meta_box() {
    add_meta_box( 'quickevent_date', 'Event Date', 'quickevent_date_callback', 'quickevent', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'quickevent_add_meta_box' );

// Fonction de rappel pour la méta-boîte personnalisée
function quickevent_date_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'quickevent_nonce' );
    $quickevent_date = get_post_meta( $post->ID, 'quickevent_date', true );
    echo '<label for="quickevent_date_field">' . __( 'Event Date' ) . '</label>';
    echo '<input type="date" id="quickevent_date_field" name="quickevent_date_field" value="' . esc_attr( $quickevent_date ) . '" size="25" />';
}

// Enregistrer les données de la méta-boîte personnalisée
function quickevent_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['quickevent_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['quickevent_nonce'], basename( __FILE__ ) ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && 'quickevent' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }
    if ( ! isset( $_POST['quickevent_date_field'] ) ) {
        return;
    }
    $my_data = sanitize_text_field( $_POST['quickevent_date_field'] );
    update_post_meta( $post_id, 'quickevent_date', $my_data );
}
add_action( 'save_post', 'quickevent_save_meta_box_data' );

// Afficher les événements sur le front-end
function quickevent_display_events() {
    $args = array(
        'post_type' => 'quickevent',
        'posts_per_page' => -1,
        'meta_key' => 'quickevent_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    );
    $events = new WP_Query( $args );
    if ( $events->have_posts() ) {
        while ( $events->have_posts() ) {
            $events->the_post();
            $quickevent_date = get_post_meta( get_the_ID(), 'quickevent_date', true );
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<p><strong>' . __( 'Date:' ) . '</strong> ' . esc_html( $quickevent_date ) . '</p>';
            the_content();
        }
        wp_reset_postdata();
    }
}

// Shortcode pour afficher les événements sur une page ou une publication
function quickevent_shortcode( $atts ) {
    ob_start();
    quickevent_display_events();
    return ob_get_clean();
}
add_shortcode( 'quickevent', 'quickevent_shortcode' );



<?php
// Plik: includes/class-fcm-frontend-admin.php

if (!defined('ABSPATH')) exit;

class FCM_Frontend_Admin {

    public function __construct() {
        add_shortcode('fc_mistrzaki_admin_panel', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'fc_mistrzaki_admin_panel')) {
            
            wp_register_script('fcm-frontend-script', false, ['jquery'], '3.4.0', true);
            
            // Przekazujemy dane z PHP do JavaScript
            $grupy_wiekowe = fcm_get_grupy_wiekowe();
            wp_localize_script('fcm-frontend-script', 'fcm_data', ['grupy' => $grupy_wiekowe]);

            $script = "
                jQuery(document).ready(function($) {
                    const modal = $('#fcm-group-modal');
                    const modalTitle = $('#fcm-modal-title');
                    const modalGroupsContainer = $('#fcm-modal-groups');
                    const closeModalBtn = $('#fcm-modal-close-btn');

                    // Logika otwierania okna
                    $('body').on('click', '#trening-calendar td:not(.pad)', function(e) {
                        e.preventDefault(); 

                        const cell = $(this);
                        const date = cell.data('date');
                        const formattedDate = cell.data('date-formatted');

                        modalTitle.text('Wybierz grupę na ' + formattedDate);

                        modalGroupsContainer.empty();
                        const baseUrl = new URL(window.location.href);
                        baseUrl.searchParams.set('trening_date', date);

                        $.each(fcm_data.grupy, function(key, label) {
                            const groupUrl = new URL(baseUrl.toString());
                            groupUrl.searchParams.set('grupa', key);
                            const button = $('<a></a>')
                                .attr('href', groupUrl.toString())
                                .addClass('button button-primary')
                                .text(label);
                            modalGroupsContainer.append(button);
                        });

                        modal.addClass('is-visible');
                    });

                    // Logika zamykania okna
                    function closeModal() {
                        modal.removeClass('is-visible');
                    }

                    closeModalBtn.on('click', closeModal);
                    modal.on('click', function(e) {
                        if ($(e.target).is(modal)) {
                            closeModal();
                        }
                    });
                });
            ";
            wp_add_inline_script('fcm-frontend-script', $script);

            wp_enqueue_style(
                'fcm-frontend-styles',
                FCM_PLUGIN_URL . 'assets/css/frontend-admin.css',
                [],
                '3.2.1'
            );

            wp_enqueue_script('fcm-frontend-script');
        }
    }

    public function render_shortcode() {
        ob_start();

        if (current_user_can('manage_options')) {
            $this->render_admin_panel();
        } else {
            echo '<h3>Zaloguj się, aby uzyskać dostęp do panelu</h3>';
            wp_login_form(['redirect' => get_permalink()]);
        }

        return ob_get_clean();
    }

    private function render_admin_panel() {
        if (isset($_GET['trening_date']) && isset($_GET['grupa'])) {
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-attendance.php';
        } elseif (isset($_GET['trening_date'])) {
            // Ten widok jest teraz zastąpiony przez modal, ale zostaje jako fallback
            include FCM_PLUGIN_PATH . 'templates/admin/treningi-groups.php';
        } else {
             include FCM_PLUGIN_PATH . 'templates/admin/treningi-calendar.php';
        }
    }
}

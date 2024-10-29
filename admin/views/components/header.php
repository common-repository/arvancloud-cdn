<?php
use WP_Arvan\Setup_Admin;
use WP_Arvan\Helper;
?>
<hr />
<div class="arvan-head">
    <div class="arvan-status">

        <div>
            <h2><?php echo esc_html_e( 'Status: ', 'arvancloud-cdn' ); ?></h2>
            <?php echo Setup_Admin::plugin_status(); ?>
        </div>

        <?php 
        if (Setup_Admin::is_plugin_setup_done()) {
            ?>
            <div>
                <h2><?php echo esc_html_e( 'Plan: ', 'arvancloud-cdn' ); ?></h2>
                <?php echo Setup_Admin::cdn_plan_level(); ?>
            </div>
            <?php
        }
        ?>

    </div>
    <div>
        <?php 
            $credentials_status = get_option( 'arvan-cloud-cdn-status' );

            if ($credentials_status === 'activated') {
                $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : false;

                if (Setup_Admin::is_plugin_setup_done() && $action !== 'change-api-key') {

                    echo wp_kses_post('<a class="arvan-btn change-api-key" href="'. esc_url( admin_url( '/admin.php?page=arvancloud-cdn&action=change-api-key' ) ) .'">' . esc_html__('Change API Key', 'arvancloud-cdn') . '</a>');

                } else {

                    echo wp_kses_post('<a class="arvan-btn back-to-options" href="'. esc_url( admin_url( '/admin.php?page=arvancloud-cdn' ) ) .'">' . esc_html__('Back to options', 'arvancloud-cdn') . '</a>');

                }

            }

            echo wp_kses_post('<a class="arvan-panel" target="_blank" rel="noopener noreferrer" href="https://panel.arvancloud.ir/cdn/' . Helper::get_site_domain(false) . '/dns">' . esc_html__('ArvanCloud panel →', 'arvancloud-cdn') . '</a>');
        ?>
    </div>
</div>

<hr />

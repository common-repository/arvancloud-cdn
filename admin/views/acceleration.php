<?php
use WP_Arvan\CDN\Acceleration;

$options = Acceleration::get_options();
if(!$options || !isset($options['status'])) {
    wp_safe_redirect( esc_url( admin_url( '/admin.php?page=arvancloud-cdn' ) ) );
}

$js_optimization    = false;
$css_optimization   = false;
if ($options['status'] == true && isset($options['extensions'])) {
    $js_optimization    = in_array('js', $options['extensions']);
    $css_optimization   = in_array('css', $options['extensions']);
}
?>


<div class="wrap ar-wrap">
    <h1><?php esc_html_e('Web Acceleration', 'arvancloud-cdn'); ?></h1>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <br>
    
    <div class="arvan-wrapper">
        <div class="arvan-card">
            <h2><?php esc_html_e('Web Acceleration', 'arvancloud-cdn'); ?></h2>
            <p><?php echo esc_html__( 'When this feature is enabled, ArvanCloud\'s edge servers will be shown the latest cache version of your site to the users if your website or web server goes down.', 'arvancloud-cdn' ) ?></p>
            <form class="arvancloud-options-form arvancloud-acceleration-options" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn' )); ?>" data-type="acceleration">
                <div class="cdn-option">
                    <div class="input-container">
                        <input type="checkbox" id="js_optimization" name="js_optimization" <?php echo $js_optimization ? 'checked' : ''; ?>/>
                        <label for="js_optimization">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('JavaScript Optimization', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('With enabling this feature the size of files will decrease by linearizing, summarizing, and removing comments.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>
                <div class="cdn-option">
                    <div class="input-container">
                        <input type="checkbox" id="css_optimization" name="css_optimization" <?php echo $css_optimization ? 'checked' : ''; ?>/>
                        <label for="css_optimization">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('CSS Optimization', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('With enabling this feature the size of files will decrease by linearizing, summarizing, and removing comments.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>


                <div id="lock-modal"></div>
                <div id="loading-circle"></div>
            </form>
        </div>
    </div>
    
 
    <br>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>
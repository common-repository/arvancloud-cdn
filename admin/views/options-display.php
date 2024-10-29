<?php
use WP_Arvan\CDN\CDN_Options;
use WP_Arvan\CDN\Cache\Page_Rules;

$automatic_cleaning = get_option( 'arvan-cloud-cdn-automatic-cleaning' );
$options = CDN_Options::get_cdn_options();
if(!$options) {
    //wp_safe_redirect( esc_url( admin_url( '/admin.php?page=arvancloud-cdn' ) ) );
}
$development_mode = $options ? ( $options['cache_developer_mode'] ?? null) : false;
$cache_consistent_uptime = $options ? ($options['cache_consistent_uptime']??null) : false;
$nonce_purge_cache = wp_create_nonce('arvan_purge_cache');

$setup_recommended_rules = Page_Rules::is_recommended_rule_enabled();
?>


<div class="wrap ar-wrap">
    <h1><?php echo esc_html_e( 'CDN Options', 'arvancloud-cdn' ) ?></h1>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <br>
    <div class="arvan-wrapper">
        <div class="arvan-card">
            <form class="arvancloud-options-form arvancloud-cdn-options-form" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn' )); ?>" data-type="cdn_options">
                <div class="cdn-option">
                    <div class="input-container">
                        <a class="purge-arvan-cache" href="<?php echo esc_url('admin.php?page=' . ACCDN_SLUG . '&purge_arvan_cache=1' . '&_wpnonce=' . esc_attr( $nonce_purge_cache )); ?>"><?php echo esc_html_e('Purge Cache', 'arvancloud-cdn'); ?></a>
                    </div>
                    <div class="detail-container">
                        <h3><?php echo esc_html_e('Purge Cache', 'arvancloud-cdn'); ?></h3>
                        <p><?php echo esc_html_e('This will help you to get the full version of the new data from your web server by clearing the cache data from Arvan servers.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>
                <div class="cdn-option">
                    <div class="input-container">
                        <input type="checkbox" id="setup_recommended_rules" name="setup_recommended_rules" <?php echo $setup_recommended_rules ? 'checked' : ''; ?>/>
                        <label for="setup_recommended_rules">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('Add WordPress Recomanded Page Rules', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('After activating of this feature, The cache in /wp-admin/* and /wp-login/* is ignored.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>
                <div class="cdn-option">
                    <div class="input-container">
                        <input type="checkbox" id="cache_consistent_uptime" name="cache_consistent_uptime" <?php echo $cache_consistent_uptime ? 'checked' : ''; ?>/>
                        <label for="cache_consistent_uptime">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php echo esc_html_e('Always Online', 'arvancloud-cdn'); ?></h3>
                        <p><?php echo esc_html_e('After activating of this feature, in case of website or webserver time out, the last saved version in arvancloud will be shown to the user. Arvancloud can only show the main page or dynamic pages if it has the permission to cache.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>
                <div class="cdn-option">
                    <div class="input-container">
                        <input type="checkbox" id="cache_developer_mode" name="cache_developer_mode" <?php echo $development_mode ? 'checked' : ''; ?>/>
                        <label for="cache_developer_mode">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php echo esc_html_e('Development Mode', 'arvancloud-cdn'); ?></h3>
                        <p><?php echo esc_html_e('Activating Development Mode will temporarily stop caching so that developers can work easier. Web optimization will also be disabled.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>
                <div class="cdn-option">
                    <div class="input-container">
                        <input type="checkbox" id="automatic_cleaning" name="automatic_cleaning" <?php echo $automatic_cleaning ? 'checked' : ''; ?>/>
                        <label for="automatic_cleaning">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php echo esc_html_e('Automatic cleaning', 'arvancloud-cdn'); ?></h3>
                        <p><?php echo esc_html_e('Automatically clear cache when a post or page or custom post type is edited or created or deleted.', 'arvancloud-cdn'); ?></p>
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

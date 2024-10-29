<?php
use WP_Arvan\CDN\Cache\Caching_Settings;

$options = Caching_Settings::get_options();
if(!$options || !isset($options['cache_status'])) {
    //wp_safe_redirect( esc_url( admin_url( '/admin.php?page=arvancloud-cdn' ) ) );
}

$cache_status = $options['cache_status']??null;

$expiration_times = [
    '0s' => __('Off', 'arvancloud-cdn'),
    '1s' => sprintf( __('%s Second', 'arvancloud-cdn'), 1),
    '2s' => sprintf( __('%s Seconds', 'arvancloud-cdn'), 2),
    '5s' => sprintf( __('%s Seconds', 'arvancloud-cdn'), 5),
    '10s' => sprintf( __('%s Seconds', 'arvancloud-cdn'), 10),
    '30s' => sprintf( __('%s Seconds', 'arvancloud-cdn'), 30),
    '30s' => sprintf( __('%s Seconds', 'arvancloud-cdn'), 30),
    '1m' => sprintf( __('%s Minute', 'arvancloud-cdn'), 1),
    '3m' => sprintf( __('%s Minutes', 'arvancloud-cdn'), 3),
    '5m' => sprintf( __('%s Minutes', 'arvancloud-cdn'), 5),
    '10m' => sprintf( __('%s Minutes', 'arvancloud-cdn'), 10),
    '15m' => sprintf( __('%s Minutes', 'arvancloud-cdn'), 15),
    '30m' => sprintf( __('%s Minutes', 'arvancloud-cdn'), 30),
    '1h' => sprintf( __('%s Hour', 'arvancloud-cdn'), 1),
    '3h' => sprintf( __('%s Hours', 'arvancloud-cdn'), 3),
    '5h' => sprintf( __('%s Hours', 'arvancloud-cdn'), 5),
    '10h' => sprintf( __('%s Hours', 'arvancloud-cdn'), 10),
    '12h' => sprintf( __('%s Hours', 'arvancloud-cdn'), 12),
    '24h' => sprintf( __('%s Day', 'arvancloud-cdn'), 1),
    '3d' => sprintf( __('%s Days', 'arvancloud-cdn'), 3),
    '7d' => sprintf( __('%s Days', 'arvancloud-cdn'), 7),
    '10d' => sprintf( __('%s Days', 'arvancloud-cdn'), 10),
    '15d' => sprintf( __('%s Days', 'arvancloud-cdn'), 15),
    '30d' => sprintf( __('%s Days', 'arvancloud-cdn'), 30),
];
?>


<div class="wrap ar-wrap">
    <h1><?php esc_html_e('Caching Settings', 'arvancloud-cdn'); ?></h1>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <br>
    
    <div class="arvan-wrapper">
        <div class="arvan-card">
            <h2><?php esc_html_e('Cache Level', 'arvancloud-cdn'); ?></h2>
            <p><?php echo esc_html__( 'By selecting the cache level, you can determine what content will be cached from your website in Arvan', 'arvancloud-cdn' ) ?></p>
            <form class="arvancloud-options-form arvancloud-cache_status" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn' )); ?>" data-type="cache_status">


                <div class="cdn-option">
                    <div class="input-container">
                        <input type="radio" id="off" name="cache_status" value="off" <?php echo $cache_status === 'off' ? 'checked' : ''; ?> />
                        <label for="off">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <div style="display: flex;align-items: center;">
                            <img class="cache-mode-img" src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/cache-off.svg'; ?>">
                            <h3><?php esc_html_e('Off', 'arvancloud-cdn'); ?></h3>
                        </div>
                        <p><?php esc_html_e('No content will be cached in Arvan. Requests will be sent directly to the origin servers', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>

                <div class="cdn-option">
                    <div class="input-container">
                        <input type="radio" id="uri" name="cache_status" value="uri" <?php echo $cache_status === 'uri' ? 'checked' : ''; ?> />
                        <label for="uri">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <div style="display: flex;align-items: center;">
                            <img class="cache-mode-img" src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/cache-ignore_querystring.svg'; ?>">
                            <h3><?php esc_html_e('Ignore QueryString', 'arvancloud-cdn'); ?></h3>
                        </div>
                        <p><?php esc_html_e('If the URL of a file contains different query strings, only one copy of that file will be cached', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>

                <div class="cdn-option">
                    <div class="input-container">
                        <input type="radio" id="query_string" name="cache_status" value="query_string" <?php echo $cache_status === 'query_string' ? 'checked' : ''; ?> />
                        <label for="query_string">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <div style="display: flex;align-items: center;">
                            <img class="cache-mode-img" src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/cache-apply_querystring.svg'; ?>">
                            <h3><?php esc_html_e('Apply QueryString', 'arvancloud-cdn'); ?></h3>
                        </div>
                        <p><?php esc_html_e('If the Query Strings are different, different versions of the files will be cached in Arvan', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>

                <div class="cdn-option">
                    <div class="input-container">
                        <input type="radio" id="advance" name="cache_status" value="advance" <?php echo $cache_status === 'advance' ? 'checked' : ''; ?> />
                        <label for="advance">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <div style="display: flex;align-items: center;">
                            <img class="cache-mode-img" src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/cache-querystring_cookie.svg'; ?>">
                            <h3><?php esc_html_e('Apply QueryString + Cookie', 'arvancloud-cdn'); ?></h3>
                        </div>
                        <p><?php esc_html_e('In addition to Query String, it also considers the values of cookies in caching files', 'arvancloud-cdn'); ?></p>
                        <div class="cache-mode-advance-options" <?php if ($cache_status !== 'advance') echo 'style="display: none;"'; ?>>
                            <a class="edit-settings edit-settings-cache"><?php esc_html_e('Edit Settings', 'arvancloud-cdn'); ?></a>
                        </div>
                    </div>
                </div>

    <div id="side-modal" class="side-modal-cache">
        <div class="side-modal-wrapper">
            <div class="side-modal-heading">
                <h3><?php esc_html_e('QueryString + Cookie Settings', 'arvancloud-cdn'); ?></h3>
                <button class="close">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="v1:ar-icon v1:ar-icon-times"><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4L4 12"></path> <path d="M4 4L12 12"></path></g></svg>
                </button>
            </div>
            <form>
                <div class="ar-checkbox-wrapper">
                    <div class="ar-checkbox">
                        <input id="cache_args" type="checkbox" name="cache_args" <?php echo $options['cache_args'] ? 'checked' : ''; ?>>
                        <label for="cache_args">Toggle</label>
                    </div>
                    <span class="ar-label"><?php _e('Apply All QueryString', 'arvancloud-cdn') ?></span>
                </div>
                <div class="ar-checkbox-wrapper">
                    <div class="ar-checkbox">
                        <input id="cache_scheme" type="checkbox" name="cache_scheme" <?php echo $options['cache_scheme'] ? 'checked' : ''; ?>>
                        <label for="cache_scheme">Toggle</label>
                    </div>
                    <span class="ar-label"><?php _e('Apply Http/Https', 'arvancloud-cdn') ?></span>
                </div>

                <br>
                <hr>
                <br>

                <div class="ar-text-wrapper">
                    <span class="ar-label"><?php _e('Apply Special QueryString', 'arvancloud-cdn') ?></span>
                    <div class="ar-text">
                        <input id="cache_arg" type="text" name="cache_arg" value="<?php echo $options['cache_arg'] ?>">
                    </div>
                </div>
                <div class="ar-text-wrapper">
                    <span class="ar-label"><?php _e('Custom Variable in Cookie', 'arvancloud-cdn') ?></span>
                    <div class="ar-text">
                        <input id="cache_cookie" type="text" name="cache_cookie" value="<?php echo $options['cache_cookie'] ?>">
                    </div>
                </div>

                <div class="ar-submit-bottons-modal">
                    <button class="ar-cancel-modal"><?php esc_html_e('Cancel', 'arvancloud-cdn'); ?></button>
                    <button class="ar-submit-modal"><?php esc_html_e('Save', 'arvancloud-cdn'); ?></button>
                </div>
            </form>
        </div>
    </div>

                <div id="lock-modal"></div>
                <div id="loading-circle"></div>
            </form>
        </div>


        <div class="arvan-card">
            <h2><?php esc_html_e('Cache Expiration Time', 'arvancloud-cdn'); ?></h2>
            <form class="arvancloud-options-form arvancloud-cache_expiration_time" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn-caching' )); ?>" data-type="cache_expiration_time">
                <div class="expiration_times-row">
                    <div class="expiration_times-col">
                        <div>
                            <label for="cache_page_200"><?php _e('Cache Max Age', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" name="cache_page_200" id="cache_page_200">
                                <?php foreach($expiration_times as $key => $value) {
                                    echo '<option value="' . $key . '" ' . selected($options['cache_page_200'], $key, true) . '>' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="cache_page_any"><?php _e('Cache Max Age for Other Pages', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" name="cache_page_any" id="cache_page_any">
                                <?php foreach($expiration_times as $key => $value) {
                                    echo '<option value="' . $key . '" ' . selected($options['cache_page_any'], $key, true) . '>' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="expiration_times-col">
                        <div>
                            <label for="cache_browser"><?php _e('Browser Cache TTL', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" name="cache_browser" id="cache_browser">
                                <option value="default" <?php selected($options['cache_browser'], 'default', true); ?>><?php _e('Default', 'arvancloud-cdn'); ?></option>
                                <?php foreach($expiration_times as $key => $value) {
                                    echo '<option value="' . $key . '" ' . selected($options['cache_browser'], $key, true) . '>' . $value . '</option>';
                                }
                                ?>
                                <option value="180d" <?php selected($options['cache_browser'], '180d', true); ?>><?php echo sprintf( __('%s Days', 'arvancloud-cdn'), 180) ?></option>
                                <option value="365d" <?php selected($options['cache_browser'], '365d', true); ?>><?php echo sprintf( __('%s Days', 'arvancloud-cdn'), 365) ?></option>
                            </select>
                        </div>
                        <div>
                            <label for="cache_ignore_sc"><?php _e('Cache Pages With Set-cookie', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" name="cache_ignore_sc" id="cache_ignore_sc">
                                <option value="off" <?php echo selected($options['cache_ignore_sc']); ?>><?php _e('Off', 'arvancloud-cdn'); ?></option>
                                <option value="on" <?php echo selected($options['cache_ignore_sc']); ?>><?php _e('On', 'arvancloud-cdn'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="ar-submit-bottons-modal" style="width: 100%;margin-top: 20px;justify-content: end;">
                        <input type="hidden" name="expiration_times_nonce" value="<?php echo wp_create_nonce('expiration_times'); ?>">
                        <input type="hidden" name="expiration_times_data" value="1">
                        <button type="submit" class="ar-submit-modal"><?php esc_html_e('Save', 'arvancloud-cdn'); ?></button>
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
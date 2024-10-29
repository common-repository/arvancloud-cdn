<?php
use WP_Arvan\Security\HTTPS_Settings;
use WP_Arvan\CDN\Domain_Info;

$HTTPS = new HTTPS_Settings(true);


$is_DNS_Cloud_enabled = Domain_Info::$DNS_Cloud;


$HSTS_duration = [
    '1mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 1),
    '2mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 2),
    '3mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 3),
    '4mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 4),
    '5mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 5),
    '6mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 6),
    '12mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 12),
    '24mo'    => sprintf( __('%s Month', 'arvancloud-cdn'), 24),
];

?>


<div class="wrap ar-wrap">
    <h1><?php esc_html_e('HTTPS Settings', 'arvancloud-cdn'); ?></h1>
    <?php 
    if ( !$is_DNS_Cloud_enabled )
        echo wp_kses_post('<div class="notice notice-error is-dismissible"><p>'. esc_html__( 'HTTP Protocol will only be activated for the records with an enabled cloud icon. ', 'arvancloud-cdn' ) .'</p></div>');
    ?>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <br>
    
    <div class="arvan-wrapper">
        <div class="arvan-card">
            <h2><?php esc_html_e('HTTPS Settings', 'arvancloud-cdn'); ?></h2>
            <form class="arvancloud-options-form arvancloud-acceleration-options" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn' )); ?>" data-type="https">

            

                <div class="cdn-option" id="HTTPS">
                    <div class="input-container">
                        <input  id="ssl_status" type="checkbox" name="ssl_status" <?php echo $HTTPS->is_HTTPS ? 'checked' : ''; ?>/>
                        <label for="ssl_status">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('Activate HTTPS', 'arvancloud-cdn'); ?></h3>
                        <p><?php _e('SSL/TLS secure protocol will be activated for your domain <br> <strong>To enable HTTPS on the domain, you need to replace your name servers with ArvanCloud NSs and enable the cloud icon for the domain.</strong>', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>



                <div class="cdn-option <?php echo !$HTTPS->is_HTTPS ? 'disabled' : ''; ?>" id="HTTPS_Default">
                    <div class="input-container">
                        <input  id="https_redirect" type="checkbox" name="https_redirect" <?php echo $HTTPS->is_HTTPS_Default ? 'checked' : ''; ?>/>
                        <label for="https_redirect">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('Set HTTPS as Default', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('All HTTP requests will automatically be redirected to HTTPS.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>


                <div class="cdn-option <?php echo !$HTTPS->is_HTTPS ? 'disabled' : ''; ?>" id="HTTPS_Rewrite">
                    <div class="input-container">
                        <input  id="replace_http" type="checkbox" name="replace_http" <?php echo $HTTPS->is_HTTPS_Rewrite ? 'checked' : ''; ?>/>
                        <label for="replace_http">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('Rewrite Files Based on HTTPS', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('Resource files and website links will be converted to HTTPS. This will prevent mixed content error.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>


                <div class="cdn-option <?php echo !$HTTPS->is_HTTPS_Default ? 'disabled' : ''; ?>" id="HSTS">
                    <div class="input-container">
                        <a class="edit-settings"><?php esc_html_e('Edit Settings', 'arvancloud-cdn'); ?></a>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('HSTS Protocol', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('HSTS protocol improves the security of request transmission and prevents MITM attacks.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>


                <div id="lock-modal"></div>
                <div id="loading-circle"></div>
            </form>
        </div>
    </div>

    <div id="side-modal" class="side-modal-HSTS">
        <div class="side-modal-wrapper">
            <div class="side-modal-heading">
                <h3><?php esc_html_e('HSTS Settings', 'arvancloud-cdn'); ?></h3>
                <button class="close">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="v1:ar-icon v1:ar-icon-times"><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4L4 12"></path> <path d="M4 4L12 12"></path></g></svg>
                </button>
            </div>
            <form>
                <div class="ar-bg-blue-gray">
                    <p><?php esc_html_e('HSTS or Security Transport Strict Http will improve the security of requests\' transmission from HTTP to HTTPS and prevents MITM attacks.', 'arvancloud-cdn'); ?></p>
                    <p><?php esc_html_e('Attention: Visitors\' browsers will cache these settings. Please consider purging by cache expiration when applying HSTS settings.', 'arvancloud-cdn'); ?></p>
                    <hr>
                    <div class="ar-simple-checkbox ar-agreement">
                        <input id="ar-https-agreement" type="checkbox" role="checkbox" value="I Understand and Accept the Rules."> 
                        <label for="ar-https-agreement" ><?php _e('I Understand and Accept the Rules.', 'arvancloud-cdn') ?></label>
                    </div>
                </div>
                <br>
                <div class="ar-checkbox-wrapper disabled">
                    <div class="ar-checkbox">
                        <input id="hsts_status" type="checkbox" name="hsts_status" <?php echo $HTTPS->is_HSTS ? 'checked' : ''; ?>>
                        <label for="hsts_status">Toggle</label>
                    </div>
                    <span class="ar-label"><?php _e('Enable HSTS Protocol', 'arvancloud-cdn') ?></span>
                </div>
                
                <div class="ar-dropdown-wrapper disabled">
                    <span class="ar-label"><?php _e('What is the duration in which the browser is required to show the HTTPS version of the website?', 'arvancloud-cdn') ?></span>
                    <select class="ar-dropdown" name="hsts_max_age">
                        <?php 
                        foreach ($HSTS_duration as $key => $title) {
                            $selected = $HTTPS->hsts_max_age == $key ? 'selected' : '';
                            echo '<option value="'. $key .'"'. $selected .'>' . $title .'</option>';
                        }
                        ?>
                    </select>
                </div>

                <br>
                <hr>
                <br>
                <br>

                <div class="ar-checkbox-wrapper disabled">
                    <div class="ar-checkbox">
                        <input id="hsts_subdomain" type="checkbox" name="hsts_subdomain" <?php echo $HTTPS->is_HSTS_subdomain ? 'checked' : ''; ?>>
                        <label for="hsts_subdomain">Toggle</label>
                    </div>
                    <span class="ar-label"><?php _e('Use HSTS for all Subdomains', 'arvancloud-cdn') ?></span>
                </div>
                <div class="ar-checkbox-wrapper disabled">
                    <div class="ar-checkbox">
                        <input id="hsts_preload" type="checkbox" name="hsts_preload" <?php echo $HTTPS->is_HSTS_preload ? 'checked' : ''; ?>>
                        <label for="hsts_preload">Toggle</label>
                    </div>
                    <span class="ar-label"><?php _e('Prepare the Domain for the Search Engines', 'arvancloud-cdn') ?></span>
                </div>

                <div class="ar-submit-bottons-modal">
                    <button class="ar-cancel-modal"><?php esc_html_e('Cancel', 'arvancloud-cdn'); ?></button>
                    <button class="ar-submit-modal disabled"><?php esc_html_e('Save', 'arvancloud-cdn'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
 
    <br>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>
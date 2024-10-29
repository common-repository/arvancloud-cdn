<?php

$credentials_status = get_option( 'arvan-cloud-cdn-status' );
?>


<div class="wrap ar-wrap">
    <h1><?php echo esc_html_e( 'CDN General Settings', 'arvancloud-cdn' ) ?></h1>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <h3><?php echo esc_html_e( 'Configure CDN API', 'arvancloud-cdn' ) ?></h3>
    <form class="arvancloud-cdn-config-form" method="post" action="<?php echo esc_url(admin_url( '/admin.php?page=arvancloud-cdn' )); ?>">
        <section class="ar-cdn-container">
            <div class="ar-cdn-box">
                <label for="accdn-api-key">API Key</label>
                <input type="text" name="accdn-api-key" value="" autocomplete="off"  placeholder="<?php echo !empty($credentials_status) ? esc_html_e( "-- not shown --", 'arvancloud-cdn' ) : 'Apikey ********-****-****-****-************' ?> ">
            </div>
            <div class="ar-cdn-box">
                <a class="get-api-key" href="https://panel.arvancloud.ir/profile/machine-user/" target="_blank" rel="noopener noreferrer"><?php echo esc_html_e('Get API Key', 'arvancloud-cdn'); ?></a>
            </div>
            <p><button type="submit" class="button button-primary" name="config_arvancloud_api_key" value="1"><?php echo esc_html_e( "Save", 'arvancloud-cdn' ) ?></button></p>
        </section>
    </form>
    <br>

    <br>
    <br>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>

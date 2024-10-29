<?php
use WP_Arvan\Security\DDoS_Protection;

$ddos = new DDoS_Protection;
$mode = $ddos->get_mode();
$ttl  = $ddos->ttl;

$ttls = [
    '0' => __('Automatic TTL', 'arvancloud-cdn'),
    '100' => sprintf( __('%s Seconds', 'arvancloud-cdn'), 100),
    '9000' => sprintf( __('%s Hours', 'arvancloud-cdn'), 2.5),
    '86400' => sprintf( __('%s Day', 'arvancloud-cdn'), 1),
];

if(empty($mode)) {
    //wp_safe_redirect( esc_url( admin_url( '/admin.php?page=arvancloud-cdn' ) ) );
}

?>

<div class="wrap ar-wrap">
    <h1><?php esc_html_e('DDoS Protection', 'arvancloud-cdn'); ?></h1>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <br>
    
    <div class="arvan-wrapper">
        <div class="arvan-card">
            <h2><?php esc_html_e('DDoS Protection', 'arvancloud-cdn'); ?></h2>
            <form class="arvancloud-options-form arvancloud-ddos-protection-options" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn' )); ?>" data-type="ddos_protection">



                <div class="cdn-option">
                    <div class="input-container">
                        <img src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/no-challenge.svg'; ?>">
                        <h4><?php esc_html_e('No Challenge', 'arvancloud-cdn'); ?></h4>
                        <input type="radio" id="off" name="ddos_protection_mode" value="off" <?php echo $mode === 'off' ? 'checked' : ''; ?> />
                        <label for="off">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('No Challenge', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('All ArvanCloud servers protect against DDoS attacks automatically by using a combination of proven methods, which block manipulated packets and also block too many packets coming from the same source.', 'arvancloud-cdn'); ?></p>
                    </div>
                </div>


                <div class="cdn-option">
                    <div class="input-container">
                        <img src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/cookie-challenge.svg'; ?>">
                        <h4><?php esc_html_e('Cookie Challenge', 'arvancloud-cdn'); ?></h4>
                        <input type="radio" id="cookie" name="ddos_protection_mode" value="cookie" <?php echo $mode === 'cookie' ? 'checked' : ''; ?> />
                        <label for="cookie">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('Cookie Challenge', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('In addition to regular network layer protections, Arvancloud also targets and neutralizes bots that attack on layer 7 and simulate human behavior.', 'arvancloud-cdn'); ?></p>
                        <div class="cookie-ttl-wrapper <?php echo $mode === 'cookie' ? '' : 'ar-hidden'; ?>">

                            <label for="ttl.cookie"><?php esc_html_e('Challenge Expiration Time', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" id="ttl.cookie" name="ttl">
                                <?php
                                foreach($ttls as $key => $ttl) {
                                    $selected = ( $ttl == $key ) ? 'selected' : '';
                                    echo '<option value="'. $key .'" '. $selected .'>'. $ttl .'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="cdn-option">
                    <div class="input-container">
                        <img src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/js-challenge.svg'; ?>">
                        <h4><?php esc_html_e('JS Challenge', 'arvancloud-cdn'); ?></h4>
                        <input type="radio" id="javascript" name="ddos_protection_mode" value="javascript" <?php echo $mode === 'javascript' ? 'checked' : ''; ?> />
                        <label for="javascript">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('JS Challenge', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('In addition to general protection for network layer and layer 7 bots, Arvancloud also uses encryption to detect and neutralize advanced layer 7 bots. In their first atttempt to connect, users will see a page for a moment which identifies them as humans.', 'arvancloud-cdn'); ?></p>
                        <div class="js-ttl-wrapper <?php echo $mode === 'javascript' ? '' : 'ar-hidden'; ?>">

                            <label for="ttl.js"><?php esc_html_e('Challenge Expiration Time', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" id="ttl.js" name="ttl">
                                <?php
                                foreach($ttls as $key => $ttl) {
                                    $selected = ( $ttl == $key ) ? 'selected' : '';
                                    echo '<option value="'. $key .'" '. $selected .'>'. $ttl .'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="cdn-option">
                    <div class="input-container">
                        <img src="<?php echo ACCDN_PLUGIN_ROOT_URL . 'admin/assets/img/recaptcha.svg'; ?>">
                        <h4><?php esc_html_e('Recaptcha', 'arvancloud-cdn'); ?></h4>
                        <input type="radio" id="recaptcha" name="ddos_protection_mode" value="recaptcha" <?php echo $mode === 'recaptcha' ? 'checked' : ''; ?>/>
                        <label for="recaptcha">Toggle</label>
                    </div>
                    <div class="detail-container">
                        <h3><?php esc_html_e('Recaptcha', 'arvancloud-cdn'); ?></h3>
                        <p><?php esc_html_e('In addition to general protection and proffesional advanced bot detection, ArvanCloud uses a captcha that users have to recognize images, to identify them as humans. Since the traffic is proccessing of this pages is done outside your website, this is the most advanced and efficient way to counter layer 7 DDoS attacks.', 'arvancloud-cdn'); ?></p>
                        <div class="reacptcha-ttl-wrapper <?php echo $mode === 'reacptcha' ? '' : 'ar-hidden'; ?>">

                            <label for="ttl.reacptcha"><?php esc_html_e('Challenge Expiration Time', 'arvancloud-cdn'); ?></label>
                            <select class="ar-dropdown" id="ttl.reacptcha" name="ttl">
                                <?php
                                foreach($ttls as $key => $ttl) {
                                    $selected = ( $ttl == $key ) ? 'selected' : '';
                                    echo '<option value="'. $key .'" '. $selected .'>'. $ttl .'</option>';
                                }
                                ?>
                            </select>
                        </div>
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
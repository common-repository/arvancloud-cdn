<?php
use WP_Arvan\Security\Firewall;
use WP_Arvan\CDN\Domain_Info;
use WP_Arvan\Helper;

$firewall = new Firewall(true);
$cdn_plan_level = Domain_Info::$plan_level;
?>


<div class="wrap ar-wrap">
    <h1><?php esc_html_e('Firewall', 'arvancloud-cdn'); ?></h1>
    <?php 
    if ( $cdn_plan_level == 1 && count($firewall->rules??[]) == 10)
        echo wp_kses_post('<div class="notice notice-error is-dismissible"><p>'. esc_html__( 'You have reached the limit for the number of rules (10 rules) you can create at Basic plan. Check the Plans page for upgrades and more information.', 'arvancloud-cdn' ) .'</p></div>');
    ?>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php' ) ?>
    <br>
    
    <div class="arvan-wrapper">
        <div class="arvan-card">
            <form class="arvancloud-options-form arvancloud-acceleration-options" method="post" action="<?php echo esc_url( admin_url( '/admin.php?page=arvancloud-cdn' )); ?>" data-type="firewall">

                <br>
                <div class="firewall_rules_title">
                    <h3><?php _e('Firewall Rules', 'arvancloud-cdn') ?></h3>
                    <button class="arvan-btn-rules" id="add_firewall_rules" data-action="add"><?php _e('+ Add new rule', 'arvancloud-cdn') ?></button>
                </div>
                
                <?php
                if (!empty($firewall->rules)) {
                ?>

                <table id="firewall_rules">
                    <thead>
                        <tr>
                            <th class="firewall_rule_head firewall_rule_head--grabber"></th>
                            <th class="firewall_rule_head firewall_rule_head--counter"></th>
                            <th class="firewall_rule_head firewall_rule_head--name"><?php _e('Name', 'arvancloud-cdn') ?></th>
                            <th class="firewall_rule_head firewall_rule_head--description"><?php _e('Description', 'arvancloud-cdn') ?></th>
                            <th class="firewall_rule_head firewall_rule_head--access"><?php _e('Operator', 'arvancloud-cdn') ?></th>
                            <th class="firewall_rule_head firewall_rule_head--enabled-disabled"><?php _e('Status', 'arvancloud-cdn') ?></th>
                            <th class="firewall_rule_head firewall_rule_head--edit"></th>
                            <th class="firewall_rule_head firewall_rule_head--delete"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = 1;
                        foreach ($firewall->rules as $rule){
                            $is_enable = ($rule['is_enabled']) ? __('Enable', 'arvancloud-cdn') : __('Disable', 'arvancloud-cdn');
                            echo '<tr class="firewall_rule" data-rule_id="'. $rule['id'] .'" data-is_enabled="'. $rule['is_enabled'] .'">
                                <td class="firewall_rule--grabber"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="9" class="c-cdnFirewallVetitiRules__textureIcon"><g transform="translate(-.134 .254)" fill="currentColor"><rect width="3" height="3" rx="1.5" transform="translate(6.134 5.746)"></rect> <rect width="3" height="3" rx="1.5" transform="translate(6.134 -.254)"></rect> <rect width="3" height="3" rx="1.5" transform="translate(12.134 5.746)"></rect> <rect width="3" height="3" rx="1.5" transform="translate(12.134 -.254)"></rect></g></svg></td>
                                <td class="rule-counter">'. $counter .'</td>
                                <td class="firewall_rule--name">'. $rule['name'] .'</td>';
                            echo'</td>
                                <td class="firewall_rule--description">'. $rule['note'] .'</td>
                                <td class="firewall_rule--access">'. __($rule['action'], 'arvancloud-cdn') .'</td>
                                <td class="firewall_rule--enabled-disabled">'. $is_enable .'</td>
                                <td class="firewall_rule--edit"><button data-action="edit"><svg width="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13.47 13.52" class="c-cdnFirewallVetitiRules__penIcon"><g><g fill="currentColor"><path d="M.5 13.52a.5.5 0 01-.5-.61l.85-3.48A.48.48 0 011 9.2L9.37.71a2.4 2.4 0 013.44 3.34l-8.48 8.49a.56.56 0 01-.24.13l-3.47.84zM1.79 9.8l-.61 2.55 2.54-.62 8.35-8.35a1.4 1.4 0 10-2-2z"></path><path d="M12 4.62a.51.51 0 01-.36-.15L9 1.77a.51.51 0 010-.71.5.5 0 01.71 0l2.66 2.7a.5.5 0 010 .71.52.52 0 01-.37.15zM2.61 11.44a.51.51 0 01-.36-.15.5.5 0 010-.7l6.33-6.28a.49.49 0 01.7 0 .48.48 0 010 .7L3 11.3a.54.54 0 01-.39.14z"></path></g></g></svg></button></td>
                                <td class="firewall_rule--delete"><button data-action="delete"><svg width="14" aria-hidden="true" focusable="false" data-prefix="far" data-icon="trash-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-trash-alt fa-w-14 c-cdnFirewallVetitiRules__trashIcon"><path fill="currentColor" d="M268 416h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12zM432 80h-82.41l-34-56.7A48 48 0 0 0 274.41 0H173.59a48 48 0 0 0-41.16 23.3L98.41 80H16A16 16 0 0 0 0 96v16a16 16 0 0 0 16 16h16v336a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128h16a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16zM171.84 50.91A6 6 0 0 1 177 48h94a6 6 0 0 1 5.15 2.91L293.61 80H154.39zM368 464H80V128h288zm-212-48h24a12 12 0 0 0 12-12V188a12 12 0 0 0-12-12h-24a12 12 0 0 0-12 12v216a12 12 0 0 0 12 12z"></path></svg></button></td>
                            </tr>';
                            $counter++;
                        }
                        ?>

                    </tbody>
                </table>
                <?php
                    }
                ?>

                <div id="lock-modal"></div>
                <div id="loading-circle"></div>
            </form>
        </div>
    </div>

    <div id="side-modal" class="side-modal-firewall">
        <div class="side-modal-wrapper">
            <div class="side-modal-heading">
                <h3 class="create_rule"><?php esc_html_e('Create New Rule', 'arvancloud-cdn'); ?></h3>
                <h3 class="edit_rule hidden"><?php esc_html_e('Edit Rule', 'arvancloud-cdn'); ?></h3>
                <div style="display: flex; align-items: center;">
                    <div class="ar-text-wrapper" style="display: flex;min-width: 340px;flex-direction: row; align-items: center;margin: 0;margin-inline-end: 30px;">
                        <label for="action" style="    width: 140px;margin-inline-end: 10px;"><?php _e('Status', 'arvancloud-cdn') ?></label>
                        <select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="<?php _e('Select', 'arvancloud-cdn') ?>" name="is_enabled" style="min-width: 300px; width: 300px !important;">
                            <option value="1" selected><?php esc_html_e('Active', 'arvancloud-cdn'); ?></option>
                            <option value="0"><?php esc_html_e('Inactive', 'arvancloud-cdn'); ?></option>
                        </select>
                    </div>
                    <button class="close">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="v1:ar-icon v1:ar-icon-times"><g stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4L4 12"></path> <path d="M4 4L12 12"></path></g></svg>
                    </button>
                </div>
            </div>
            <form>

                <div style="display: flex;">
                    <div class="ar-text-wrapper ar-col-lg-6">
                        <div class="ar-input">
                            <label class="ar-label" for="name"><?php _e('Rule Name:', 'arvancloud-cdn') ?></label>
                            <input id="name" type="text" value="" name="name">
                        </div>
                    </div>
                    <div class="ar-text-wrapper ar-col-lg-6">
                        <div class="ar-input">
                            <label class="ar-label" for="note"><?php _e('Rule Description', 'arvancloud-cdn') ?></label>
                            <input id="note" type="text" value="" name="note">
                        </div>
                    </div>
                </div>
                <div class="ar-col-12">
                    <p style="font-size: 15px;"><?php _e('If request meet this conditions:', 'arvancloud-cdn'); ?></p>

                    <div class="firewall_rule_repeater">
                        <div class="ar-firewall-or">
                            <div class="ar-firewall-or-label">
                                <div class="ar-col-12 ar-col-md-12 ar-col-lg-3"><?php _e('Parameter', 'arvancloud-cdn'); ?></div>
                                <div class="ar-col-12 ar-col-md-12 ar-col-lg-3"><?php _e('Operator', 'arvancloud-cdn'); ?></div>
                                <div class="ar-col-12 ar-col-md-12 ar-col-lg-6"><?php _e('Value', 'arvancloud-cdn'); ?></div>
                            </div>
                            <div class="ar-firewall-and">
                                <div class="ar-col-12 ar-col-md-12 ar-col-lg-3">
                                    <select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="<?php _e('Select', 'arvancloud-cdn') ?>" name="filter_type">
                                        <option></option>
                                        <option value="ip.src">IP Source Address</option>
                                        <option value="ip.geoip.country">Country</option>
                                        <option value="http.request.uri.path">URI Path</option>
                                        <option value="http.host">Hostname</option>
                                    </select>
                                </div>
                                <div class="ar-col-12 ar-col-md-12 ar-col-lg-3">
                                    <select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="<?php _e('Select', 'arvancloud-cdn') ?>" name="filter_operator">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="ar-col-12 ar-col-md-12 ar-col-lg-6 filter_value">
                                    <input type="text" class="form-control" name="filter_value">    
                                </div>
                            </div>
                            <div class="ar-col-12">
                                <button id="ar-firewall-add" class="ar-btn-secondary"><?php _e('+ AND', 'arvancloud-cdn') ?></button>
                            </div>
                        </div>
                        <div class="ar-col-12" style="text-align: center;">
                            <button id="ar-firewall-or" class="ar-btn-secondary"><?php _e('+ OR', 'arvancloud-cdn') ?></button>
                        </div>
                    </div>

                    <br>
                    <div class="ar-text-wrapper">
                        <div class="ar-input">
                            <label class="ar-label" for="export_filter"><?php _e('Equal to:', 'arvancloud-cdn') ?></label>
                            <input id="export_filter" type="text" name="export_filter" disabled>
                        </div>
                    </div>
                </div>
                    <div class="ar-text-wrapper ar-col-lg-6">
                        <label for="action"><?php _e('Then...<br>Select an Operator', 'arvancloud-cdn') ?></label>
                        <select class="ar-dropdown ar-dropdown-select2 form-control" data-placeholder="<?php _e('Select', 'arvancloud-cdn') ?>" name="action">
                            <option value="allow" selected><?php esc_html_e('allow', 'arvancloud-cdn'); ?></option>
                            <option value="deny"><?php esc_html_e('deny', 'arvancloud-cdn'); ?></option>
                        </select>
                    </div>
                    <br>

                <div class="ar-submit-bottons-modal" style="margin-top: 20px;">
                    <button class="ar-cancel-modal"><?php esc_html_e('Cancel', 'arvancloud-cdn'); ?></button>
                    <button class="ar-submit-modal"><?php esc_html_e('Save', 'arvancloud-cdn'); ?></button>
                </div>
            </form>
        </div>
    </div>
    
 
    <br>
    <?php require_once( ACCDN_PLUGIN_ROOT . 'admin/views/components/footer.php' ); ?>
</div>
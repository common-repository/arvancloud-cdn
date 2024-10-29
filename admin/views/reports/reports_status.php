<?php

use WP_Arvan\CDN\Reports;

$time_periods = [
    '3h'    => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 3),
    '6h'    => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 6),
    '12h'   => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 12),
    '24h'   => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 24),
    '7d'    => sprintf( __('%s Week Ago', 'arvancloud-cdn'), 1),
    '30d'    => sprintf( __('%s Month Ago', 'arvancloud-cdn'), 1),
];

$time_period    = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '3h';
$time_period_t  = $time_periods[$time_period] ?? $time_period;
$report         = new Reports( 'response-time', $time_period, 'line' );
?>

<div class="wrap ar-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <?php require_once(ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php') ?>

    <div class="cdn-report-header">

        <nav class="cdn-nav-tab-wrapper">
        </nav> 
        <div class="cdn-report-head__titleContainer">
                    
            <label for="report_period"><?php _e('Range Date ', 'arvancloud-cdn'); ?></label>
            <select class="cdn-report-head__subTitle" name="report_period" id="report_period">
                <?php
                foreach ($time_periods as $key => $title) {
                    $selected = ($key === $time_period) ? 'selected' : '';
                    echo '<option value="'. $key .'" '. $selected .'>' . $title .'</option>';
                }
                ?>
            </select>

        </div>
    </div>

    <div class="arvan-wrapper" id="cdn_reports">
        <div class="arvan-card">
            <div class="cdn-report-head">
                <div>
                    <span class="cdn-report-head__title"><?php esc_html_e( 'Response Time Diagram', 'arvancloud-cdn' ) ?></span>
                    <p class="cdn-report-head__subTitle"><?php echo $time_period_t; ?></p>
                </div>
            </div>
            <hr>
            <div class="tab-content">
                <?php $chart_ID = 'chart_' . $report->report_type . '_' . $report->chart_type;  ?>
                <div class="report">
                    <canvas id="<?php echo $chart_ID; ?>"></canvas>
                </div>
                <?php $report->print_chart_script( $chart_ID ) ?>
            </div>
            
        </div>

        <div class="arvan-reports-doughnuts" style="flex-direction: column;">
            <div class="arvan-reports-doughnut" style="width: 100%;">
                <?php
                $report         = new Reports( 'status/summary', $time_period, 'doughnut' );
                ?>
                <div class="arvan-card">
                    <div class="cdn-report-head">
                        <div>
                            <span class="cdn-report-head__title"><?php esc_html_e( 'Status Codes - Summary', 'arvancloud-cdn' ); ?></span>
                            <p class="cdn-report-head__subTitle"><?php echo $time_period_t; ?></p>
                        </div>

                    </div>
                    <div class="report-with-labels">
                        <div class="report">
                            <canvas id="<?php echo $report->chart_ID;; ?>"></canvas>
                        </div>
                        <div class="report-labels">
                            <?php
                            foreach($report->table_of_labels as $label) {
                                ?>
                                <div class="ar-report-label">
                                    <div class="ar-report-label-left" ><div class="label-cube" style="background-color: <?php echo $label['color'] ?>;"></div><?php echo $label['label'] ?></div>
                                    <div class="ar-report-label-right"><?php echo $label['value'] ?></div>
                                </div>
                                <?php
                            }
                            ?>

                            <div class="label"></div>
                        </div>
                    </div>
                    <?php $report->print_chart_script(); ?>
                </div>
            </div>

            <div class="arvan-reports-line" style="width: 100%;">
                <?php
                $report         = new Reports( 'status', $time_period, 'line' ); 
                ?>
                <div class="arvan-card">
                    <div class="cdn-report-head">
                        <div>
                            <span class="cdn-report-head__title"><?php esc_html_e( 'Status Codes - By Time', 'arvancloud-cdn' ); ?></span>
                            <p class="cdn-report-head__subTitle"><?php echo $time_period_t; ?></p>
                        </div>

                    </div>
                    <div class="report">
                        <canvas id="<?php echo $report->chart_ID;; ?>"></canvas>
                    </div>
                    <?php $report->print_chart_script(); ?>
                </div>
            </div>

        </div>

        

    </div>
    <br>

    <?php require_once(ACCDN_PLUGIN_ROOT . 'admin/views/components/footer.php'); ?>
</div>
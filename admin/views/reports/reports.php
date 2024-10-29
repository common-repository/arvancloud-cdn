<?php

use WP_Arvan\CDN\Reports;
use WP_Arvan\Helper;

$page_url = esc_url(admin_url('admin.php?page=' . ACCDN_SLUG . '-reports'));

$report_pages = [
    'traffics'  => __('Traffics Report', 'arvancloud-cdn'),
    'visitors'  => __('Users Report', 'arvancloud-cdn'),
    'requests'  => __('Requests', 'arvancloud-cdn'),
];

$time_periods = [
    '3h'    => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 3),
    '6h'    => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 6),
    '12h'   => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 12),
    '24h'   => sprintf( __('%s Hours Ago', 'arvancloud-cdn'), 24),
    '7d'    => sprintf( __('%s Week Ago', 'arvancloud-cdn'), 1),
    '30d'    => sprintf( __('%s Month Ago', 'arvancloud-cdn'), 1),
];

$tab            = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : strtolower(array_key_first($report_pages));
$time_period    = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '3h';
$time_period_t  = $time_periods[$time_period] ?? $time_period;
$tab_t          = $report_pages[$tab] ?? $tab;
$report         = new Reports( $tab, $time_period );

?>

<div class="wrap ar-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <?php require_once(ACCDN_PLUGIN_ROOT . 'admin/views/components/header.php') ?>

    <div class="cdn-report-header">

        <nav class="cdn-nav-tab-wrapper">
            <?php
            foreach ($report_pages as $slug => $page) {
                $class = $tab === $slug ? 'cdn-nav-tab-active' : '';
                echo '<a href="' . $page_url . '&tab=' . $slug . '" class="cdn-nav-tab ' . $class . '">' . $page . '</a>';
            }
            ?>
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
                    <span class="cdn-report-head__title"><?php echo $tab_t; ?></span>
                    <p class="cdn-report-head__subTitle"><?php echo $time_period_t; ?></p>
                </div>


                <div class="cdn-report-head__statistics">
                    <?php
                        foreach ($report->statistics_headings as $statistic) {
                            ?>
                            <div class="cdn-report-statistics-item">
                                <span class="cdn-report-item__title">
                                    <?php echo esc_html($statistic['value']) ?>
                                </span>
                                <p class="cdn-report-item__subTitle">
                                    <?php echo esc_html($statistic['label']) ?>
                                </p>
                            </div>
                            <?php
                        }
                    ?>
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

        <div class="arvan-reports-doughnuts">
            <div class="arvan-reports-doughnut">
                <?php
                $report         = new Reports( 'traffics/saved', $time_period, 'doughnut' ); 
                ?>
                <div class="arvan-card">
                    <div class="cdn-report-head">
                        <div>
                            <span class="cdn-report-head__title"><?php esc_html_e( 'Saved Traffic', 'arvancloud-cdn' ); ?></span>
                            <p class="cdn-report-head__subTitle"><?php echo $time_period_t; ?></p>
                        </div>
                        <div class="cdn-report-head__statistics">
                        <?php
                            foreach ($report->statistics_headings as $statistic) {
                                ?>
                                <div class="cdn-report-statistics-item">
                                    <span class="cdn-report-item__title">
                                        <?php echo esc_html($statistic['value']) ?>
                                    </span>
                                    <p class="cdn-report-item__subTitle">
                                        <?php echo esc_html($statistic['label']) ?>
                                    </p>
                                </div>
                            <?php
                            }
                            ?>
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
                                    <div class="ar-report-label-right"><?php echo Helper::size_convertor( $label['value'] ) ?></div>
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

            <div class="arvan-reports-doughnut">
                <?php
                $report         = new Reports( 'traffics/request', $time_period, 'doughnut' ); 
                ?>
                <div class="arvan-card">
                    <div class="cdn-report-head">
                        <div>
                            <span class="cdn-report-head__title"><?php esc_html_e( 'Saved Request', 'arvancloud-cdn' ); ?></span>
                            <p class="cdn-report-head__subTitle"><?php echo $time_period_t; ?></p>
                        </div>
                        <div class="cdn-report-head__statistics">
                            <?php
                            foreach ($report->statistics_headings as $statistic) {
                                ?>
                                <div class="cdn-report-statistics-item">
                                    <span class="cdn-report-item__title">
                                        <?php echo esc_html($statistic['value']) ?>
                                    </span>
                                    <p class="cdn-report-item__subTitle">
                                        <?php echo esc_html($statistic['label']) ?>
                                    </p>
                                </div>
                            <?php
                            }
                            ?>
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
                                    <div class="ar-report-label-right"><?php echo Helper::size_convertor( $label['value'] ) ?></div>
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

        </div>

        

    </div>
    <br>

    <?php require_once(ACCDN_PLUGIN_ROOT . 'admin/views/components/footer.php'); ?>
</div>
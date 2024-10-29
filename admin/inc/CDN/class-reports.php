<?php

namespace WP_Arvan\CDN;

use WP_Arvan\Helper;
use WP_Arvan\API\HTTP\Request_Arvan;

class Reports
{

    /**
     * Chart labels
     * @var array|false|false[]|string[] $labels
     */
    public $labels = [];
    /**
     * Chart datasets
     * @var array|false|false[]|string[] $datasets
     */
    public $datasets = [];
    /**
     * a list of statistics for showing on top of charts
     * @var array|false|false[]|string[] $statistics_headings
     */
    public $statistics_headings = [];
    /**
     * a list of labels with color and value only if $chart_type == doughnut
     * @var array|false|false[]|string[] $table_of_labels
     */
    public $table_of_labels = [];

    protected static $_instance = null;

    /**
     * type of chart - doughnut or line
     * @var mixed|string
     */
    public $chart_type;
    /**
     * Type of Report data
     * @var array|string|string[]
     */
    public $report_type;
    /**
     * All the Api response
     * @var mixed
     */
    public $report_data;

    private $colors = [
        'bypass' => '#F7D209',
        'saved' => '#00baba',
        'total' => '#ff9300',
        'Hit' => '#00baba',
        'Bypass' => '#ff9300',
        'Miss' => '#F7D209',
        502 => 'rgb(239, 90, 239)',
        403 => 'rgb(123, 101, 245)',
        404 => 'rgb(123, 101, 245)',
        405 => 'rgb(239, 90, 239)',
        500 => 'rgb(101, 190, 245)',
        301 => 'rgb(255, 147, 0)',
        302 => 'rgb(247, 210, 9)',
        200 => 'rgb(0, 186, 186)',
        '5xx' => '#7B65F5',
        '4xx' => '#F7D209',
        '3xx' => '#00baba',
        '2xx' => '#ff9300',
    ];
    
    private $fill_colors = [
        'bypass' => 'rgba(247,210,9,0.2)',
        'saved' => 'rgba(0,186,186,0.2)',
        'total' => 'rgba(255,147,0,0.2)',
        '5xx' => 'rgba(123,101,245,0.2)',
        '4xx' => 'rgba(247,210,9,0.2)',
        '3xx' => 'rgba(247,210,9,0.2)',
        '2xx' => 'rgba(255,147,0,0.2)',
        502 => 'rgb(239, 90, 239)',
        404 => 'rgb(123, 101, 245)',
        403 => 'rgb(247, 210, 9)',
        301 => 'rgb(255, 147, 0)',
        200 => 'rgb(0, 186, 186)',
    ];

    /**
     * Time period
     * @var mixed|string
     */
    private $period;

    /**
     * is chart data needs bytes conversion
     * @var bool
     */
    public $should_convert_byte;

    /**
     * Unique chart ID
     * @var string
     */
    public $chart_ID;

    /**
     * Get report data by calling API
     * @param $endpoint
     * @param $period
     * @return mixed
     */
    private function get_report($endpoint, $period = '3h')
    {
        $domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain/reports/$endpoint?period=$period&group=none");
    }

    public function __construct($report_data, $period = '3h', $chart_type = 'line')
    {
        $this::$_instance = $this;
        $this->report_type = str_replace('/', '_', $report_data);
        $this->report_type = str_replace('-', '_', $this->report_type);
        $this->chart_type = $chart_type;
        $this->should_convert_byte = ($this->report_type == 'traffics' );
        $this->chart_ID = 'chart_' . $this->report_type. '_' . $this->chart_type;
        $endpoint = ($report_data == 'requests') ? 'traffics' : $report_data;

        $endpoint = ($endpoint == 'traffics/request') ? 'traffics/saved' : $endpoint;


        $report = $this->get_report($endpoint, $period);
        if(!$report)
            return;
        $this->period = $period;

        $this->set_statistics( $report['statistics'], $endpoint, $report_data);
        $this->report_data = $this->set_report_data($endpoint, $report['charts']);

        // if (!$this->report_data || !isset($this->report_data['categories']) || count($this->report_data['categories']) < 1) {
        //     return false;
        // }

        $this->labels = $this->format_chart_label();
        $this->datasets = $this->format_chart_datasets();

        self::$_instance = $this;

    }

    /**
     * Creating a list of statistics for showing on top of charts
     *
     * @param $report
     * @param $endpoint
     * @param $report_data
     * @return void
     * @throws \Exception
     */
    private function set_statistics( $report, $endpoint, $report_data ) {

        if ( isset($report['visitors']) ) {
            $this->statistics_headings[] = array(
                'label' => __('Maximum Visitors', 'arvancloud-cdn'),
                'value' => date_format((new \DateTime($report["visitors"]['top_visitors']))->setTimeZone(wp_timezone()), "Y/m/d - H:i") ?? false
            );
            $this->statistics_headings[] = array(
                'label' => __('Number Of Visitors', 'arvancloud-cdn'),
                'value' => $report["visitors"]['total_visitors'] ?? false
            );

        } else if ( isset($report['requests']) && $report_data != 'traffics' ) {
            $this->statistics_headings[] = array(
                'label' => __('Maximum Requests', 'arvancloud-cdn'),
                'value' => date_format((new \DateTime($report["requests"]['top']))->setTimeZone(wp_timezone()), "Y/m/d - H:i") ?? false
            );
            $this->statistics_headings[] = array(
                'label' => __('Saved', 'arvancloud-cdn'),
                'value' => $report["requests"]['saved'] ?? false
            );
            $this->statistics_headings[] = array(
                'label' => __('All Requests', 'arvancloud-cdn'),
                'value' => $report["requests"]['total'] ?? false
            );
        } else if ( isset($report['traffics']) ) {
            $this->statistics_headings[] = array(
                'label' => __('Maximum Traffics', 'arvancloud-cdn'),
                'value' => date_format((new \DateTime($report["traffics"]['top']))->setTimeZone(wp_timezone()), "Y/m/d - H:i") ?? false
            );
            $this->statistics_headings[] = array(
                'label' => __('Saved', 'arvancloud-cdn'),
                'value' => Helper::size_convertor($report["traffics"]['saved']) ?? false
            );
            $this->statistics_headings[] = array(
                'label' => __('All Traffics', 'arvancloud-cdn'),
                'value' => Helper::size_convertor($report["traffics"]['total']) ?? false
            );
        } else if ( $this->report_type == 'traffics_request' && isset($report['traffic']) ) {
            $this->statistics_headings[] = array(
                'label' => __('Saved Request', 'arvancloud-cdn'),
                'value' => $report["request"]['saved'] ?? false
            );
        } else if ( $this->report_type == 'traffics_saved' && isset($report['traffic']) ) {
            $this->statistics_headings[] = array(
                'label' => __('Saved Traffic', 'arvancloud-cdn'),
                'value' => Helper::size_convertor($report["traffic"]['saved']) ?? false
            );
        } else {
            return;
        }

        // if (Helper::is_site_persian()) {
        //     $this->most_traffic = Helper::digits_enToFa($this->most_traffic);
        //     $this->saved_size = Helper::digits_enToFa($this->saved_size);
        //     $this->total_traffic_size = Helper::digits_enToFa($this->total_traffic_size);
        // }

    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self('traffics');
        }
        return self::$_instance;
    }

    private function format_chart_label( ) {

        if (!isset($this->report_data['categories']) && $this->chart_type != 'doughnut') {
            return false;
        }

        if ( $this->chart_type == 'doughnut' ) {

            if ($this->report_type == 'status_summary' || $this->report_type == 'status') {
                return array_map(
                    function ($data) {
                        return $data['name'];
                    }, $this->report_data
                );
            }

            return array_map(
                function ($data) {
                    return __(ucfirst(substr($data['name'], strrpos($data['name'], '.') + 1)), 'arvancloud-cdn');
                }, $this->report_data
            );
        }

        if (substr($this->period, -1) != 'h') {
            return array_map(
                function ($date) {
                    return date_format((new \DateTime($date))->setTimeZone(wp_timezone()), "Y/m/d");
                }, $this->report_data['categories']
            );
        }

        return array_map(
            function ($date) {
                return date_format((new \DateTime($date))->setTimeZone(wp_timezone()), "H:i");
            }, $this->report_data['categories']
        );
    }

    private function format_chart_datasets() {

        if (!isset($this->report_data['series']) && $this->chart_type != 'doughnut') {
            return false;
        }
        $datasets = [];

        if ( $this->chart_type == 'doughnut' ) {
            $data = array_map(
                function ($data) {
                    return $data['y'];
                }, $this->report_data
            );

            $fill_color = array_map(
                function ($name) {
                    return $this->colors[$name] ?? 'rgba(255,147,0,0.2)';
                }, $this->labels
            );


            $datasets = [[
                'label'           => $this->chart_ID,
                'data'            => $data,
                'fill'            => true,
                'backgroundColor' => $fill_color,
                'borderWidth'     => 1,
                'hoverOffset'     => 20,
            ]];
            $c = 0;
            foreach($this->report_data as $data) {
                $this->table_of_labels[] = [
                    'color'     => $fill_color[$c],
                    'label'     => str_replace( '.', '', __(ucfirst(substr($data['name'], strrpos($data['name'], '.'))), 'arvancloud-cdn')),
                    'value'     => $this->should_convert_byte ? Helper::size_convertor($data['y']) : $data['y'],
                ];
                $c++;
            }
        } else {
            foreach( $this->report_data['series'] as $data ) {
    
                $name = substr($data['name'], strrpos($data['name'], '.') + 1);
                $color = $this->colors[$name] ?? '#ff9300';
                $fill_color = $this->fill_colors[$name] ?? 'rgba(255,147,0,0.2)';
                $name = __(ucfirst($name), 'arvancloud-cdn');
    
                $datasets[] = [
                    'label' => $name,
                    'fill' => true,
                    'backgroundColor' => $fill_color,
                    'pointBackgroundColor' => $color,
                    'borderColor' => $color,
                    'borderWidth' => 1,
                    'data' => $data['data'],
                ];
            }
        }


        return $datasets;
    }

    private function set_report_data($endpoint, $charts)
    {
        if ($endpoint == 'traffics' && $this->report_type != 'requests') {
            return $charts['requests'];
        } else if ($endpoint == 'traffics') {
            return $charts['traffics'];
        } else if ($endpoint == 'visitors') {
            return $charts['visitors'];
        } else if ($endpoint == 'traffics/saved' && $this->report_type == 'traffics_saved') {
            return $charts['traffic'];
        } else if ($endpoint == 'traffics/saved' && $this->report_type == 'traffics_request') {
            return $charts['request'];
        } else if ($endpoint == 'response-time') {
            return $charts['ir'];
        } else if ($endpoint == 'status' || $endpoint == 'status/summary') {
            return $charts['status_code'];
        }

        return $charts[0];
    }

    /**
     * Chart config for ChartJS
     *
     * @return array
     */
    private function get_chart_config() {
        $data = [
            'labels' => $this->labels,
            'datasets' => $this->datasets,
        ];
        $scales_y = ( !$this->should_convert_byte && $this->chart_type != 'doughnut' ) ? ['beginAtZero' => true] : [];
        $scales_x = ( $this->chart_type != 'doughnut' ) ? ['grid' => ['display' => false]] : [];
        $legend   = ( $this->chart_type != 'doughnut' ) ? [
            'position' => 'bottom',
        ] : ['display' => false];
        $plugins  = ['legend' => $legend];

        $interaction = ( $this->chart_type != 'doughnut' ) ? [
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ]
        ] : [];

        $scales = [
            'y' => array_filter($scales_y),
            'x' => array_filter($scales_x)
        ];

        $padding = ( $this->chart_type != 'doughnut' ) ? 10 : 40;

        $config = [
            'type' => $this->chart_type,
            'data' => $data,
            'options' => [
                'layout' => [
                    'padding' => $padding
                ],
                'plugins' => $plugins,
                'scales' => array_filter($scales),
                array_filter($interaction),
            ]
        ];
        
        return array_filter($config);
    }


    /**
     * print ChartJS
     * @return void
     */
    public function print_chart_script( ) {
        ?>
        <script>
            var config = <?php echo json_encode($this->get_chart_config()); ?>

            <?php
            if ($this->should_convert_byte) {
                echo 'config.options.scales.y = {ticks: {
                    callback: function(value, index, ticks) {
                        return formatBytes(value);
                    }
                }}' ;
            }
            ?>
            
            const <?php echo $this->chart_ID; ?> = new Chart(
                document.getElementById('<?php echo $this->chart_ID; ?>'),
                config
            );
        </script>
        <?php
    }
}

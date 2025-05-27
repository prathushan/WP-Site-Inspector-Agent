<?php
if (!defined('ABSPATH')) exit;

// Get initial chart data
$analyzer = new WP_Site_Inspector_Analyzer();
$plugins_data = $analyzer->analyze_tab('plugins');
$pages_data = $analyzer->analyze_tab('pages');
$posts_data = $analyzer->analyze_tab('posts');
$templates_data = $analyzer->analyze_tab('templates');
$shortcodes_data = $analyzer->analyze_tab('shortcodes');
$apis_data = $analyzer->analyze_tab('apis');
$post_types_data = $analyzer->analyze_tab('post-types');

// Calculate chart data
$active_plugins = count(array_filter($plugins_data, fn($p) => $p['status'] === 'Active'));
$inactive_plugins = count($plugins_data) - $active_plugins;

$published_pages = count(array_filter($pages_data, fn($p) => $p['status'] === 'Publish'));
$draft_pages = count($pages_data) - $published_pages;

wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.7.0', true);
?>
<div class="wrap">
    <h1><?php _e('WP Site Inspector', 'wp-site-inspector'); ?></h1>

    <!-- Loading Indicator -->
    <div id="wpsi-loading" class="wpsi-loading" style="display: none;">
        <div class="spinner is-active"></div>
        <p><?php _e('Loading data...', 'wp-site-inspector'); ?></p>
    </div>

    <!-- Charts Section - Always visible -->
    <div class="wpsi-dashboard-grid">
        <div class="wpsi-chart-card">
            <h3><?php _e('Plugins Overview', 'wp-site-inspector'); ?></h3>
            <canvas id="pluginPieChart"></canvas>
        </div>
        <div class="wpsi-chart-card">
            <h3><?php _e('Pages Overview', 'wp-site-inspector'); ?></h3>
            <canvas id="pagePieChart"></canvas>
        </div>
        <div class="wpsi-chart-card">
            <h3><?php _e('Total Overview', 'wp-site-inspector'); ?></h3>
            <canvas id="combinedBarChart"></canvas>
        </div>
    </div>

    <!-- Tabs Container -->
    <div class="tab-container">
        <div class="tab-buttons">
            <button class="tab-button active" data-tab="theme"><?php _e('Theme Info', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="builders"><?php _e('Builders', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="plugins"><?php _e('Plugins', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="pages"><?php _e('Pages', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="posts"><?php _e('Posts', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="post-types"><?php _e('Post Types', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="templates"><?php _e('Templates', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="shortcodes"><?php _e('Shortcodes', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="hooks"><?php _e('Hooks', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="apis"><?php _e('REST APIs', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="cdn"><?php _e('CDN Links', 'wp-site-inspector'); ?></button>
            <button class="tab-button" data-tab="logs"><?php _e('Logs', 'wp-site-inspector'); ?></button>
        </div>

        <!-- Tab Content Container -->
        <div id="tab-content-container">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
</div>

<style>
.wpsi-loading {
    text-align: center;
    padding: 20px;
}
.wpsi-loading .spinner {
    float: none;
    margin: 0 auto;
}
.wpsi-dashboard-grid {
    /* position: sticky;
    top: 32px; 
    z-index: 100; */
    background: #f0f0f1;
    padding: 20px 0;
    margin: -20px 0 20px 0;
}
.wpsi-chart-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.wpsi-chart-card h3 {
    margin-top: 0;
    margin-bottom: 15px;
}
canvas {
    width: 100% !important;
    height: 300px !important;
}
.wpsi-pagination {
    margin-top: 20px;
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
}
.wpsi-pagination button {
    padding: 5px 15px;
}
.wpsi-pagination .page-info {
    font-size: 14px;
}
.wpsi-pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentTab = 'theme';
    let loadedTabs = {};
    let charts = {};
    
    // Initialize charts immediately with PHP data
    function initializeChartsOnLoad() {
        // Plugin Pie Chart
        const pluginCtx = document.getElementById('pluginPieChart').getContext('2d');
        charts.plugins = new Chart(pluginCtx, {
            type: 'pie',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [<?php echo $active_plugins; ?>, <?php echo $inactive_plugins; ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Pages Pie Chart
        const pagesCtx = document.getElementById('pagePieChart').getContext('2d');
        charts.pages = new Chart(pagesCtx, {
            type: 'pie',
            data: {
                labels: ['Published', 'Draft'],
                datasets: [{
                    data: [<?php echo $published_pages; ?>, <?php echo $draft_pages; ?>],
                    backgroundColor: ['#3498db', '#95a5a6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Combined Bar Chart
        const overviewCtx = document.getElementById('combinedBarChart').getContext('2d');
        charts.overview = new Chart(overviewCtx, {
            type: 'bar',
            data: {
                labels: ['Posts', 'Plugins', 'Pages', 'Post Types', 'Templates', 'Shortcodes', 'REST APIs'],
                datasets: [{
                    label: 'Total Items',
                    data: [
                        <?php echo count($posts_data); ?>,
                        <?php echo count($plugins_data); ?>,
                        <?php echo count($pages_data); ?>,
                        <?php echo count($post_types_data); ?>,
                        <?php echo count($templates_data); ?>,
                        <?php echo count($shortcodes_data); ?>,
                        <?php echo count($apis_data); ?>
                    ],
                    backgroundColor: '#0073aa'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Initialize charts on page load
    initializeChartsOnLoad();

    // Function to show loading indicator
    function showLoading() {
        $('#wpsi-loading').show();
    }
    
    // Function to hide loading indicator
    function hideLoading() {
        $('#wpsi-loading').hide();
    }
    
    // Function to load page content
    function loadPageContent(tab, page) {
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsi_load_page',
                tab: tab,
                page: page,
                nonce: '<?php echo wp_create_nonce('wpsi_ajax_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $(`#${tab}-tbody`).html(response.data.html);
                    updatePaginationState(tab, page, response.data.total_pages);
                } else {
                    alert('Error loading page content');
                }
            },
            error: function() {
                alert('Error communicating with server');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    // Function to update pagination state
    function updatePaginationState(tab, currentPage, totalPages) {
        const $pagination = $(`.wpsi-pagination[data-tab="${tab}"]`);
        const $prevBtn = $pagination.find('.prev-page');
        const $nextBtn = $pagination.find('.next-page');
        const $currentPageSpan = $pagination.find('.current-page');
        
        $currentPageSpan.text(currentPage);
        $prevBtn.prop('disabled', currentPage <= 1);
        $nextBtn.prop('disabled', currentPage >= totalPages);
        
        $pagination.data('current-page', currentPage);
    }
    
    // Function to load tab content
    function loadTabContent(tabId) {
        if (loadedTabs[tabId]) {
            // If data is already loaded, just show it
            $('.tab-content').hide();
            $(`#${tabId}`).show();
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpsi_load_tab_content',
                tab: tabId,
                nonce: '<?php echo wp_create_nonce('wpsi_ajax_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Add content to container
                    $('#tab-content-container').append(response.data.html);
                    loadedTabs[tabId] = true;
                    
                    // Hide other tabs
                    $('.tab-content').hide();
                    $(`#${tabId}`).show();
                    
                    // Initialize charts if chart data is available
                    if (response.data.chartData) {
                        initializeCharts(response.data.chartData);
                    }
                    
                    // Initialize pagination handlers
                    initializePaginationHandlers(tabId);
                } else {
                    alert('Error loading content');
                }
            },
            error: function() {
                alert('Error communicating with server');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    // Function to initialize pagination handlers
    function initializePaginationHandlers(tabId) {
        const $pagination = $(`.wpsi-pagination[data-tab="${tabId}"]`);
        
        $pagination.on('click', '.prev-page', function() {
            const currentPage = parseInt($pagination.data('current-page'));
            if (currentPage > 1) {
                loadPageContent(tabId, currentPage - 1);
            }
        });
        
        $pagination.on('click', '.next-page', function() {
            const currentPage = parseInt($pagination.data('current-page'));
            const totalPages = parseInt($pagination.data('total-pages'));
            if (currentPage < totalPages) {
                loadPageContent(tabId, currentPage + 1);
            }
        });
    }
    
    // Tab click handler
    $('.tab-button').on('click', function() {
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        currentTab = $(this).data('tab');
        loadTabContent(currentTab);
    });
    
    // Load initial tab
    loadTabContent(currentTab);
});
</script>

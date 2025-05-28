<?php
if (!defined('ABSPATH')) exit;

// Include the analyzer class
require_once plugin_dir_path(dirname(__FILE__)) . 'class-analyzer.php';

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
    
    // Use localized AJAX URL
    const ajaxurl = wpsiAjax.ajaxurl;
    const nonce = wpsiAjax.nonce;
    
    // Function to handle AJAX errors
    function handleAjaxError(jqXHR, textStatus, errorThrown) {
        console.error('AJAX error details:', {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            responseText: jqXHR.responseText,
            textStatus: textStatus,
            errorThrown: errorThrown
        });

        let errorMessage = 'Server communication error.';
        if (jqXHR.status === 404) {
            errorMessage = 'AJAX endpoint not found. Please check if your WordPress installation is working correctly.';
        } else if (jqXHR.status === 502) {
            errorMessage = 'Server is temporarily unavailable. Please try again in a few moments.';
        } else if (jqXHR.status === 403) {
            errorMessage = 'Access denied. Please refresh the page and try again.';
        }

        alert(errorMessage + ' Check browser console for details.');
        hideLoading();
    }
    
    // Function to load page content
    function loadPageContent(tab, page) {
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpsi_load_page',
                tab: tab,
                page: page,
                nonce: nonce
            },
            success: function(response) {
                try {
                    if (response && response.success && response.data) {
                        $(`#${tab}-tbody`).html(response.data.html);
                        updatePaginationState(tab, page, response.data.total_pages);
                    } else {
                        console.error('Invalid response format:', response);
                        alert('Error loading page content: Invalid response format');
                    }
                } catch (error) {
                    console.error('Error processing response:', error);
                    alert('Error processing server response');
                }
            },
            error: handleAjaxError,
            complete: function() {
                hideLoading();
            }
        });
    }
    
    // Function to load tab content
    function loadTabContent(tabId) {
        if (loadedTabs[tabId]) {
            $('.tab-content').hide();
            $(`#${tabId}`).show();
            hideLoading();
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpsi_load_tab_content',
                tab: tabId,
                nonce: nonce
            },
            success: function(response) {
                try {
                    if (response && response.success && response.data) {
                        $('#tab-content-container').append(response.data.html);
                        loadedTabs[tabId] = true;
                        
                        $('.tab-content').hide();
                        $(`#${tabId}`).show();
                        
                        if (response.data.chartData) {
                            initializeCharts(response.data.chartData);
                        }
                        
                        initializePaginationHandlers(tabId);
                    } else {
                        console.error('Invalid response format:', response);
                        alert('Error loading content: Invalid response format');
                    }
                } catch (error) {
                    console.error('Error processing response:', error);
                    alert('Error processing server response');
                }
            },
            error: handleAjaxError,
            complete: function() {
                hideLoading();
            }
        });
    }
    
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

    // Function to initialize/update charts with new data
    function initializeCharts(chartData) {
        if (chartData.plugins) {
            if (charts.plugins) {
                charts.plugins.data.datasets[0].data = chartData.plugins.active_inactive;
                charts.plugins.update();
            }
        }
        
        if (chartData.pages) {
            if (charts.pages) {
                charts.pages.data.datasets[0].data = chartData.pages.published_draft;
                charts.pages.update();
            }
        }
        
        if (chartData.overview) {
            if (charts.overview) {
                charts.overview.data.labels = chartData.overview.labels;
                charts.overview.data.datasets[0].data = chartData.overview.data;
                charts.overview.update();
            }
        }
    }

    // Function to show loading indicator
    function showLoading() {
        $('#wpsi-loading').show();
    }
    
    // Function to hide loading indicator
    function hideLoading() {
        $('#wpsi-loading').hide();
    }
    
    // Function to update pagination state
    function updatePaginationState(tab, currentPage, totalPages) {
        const $pagination = $(`.wpsi-pagination[data-tab="${tab}"]`);
        const $prevBtn = $pagination.find('.prev-page');
        const $nextBtn = $pagination.find('.next-page');
        const $pageButtons = $pagination.find('.page-number');
        
        // Remove all existing page number buttons
        $pageButtons.not(':first').remove(); // Keep the first page
        $('.pagination-ellipsis').remove();
        
        // Function to add page button
        function addPageButton(pageNum, isActive = false) {
            const activeClass = isActive ? ' active' : '';
            return $(`<button class="pagination-btn page-number${activeClass}" data-page="${pageNum}">${pageNum}</button>`);
        }
        
        // Always show first page
        $pageButtons.first().toggleClass('active', currentPage === 1);
        
        if (totalPages > 7) {
            // Show first set of pages
            if (currentPage <= 4) {
                for (let i = 2; i <= 5; i++) {
                    addPageButton(i, i === currentPage).insertBefore($nextBtn);
                }
                $('<span class="pagination-ellipsis">...</span>').insertBefore($nextBtn);
                addPageButton(totalPages - 1).insertBefore($nextBtn);
                addPageButton(totalPages).insertBefore($nextBtn);
            }
            // Show last set of pages
            else if (currentPage >= totalPages - 3) {
                addPageButton(2).insertBefore($nextBtn);
                $('<span class="pagination-ellipsis">...</span>').insertBefore($nextBtn);
                for (let i = totalPages - 4; i <= totalPages; i++) {
                    addPageButton(i, i === currentPage).insertBefore($nextBtn);
                }
            }
            // Show middle pages
            else {
                addPageButton(2).insertBefore($nextBtn);
                $('<span class="pagination-ellipsis">...</span>').insertBefore($nextBtn);
                for (let i = currentPage - 1; i <= currentPage + 1; i++) {
                    addPageButton(i, i === currentPage).insertBefore($nextBtn);
                }
                $('<span class="pagination-ellipsis">...</span>').insertBefore($nextBtn);
                addPageButton(totalPages).insertBefore($nextBtn);
            }
        } else {
            // Show all pages if total pages is 7 or less
            for (let i = 2; i <= totalPages; i++) {
                addPageButton(i, i === currentPage).insertBefore($nextBtn);
            }
        }
        
        // Update prev/next buttons
        $prevBtn.prop('disabled', currentPage <= 1);
        $nextBtn.prop('disabled', currentPage >= totalPages);
        
        $pagination.data('current-page', currentPage);
    }
    
    // Function to initialize pagination handlers
    function initializePaginationHandlers(tabId) {
        const $pagination = $(`.wpsi-pagination[data-tab="${tabId}"]`);
        
        // Previous button handler
        $pagination.on('click', '.prev-page', function() {
            const currentPage = parseInt($pagination.data('current-page'));
            if (currentPage > 1) {
                loadPageContent(tabId, currentPage - 1);
            }
        });
        
        // Next button handler
        $pagination.on('click', '.next-page', function() {
            const currentPage = parseInt($pagination.data('current-page'));
            const totalPages = parseInt($pagination.data('total-pages'));
            if (currentPage < totalPages) {
                loadPageContent(tabId, currentPage + 1);
            }
        });
        
        // Page number buttons handler
        $pagination.on('click', '.page-number', function() {
            const pageNum = parseInt($(this).data('page'));
            const currentPage = parseInt($pagination.data('current-page'));
            
            if (pageNum !== currentPage) {
                loadPageContent(tabId, pageNum);
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
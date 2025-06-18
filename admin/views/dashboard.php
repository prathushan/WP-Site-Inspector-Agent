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
$active_plugins = count(array_filter($plugins_data, fn($p) => $p['status'] === 'active'));
$inactive_plugins = count($plugins_data) - $active_plugins;

// Ensure pages data is properly calculated
$pages_data = $analyzer->analyze_tab('pages');
$published_pages = count(array_filter($pages_data, function($p) {
    return strtolower($p['status']) === 'publish';
}));
$draft_pages = count(array_filter($pages_data, function($p) {
    return strtolower($p['status']) === 'draft';
}));

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

<!-- AI Chat Box -->
<div id="wpsi-ai-chatbox" style="display:none; position:fixed; bottom:20px; right:20px; width:700px;
    height:600px; background:#fff; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.2); 
    z-index:10000;flex-direction:column; font-family:sans-serif;">

    <div style="background:#000; color:#fff; padding:12px 16px; font-weight:bold; position:relative;">
        AI Code Assistant
        <button id="wpsi-chat-close" style="position:absolute; right:10px; top:8px; background:none; border:none; color:#fff; font-size:18px; cursor:pointer;">×</button>
    </div>

    <div id="wpsi-chat-messages" style="flex:1; padding:15px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; background:#f7f7f7;">
        <!-- Messages will be added here -->
    </div>

    <div style="padding:10px; border-top:1px solid #ddd; display:flex;">
        <input type="text" id="wpsi-user-input" placeholder="Ask something..." style="flex:1; padding:8px 10px; border-radius:6px; border:1px solid #ccc; font-size:14px;">
        <button id="wpsi-send-btn" style="margin-left:8px; padding:8px 12px; background:#4b6cb7; color:#fff; border:none; border-radius:6px; cursor:pointer;">Send</button>
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

/* AI Chat Styles */
#wpsi-ai-chatbox {
    display: none;
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 700px;
    height: 600px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    z-index: 10000;
    flex-direction: column;
    font-family: sans-serif;
}

#wpsi-chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f7f7f7;
}

.wpsi-ai-thinking {
    align-self: flex-start;
    background: #f0f0f0;
    padding: 10px 14px;
    border-radius: 12px;
    max-width: 90%;
    display: flex;
    align-items: center;
    gap: 10px;
}

.wpsi-spinner {
    width: 16px;
    height: 16px;
    border: 3px solid #ccc;
    border-top: 3px solid #4b6cb7;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Global utility functions
function showLoading() {
    jQuery('#wpsi-loading').show();
}

function hideLoading() {
    jQuery('#wpsi-loading').hide();
}

// Define exportToExcel function globally
function exportToExcel() {
    const $ = jQuery;
    showLoading();
    
    $.ajax({
        url: wpsiAjax.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'wpsi_export_excel',
            nonce: wpsiAjax.nonce
        },
        success: function(response) {
            if (response.success && response.data) {
                // Create workbook
                const wb = XLSX.utils.book_new();
                
                // Add each tab's data as a worksheet
                Object.keys(response.data).forEach(tabName => {
                    const data = response.data[tabName];
                    if (data && data.length > 0) {
                        const ws = XLSX.utils.json_to_sheet(data);
                        XLSX.utils.book_append_sheet(wb, ws, tabName);
                    }
                });
                
                // Generate and download the file
                const fileName = 'wp-site-inspector-export-' + new Date().toISOString().slice(0,10) + '.xlsx';
                XLSX.writeFile(wb, fileName);
            } else {
                alert('Error exporting data: ' + (response.data?.error || 'Unknown error'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error details:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                textStatus: textStatus,
                errorThrown: errorThrown
            });
            alert('Error exporting data. Please check console for details.');
        },
        complete: function() {
            hideLoading();
        }
    });
}

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
                labels: [
                    '<?php echo esc_js(__('Active', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Inactive', 'wp-site-inspector')); ?>'
                ],
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
                labels: [
                    '<?php echo esc_js(__('Published', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Draft', 'wp-site-inspector')); ?>'
                ],
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
                labels: [
                    '<?php echo esc_js(__('Posts', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Plugins', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Pages', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Post Types', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Templates', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('Shortcodes', 'wp-site-inspector')); ?>',
                    '<?php echo esc_js(__('REST APIs', 'wp-site-inspector')); ?>'
                ],
                datasets: [{
                    label: '<?php echo esc_js(__('Total Items', 'wp-site-inspector')); ?>',
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
                charts.plugins.data.labels = chartData.plugins.labels;
                charts.plugins.data.datasets[0].data = chartData.plugins.active_inactive;
                charts.plugins.update();
            }
        }
        
        if (chartData.pages) {
            if (charts.pages) {
                // Ensure we're using the correct data structure
                if (Array.isArray(chartData.pages.published_draft) && chartData.pages.published_draft.length === 2) {
                    charts.pages.data.labels = chartData.pages.labels;
                    charts.pages.data.datasets[0].data = chartData.pages.published_draft;
                    charts.pages.update();
                }
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

    // Add export button to the page
    $('.wrap').append(`
        <div class="export-buttons">
            <button id="wpsi-export-excel" class="button button-primary">
                <span class="dashicons dashicons-download"></span> Export All Data to Excel
            </button>
        </div>
    `);

    // Bind click event to export button
    $(document).on('click', '#wpsi-export-excel', function(e) {
        e.preventDefault();
        exportToExcel();
    });

    // Add AI Chat functionality
    function appendMessage(who, message, bgColor, align = 'flex-start') {
        const msgHtml = `
            <div style="align-self: ${align}; background:${bgColor}; padding:10px 14px; border-radius:12px; max-width: 90%; word-wrap: break-word;">
                <strong>${who}:</strong><br>${message}
            </div>
        `;
        $('#wpsi-chat-messages').append(msgHtml);
        $('#wpsi-chat-messages').scrollTop($('#wpsi-chat-messages')[0].scrollHeight);
    }

    function sendMessageToAI(message, $chat) {
        appendMessage('You', message, '#d0ebff', 'flex-end');

        // Thinking placeholder
        const thinkingDiv = $(`
            <div class="wpsi-ai-thinking">
                <div class="wpsi-spinner"></div>
                <span>analyzing error</span>
            </div>
        `);
        $chat.append(thinkingDiv);
        $chat.scrollTop($chat[0].scrollHeight);

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wpsi_ask_ai',
                message: message,
                nonce: nonce
            },
            success: function(response) {
                thinkingDiv.remove();
                if (response.success && response.data) {
                    const aiText = response.data.response ? response.data.response.replace(/\n/g, "<br>") : '';
                    appendMessage('AI', aiText, '#e7f5e6');
                } else {
                    appendMessage('Error', 'Failed to get AI response', '#ffdede');
                }
            },
            error: function(xhr, status, error) {
                thinkingDiv.remove();
                appendMessage('Error', 'Failed to communicate with server', '#ffdede');
            }
        });
    }

    // Open AI chat and send message if available
    $(document).on('click', '.ask-ai-button', function () {
        const message = $(this).data('message');
        const $chat = $('#wpsi-chat-messages');

        // Use flex to maintain column layout
        $('#wpsi-ai-chatbox').css('display', 'flex');
        if (message) sendMessageToAI(message, $chat);
    });

    // Manual input send
    $(document).on('click', '#wpsi-send-btn', function() {
        const input = $('#wpsi-user-input');
        const message = input.val().trim();
        if (!message) return;
        input.val('');
        sendMessageToAI(message, $('#wpsi-chat-messages'));
    });

    // Enter key triggers send
    $(document).on('keypress', '#wpsi-user-input', function(e) {
        if (e.which === 13) {
            $('#wpsi-send-btn').click();
        }
    });

    // Close button
    $(document).on('click', '#wpsi-chat-close', function() {
        $('#wpsi-ai-chatbox').fadeOut();
    });
});
</script>
<?php
if (!defined('ABSPATH')) exit;

$analyzer = new WP_Site_Inspector_Analyzer();
$data = $analyzer->analyze();

$plugins = $data['plugins'];
$pages = $data['pages'];
$posts = $data['posts'];

echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
?>
<h1>Wp Site Inspector</h1>

<!-- Charts Section -->
<div class="wpsi-dashboard-grid">
  <div class="wpsi-chart-card">
    <h3>Plugins Overview</h3>
    <canvas id="pluginPieChart"></canvas>
  </div>
  <div class="wpsi-chart-card">
    <h3>Pages Overview</h3>
    <canvas id="pagePieChart"></canvas>
  </div>
  <div class="wpsi-chart-card">
    <h3>Total Overview</h3>
    <canvas id="combinedBarChart" style="width:100%"></canvas>
  </div>
</div>

<!-- Tabs Container -->
<div class="tab-container">
  <div class="tab-buttons">
    <button class="tab-button active" data-tab="theme">Theme Info</button>
    <button class="tab-button" data-tab="builders">Builders</button>
    <button class="tab-button" data-tab="plugins">Plugins</button>
    <button class="tab-button" data-tab="pages">Pages</button>
    <button class="tab-button" data-tab="posts">Posts</button>
    <button class="tab-button" data-tab="post-types">Post Types</button>
    <button class="tab-button" data-tab="templates">Templates</button>
    <button class="tab-button" data-tab="shortcodes">Shortcodes</button>
    <button class="tab-button" data-tab="hooks">Hooks</button>
    <button class="tab-button" data-tab="apis">REST APIs</button>
    <button class="tab-button" data-tab="cdn">CDN Links</button>
    <button class="tab-button" data-tab="logs">Logs</button>
  </div>

  <?php
  function wpsi_render_tab_content($id, $title, $headers, $rows, $per_page = 10)
  {
    $total_rows = count($rows);
    $total_pages = ceil($total_rows / $per_page);
    $safe_id = htmlspecialchars($id, ENT_QUOTES);
    $safe_title = $title;

    echo "<div id='$safe_id' class='tab-content'>";
    echo "<h2>$safe_title</h2>";
    echo "<div class='wpsi-table-wrap'>";

    // Table start
    echo "<table data-title='$safe_title'><thead><tr>";
    echo "<th>S.No</th>"; // Serial number header
    foreach ($headers as $th) {
      echo "<th>" . htmlspecialchars($th, ENT_QUOTES) . "</th>";
    }
    echo "</tr></thead><tbody>";

    foreach ($rows as $index => $row) {
      $page_number = floor($index / $per_page);
      echo "<tr class='page-row page-{$safe_id}-{$page_number}'>";
      echo "<td>" . ($index + 1) . "</td>"; // Serial number
      foreach ($row as $i => $col) {
  $is_last_column = ($i === array_key_last($row));
  if ($is_last_column) {
    echo "<td>$col</td>"; // Allow raw HTML for Ask AI button
  } else {
    echo "<td>" . htmlspecialchars($col, ENT_QUOTES) . "</td>";
  }
}

      echo "</tr>";
    }

    echo "</tbody></table></div>";

    // Pagination controls
    if ($total_pages > 1) {
      echo "<div class='pagination' id='pagination-$safe_id'></div>";
    }

    echo "</div>"; // End tab-content

  ?>
    <!-- Include Font Awesome CDN if not already included -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const totalPages = <?= $total_pages ?>;
        const container = document.getElementById('pagination-<?= $safe_id ?>');
        if (!container) return; // Safety check
        const tabId = '<?= $safe_id ?>';

        function showPage(page) {
          if (page < 0) page = 0;
          if (page >= totalPages) page = totalPages - 1;

          const rows = document.querySelectorAll(`#${tabId} .page-row`);
          rows.forEach(row => {
            row.style.display = row.classList.contains(`page-${tabId}-${page}`) ? '' : 'none';
          });
          renderPagination(page);
        }

        function createButton(labelHtml, page, disabled = false, active = false) {
          const btn = document.createElement('button');
          btn.className = 'pagination-btn';
          btn.innerHTML = labelHtml;
          if (disabled) btn.disabled = true;
          if (active) btn.classList.add('active');
          btn.dataset.page = page;
          btn.addEventListener('click', () => {
            showPage(page);
          });
          return btn;
        }

        function renderPagination(currentPage) {
          container.innerHTML = '';

          // Prev button with Font Awesome icon
          container.appendChild(createButton('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 0));

          // Ellipsis creator helper
          const createEllipsis = () => {
            const span = document.createElement('span');
            span.textContent = '...';
            span.className = 'ellipsis';
            return span;
          };

          if (totalPages <= 7) {
            for (let i = 0; i < totalPages; i++) {
              container.appendChild(createButton((i + 1).toString(), i, false, i === currentPage));
            }
          } else {
            container.appendChild(createButton('1', 0, false, currentPage === 0));

            if (currentPage > 3) {
              container.appendChild(createEllipsis());
            }

            let start = Math.max(1, currentPage - 1);
            let end = Math.min(totalPages - 2, currentPage + 1);

            for (let i = start; i <= end; i++) {
              container.appendChild(createButton((i + 1).toString(), i, false, i === currentPage));
            }

            if (currentPage < totalPages - 4) {
              container.appendChild(createEllipsis());
            }

            container.appendChild(createButton(totalPages.toString(), totalPages - 1, false, currentPage === totalPages - 1));
          }

          // Next button with Font Awesome icon
          container.appendChild(createButton('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages - 1));
        }

        showPage(0);
      });
    </script>
  <?php
  }

  // Theme Info
  $theme = $data['theme'];
  $theme_name_safe = esc_js($theme['name']);
  wpsi_render_tab_content('theme', 'Theme Info', ['Property', 'Value'], [
    ['Active Theme', esc_html($theme['name']) . ' v' . esc_html($theme['version'])],
    ['Theme Type', esc_html($theme['type'])]
  ]);


  // Builders
  $builder_rows = [];
  foreach ($data['builders'] as $b) {
    $builder_rows[] = [esc_html($b['name']), esc_html($b['status'])];
  }
  if (!$builder_rows) {
    $builder_rows[] = ['None Detected', '-'];
  }
  wpsi_render_tab_content('builders', 'Theme Builders', ['Builder', 'Status'], $builder_rows);

  $plugin_rows = [];
  foreach ($data['plugins'] as $plugin) {
    $plugin_rows[] = [
      esc_html($plugin['name']),
      esc_html($plugin['status']),
      esc_html($plugin['update']),
      esc_html($plugin['last_update']),
      esc_html($plugin['installed_on'])
    ];
  }

  wpsi_render_tab_content(
    'plugins',
    'Plugins',
    ['Plugin Name', 'Status', 'Update Status', 'Last Updated', 'installed_on'],
    $plugin_rows
  );


  // Pages
  $page_rows = [];
  foreach ($pages as $page) {
    $page_rows[] = [
      esc_html($page['title']),
      esc_html($page['status']),
      esc_html($page['date']) // Add this line for publish date
    ];
  }

  // Add "Published At" column
  wpsi_render_tab_content('pages', 'Pages', ['Title', 'Status', 'Published At'], $page_rows);


  // Posts
  $post_rows = [];
  foreach ($posts as $post) {
    $post_rows[] = [
      esc_html($post['title']),
      esc_html($post['status']),
      esc_html($post['date'])
    ];
  }

  wpsi_render_tab_content('posts', 'Posts', ['Title', 'Status', 'Published At'], $post_rows);


  // Post Types
  $post_type_rows = [];
  foreach ($data['post_types'] as $k => $pt) {
    $post_type_rows[] = [
      esc_html($k),                      // Post type slug (e.g., 'post', 'page', 'event')
      esc_html($pt['label']),           // Label
      esc_html($pt['file']),            // Source file
      esc_html($pt['used_count']),      // Count of published items
      esc_html($pt['last_used'])        // Last used date & time
    ];
  }

  wpsi_render_tab_content(
    'post-types',
    'Post Types',
    ['Type', 'Label', 'Location', 'Used Count', 'Last Used (Date & Time)'],
    $post_type_rows
  );



  // Templates
  $template_rows = [];
  foreach ($data['templates'] as $tpl) {
    $template_rows[] = [
      esc_html($tpl['title']),
      esc_html($tpl['path'])
    ];
  }
  wpsi_render_tab_content('templates', 'Templates', ['Template Title', 'File'], $template_rows);

  // Shortcodes
  $shortcode_rows = [];
  foreach ($data['shortcodes'] as $tag => $info) {
    $used = !empty($info['used_in']) ? esc_html(implode(', ', $info['used_in'])) : 'Not used';
    $shortcode_rows[] = [
      esc_html('[' . $tag . ']'),
      esc_html($info['file']),
      $used
    ];
  }
  wpsi_render_tab_content('shortcodes', 'Shortcodes', ['Shortcode', 'File', 'Used In'], $shortcode_rows);

  // Hooks
  $hook_rows = [];
  foreach ($data['hooks'] as $hook) {
    $hook_rows[] = [
      esc_html($hook['type']),
      esc_html($hook['hook']),
      esc_html($hook['file'])
    ];
  }
  wpsi_render_tab_content('hooks', 'Hooks', ['Type', 'Hook', 'Registered In'], $hook_rows);

  // REST APIs
  $api_rows = [];

  if (!empty($data['apis']) && is_array($data['apis'])) {
    foreach ($data['apis'] as $api) {
      $used = !empty($api['used_in']) ? implode('<br>', $api['used_in']) : 'Not used';
      $api_rows[] = [
        esc_html($api['namespace'] . $api['route']),
        esc_html($api['file'] . ' (line ' . $api['line'] . ')'),
        $used // allow HTML (line breaks)
      ];
    }
    wpsi_render_tab_content('apis', 'REST API Endpoints', ['Endpoint', 'Location', 'Used In'], $api_rows);
  } else {
    wpsi_render_tab_content('apis', 'REST API Endpoints', ['Endpoint', 'Location', 'Used In'], $api_rows);
  }

  // CDN Links
  $cdn_rows = [];
  foreach ($data['cdn_links'] as $cdn) $cdn_rows[] = [$cdn['lib'], $cdn['file']];
  wpsi_render_tab_content('cdn', 'CDN / JS Usage', ['Library', 'File'], $cdn_rows);







$log_file = WP_CONTENT_DIR . '/site-inspector.log';

// Handle Clear Logs button submission
if (isset($_POST['wpsi_clear_logs']) && check_admin_referer('wpsi_clear_logs_action')) {
    file_put_contents($log_file, ''); // Clear log file
    $log_cleared = true;
}

$log_rows = [];
if (file_exists($log_file)) {
    $log_lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($log_lines as $line) {
        if (preg_match('/^(Analysis|Theme|Plugins|Pages|Posts|Templates|Shortcodes)/i', $line)) {
            continue;
        }

        if (preg_match('/^\[(ERROR|WARNING|NOTICE|DEPRECATED|FATAL)\]\s([\d\-:\s]+)\s\-\s(.+?)(?:\s\(File:\s(.+?),\sLine:\s(\d+)\))?$/', $line, $matches)) {
            $type = strtoupper($matches[1]);
            $timestamp = trim($matches[2]);
            $message = trim($matches[3]);
            $file = isset($matches[4]) ? 'File: ' . trim($matches[4]) : '';
            $line_no = isset($matches[5]) ? 'Line: ' . trim($matches[5]) : '';

            $full_message = $message;
            if ($file || $line_no) {
                $full_message .= ' (' . trim("$file $line_no") . ')';
            }

            $ai_button = '<button class="button ask-ai-button" data-message="' . esc_attr($full_message) . '">Ask AI</button>';

            $log_rows[] = [
                esc_html($timestamp ? date('m/d/y, h:ia', strtotime($timestamp)) : 'N/A'),
                esc_html($type),
                esc_html($full_message),
                $ai_button
            ];
        }
    }
}

if (empty($log_rows)) {
    $log_rows[] = ['—', 'INFO', 'No error logs found.', ''];
}

if (!empty($log_cleared)) {
    echo '<div class="notice notice-success"><p>Logs cleared successfully.</p></div>';
}

ob_start(); ?>
<div style="display: inline-block; margin-left: 20px;">
    <form method="post" style="display: inline;">
        <?php wp_nonce_field('wpsi_clear_logs_action'); ?>
        <input type="submit" name="wpsi_clear_logs" class="button button-secondary" value="Clear Logs" />
    </form>
</div>
<?php
$clear_button = ob_get_clean();
$custom_title = 'Error Logs' . $clear_button;

// Render the log table
wpsi_render_tab_content('logs', $custom_title, ['Date', 'Type', 'Message', 'AI'], $log_rows);
?>
<script>
 jQuery(document).ready(function ($) {
    // Only add once
    if (!$('#wpsi-ai-chatbox').length) {
        const chatHtml = `
            <div id="wpsi-ai-chatbox" style="display:none; position:fixed; bottom:20px; right:20px; width:700px;
                height:600px; background:#fff; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.2); 
                z-index:10000;flex-direction:column; font-family:sans-serif;">

                <div style="background:#4b6cb7; color:#fff; padding:12px 16px; font-weight:bold; position:relative;">
                    AI Assistant
                    <button id="wpsi-chat-close" style="position:absolute; right:10px; top:8px; background:none; border:none; color:#fff; font-size:18px; cursor:pointer;">×</button>
                </div>

                <div id="wpsi-chat-messages" style="flex:1; padding:15px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; background:#f7f7f7;">
                    <!-- Messages -->
                </div>

                <div style="padding:10px; border-top:1px solid #ddd; display:flex;">
                    <input type="text" id="wpsi-user-input" placeholder="Ask something..." style="flex:1; padding:8px 10px; border-radius:6px; border:1px solid #ccc; font-size:14px;">
                    <button id="wpsi-send-btn" style="margin-left:8px; padding:8px 12px; background:#4b6cb7; color:#fff; border:none; border-radius:6px; cursor:pointer;">Send</button>
                </div>
            </div>
        `;
        $('body').append(chatHtml);
    }

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
        appendMessage('You', message, '#d0ebff', 'flex-end' );

        // Thinking placeholder
const thinkingDiv = $(`
    <div class="wpsi-ai-thinking" style="align-self:flex-start; background:#f0f0f0; padding:10px 14px; border-radius:12px; max-width:90%; display:flex; align-items:center; gap:10px;">
        <div class="wpsi-spinner" style="width:16px; height:16px; border:3px solid #ccc; border-top:3px solid #4b6cb7; border-radius:50%; animation:spin 1s linear infinite;"></div>
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
            },
            success: function (response) {
                thinkingDiv.remove();
                const aiText = response?.data?.response?.replace(/\n/g, "<br>") || '';
                const errorMsg = response?.data?.error || 'Something went wrong.';
                appendMessage('AI', aiText || `<span style="color:red;">${errorMsg}</span>`, '#e7f5e6');
            },
            error: function (xhr, status, error) {
                thinkingDiv.remove();
                appendMessage('AJAX Failed', error, '#ffdede');
            }
        });
    }

    // Trigger chat box from any button with .ask-ai-button
    $('.ask-ai-button').on('click', function () {
        const message = $(this).data('message');
        const $chat = $('#wpsi-chat-messages');
        $('#wpsi-ai-chatbox').fadeIn();
        if (message) sendMessageToAI(message, $chat);
    });

    // Manual input send
    $(document).on('click', '#wpsi-send-btn', function () {
        const input = $('#wpsi-user-input');
        const message = input.val().trim();
        if (!message) return;
        input.val('');
        sendMessageToAI(message, $('#wpsi-chat-messages'));
    });

    // Enter key triggers send
    $(document).on('keypress', '#wpsi-user-input', function (e) {
        if (e.which === 13) {
            $('#wpsi-send-btn').click();
        }
    });

    // Close button
    $(document).on('click', '#wpsi-chat-close', function () {
        $('#wpsi-ai-chatbox').fadeOut();
    });
});




</script>
</div>

<!-- Export Buttons -->
<div class="export-buttons">
  <button id="exportExcel">Export to Excel <i class="fa-solid fa-file-export"></i></button>
</div>


<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>

<script>
  // Tab functionality
  document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
      // Remove active class from all buttons and contents
      document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

      // Add active class to clicked button and corresponding content
      button.classList.add('active');
      const tabId = button.getAttribute('data-tab');
      document.getElementById(tabId).classList.add('active');
    });
  });

  // Set first tab as active by default
  document.querySelector('.tab-button').click();
  const WPSI_THEME_NAME = "<?php echo $theme_name_safe; ?>";
  document.getElementById("exportExcel").addEventListener("click", () => {
    const wb = XLSX.utils.book_new();
    document.querySelectorAll("table[data-title]").forEach(table => {
      const title = table.dataset.title.replace(/[:\\\/\?\*\[\]]/g, '').substring(0, 31);
      const ws = XLSX.utils.table_to_sheet(table);
      XLSX.utils.book_append_sheet(wb, ws, title);
    });

    const themeName = (typeof WPSI_THEME_NAME !== 'undefined') ? WPSI_THEME_NAME.replace(/\s+/g, '_') : 'Site_Inspector';
    XLSX.writeFile(wb, `${themeName}_Inspector_Report.xlsx`);
  });

  // Plugin Pie Chart
  new Chart(document.getElementById('pluginPieChart'), {
    type: 'pie',
    data: {
      labels: ['Active', 'Inactive'],
      datasets: [{
        data: [
          <?php echo count(array_filter($plugins, fn($p) => $p['status'] === 'Active')); ?>,
          <?php echo count(array_filter($plugins, fn($p) => $p['status'] !== 'Active')); ?>
        ],
        backgroundColor: ['#2ecc71', '#e74c3c']
      }]
    }
  });

  // Page Pie Chart
  new Chart(document.getElementById('pagePieChart'), {
    type: 'pie',
    data: {
      labels: ['Published', 'Draft'],
      datasets: [{
        data: [
          <?php echo count(array_filter($pages, fn($p) => strtolower($p['status']) === 'publish')); ?>,
          <?php echo count(array_filter($pages, fn($p) => strtolower($p['status']) === 'draft')); ?>
        ],
        backgroundColor: ['#3498db', '#95a5a6']
      }]
    }
  });

  // Post Pie Chart
  new Chart(document.getElementById('postPieChart'), {
    type: 'pie',
    data: {
      labels: ['Published Posts', 'Draft Posts'],
      datasets: [{
        data: [
          <?php echo count(array_filter($posts, fn($p) => strtolower($p['status']) === 'publish')); ?>,
          <?php echo count(array_filter($posts, fn($p) => strtolower($p['status']) === 'draft')); ?>
        ],
        backgroundColor: ['#9b59b6', '#f39c12']
      }]
    }
  });
  // Combined Bar Chart
  new Chart(document.getElementById('combinedBarChart'), {
    type: 'bar',
    data: {
      labels: [
        "Posts", "Plugins", "Pages",
        "Post Types", "Templates", "Shortcodes",
        "REST APIs"
      ],
      datasets: [{
        label: 'Total Items',
        data: [
          <?php echo count($posts ?? []); ?>,
          <?php echo count($plugins ?? []); ?>,
          <?php echo count($pages ?? []); ?>,
          <?php echo count($data['post_types'] ?? []); ?>,
          <?php echo count($data['templates'] ?? []); ?>,
          <?php echo count($data['shortcodes'] ?? []); ?>,
          <?php echo count($data['apis'] ?? []); ?>,
        ],
        backgroundColor: '#0073aa'
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          mode: 'index',
          intersect: false
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Category'
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Count'
          }
        }
      }
    }
  });
</script>
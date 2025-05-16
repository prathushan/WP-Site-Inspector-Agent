
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
  </div>

  <?php
  function wpsi_render_tab_content($id, $title, $headers, $rows, $per_page = 10)
  {
    $total_rows = count($rows);
    $total_pages = ceil($total_rows / $per_page);
    $safe_id = htmlspecialchars($id, ENT_QUOTES);
    $safe_title = htmlspecialchars($title, ENT_QUOTES);

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
      foreach ($row as $col) {
        echo "<td>" . htmlspecialchars($col, ENT_QUOTES) . "</td>";
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
    ];
}

wpsi_render_tab_content(
    'plugins',
    'Plugins',
    ['Plugin Name', 'Status', 'Update Status','Last Updated'],
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
  foreach ($data['apis'] as $api) {
    $used = $api['used_in'] ? implode('<br>', $api['used_in']) : 'Not used';
    $api_rows[] = [$api['namespace'] . $api['route'], $api['file'] . ' (line ' . $api['line'] . ')', $used];
  }
  wpsi_render_tab_content('apis', 'REST API Endpoints', ['Endpoint', 'Location', 'Used In'], $api_rows);

  // CDN Links
  $cdn_rows = [];
  foreach ($data['cdn_links'] as $cdn) $cdn_rows[] = [$cdn['lib'], $cdn['file']];
  wpsi_render_tab_content('cdn', 'CDN / JS Usage', ['Library', 'File'], $cdn_rows);
  ?>
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
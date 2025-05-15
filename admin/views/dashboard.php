<?php
if (!defined('ABSPATH')) exit;

$analyzer = new WP_Site_Inspector_Analyzer();
$data = $analyzer->analyze();

$plugins = $data['plugins'];
$pages = $data['pages'];
$posts = $data['posts'];

echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
?>

<style>
body {
  font-family: Arial, sans-serif;
  background: #fafafa;
}
h2 {
  margin-top: 40px;
  color: #333;
}
.wpsi-section {
  margin-bottom: 50px;
}
.wpsi-table-wrap {
  border: 1px solid #ddd;
  border-radius: 5px;
}
table {
  border-collapse: collapse;
  width: 100%;
}
th, td {
  padding: 10px 15px;
  border: 1px solid #ddd;
  text-align: left;
}
th {
  background-color: #f0f0f0;
  position: sticky;
  top: 0;
}
tr:hover {
  background-color: #f9f9f9;
}
.export-buttons {
  position: fixed;
  bottom: 30px;
  right: 30px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 9999;
}
.export-buttons button {
  background: #0073aa;
  color: white;
  border: none;
  padding: 12px 20px;
  font-weight: bold;
  border-radius: 5px;
  cursor: pointer;
}
canvas {
  max-width: 100%;
}
.wpsi-dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}
.wpsi-chart-card {
  background: #fff;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.wpsi-chart-card h3 {
  margin-top: 0;
  font-size: 16px;
  margin-bottom: 10px;
}
.wpsi-chart-card canvas {
  max-height: 200px !important;
}

/* Tabs styling */
.tab-container {
  margin-top: 20px;
}
.tab-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-bottom: 10px;
}
.tab-button {
  padding: 8px 16px;
  background: #e0e0e0;
  border: none;
  border-radius: 4px 4px 0 0;
  cursor: pointer;
  font-weight: bold;
}
.tab-button.active {
  background: #0073aa;
  color: white;
}
.tab-content {
  display: none;
  background: white;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 0 5px 5px 5px;
}
.tab-content.active {
  display: block;
}
</style>

<!-- Charts Section -->
<div class="wpsi-dashboard-grid">
  <div class="wpsi-chart-card">
    <h3>Plugins (Active vs Inactive)</h3>
    <canvas id="pluginPieChart"></canvas>
  </div>
  <div class="wpsi-chart-card">
    <h3>Pages (Published vs Draft)</h3>
    <canvas id="pagePieChart"></canvas>
  </div>
  <div class="wpsi-chart-card">
    <h3>Posts (Published vs Draft)</h3>
    <canvas id="postPieChart"></canvas>
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
  function wpsi_render_tab_content($id, $title, $headers, $rows) {
    echo "<div id='$id' class='tab-content'>";
    echo "<h2>$title</h2>";
    echo "<div class='wpsi-table-wrap'><table data-title='$title'><thead><tr>";
    foreach ($headers as $th) echo "<th>$th</th>";
    echo "</tr></thead><tbody>";
    foreach ($rows as $row) {
      echo "<tr>";
      foreach ($row as $col) echo "<td>$col</td>";
      echo "</tr>";
    }
    echo "</tbody></table></div></div>";
  }

  // Theme Info
  $theme = $data['theme'];
  wpsi_render_tab_content('theme', 'Theme Info', ['Property', 'Value'], [
    ['Active Theme', $theme['name'] . ' v' . $theme['version']],
    ['Theme Type', $theme['type']]
  ]);

  // Builders
  $builder_rows = [];
  foreach ($data['builders'] as $b) $builder_rows[] = [$b['name'], $b['status']];
  if (!$builder_rows) $builder_rows[] = ['None Detected', '-'];
  wpsi_render_tab_content('builders', 'Theme Builders', ['Builder', 'Status'], $builder_rows);

  // Plugins
  $plugin_rows = [];
  foreach ($plugins as $p) $plugin_rows[] = [$p['name'], $p['status']];
  wpsi_render_tab_content('plugins', 'Plugins', ['Plugin Name', 'Status'], $plugin_rows);

  // Pages (no 'used' column)
  $page_rows = [];
  foreach ($pages as $page) $page_rows[] = [$page['title'], $page['status']];
  wpsi_render_tab_content('pages', 'Pages', ['Title', 'Status'], $page_rows);

  // Posts (no 'used' column)
  $post_rows = [];
  foreach ($posts as $post) $post_rows[] = [$post['title'], $post['status']];
  wpsi_render_tab_content('posts', 'Posts', ['Title', 'Status'], $post_rows);

  // Post Types
  $post_type_rows = [];
  foreach ($data['post_types'] as $k => $pt) $post_type_rows[] = [$k, $pt['label'], $pt['file']];
  wpsi_render_tab_content('post-types', 'Post Types', ['Type', 'Label', 'Location'], $post_type_rows);

  // Templates
  $template_rows = [];
  foreach ($data['templates'] as $tpl) $template_rows[] = [$tpl['title'], $tpl['path']];
  wpsi_render_tab_content('templates', 'Templates', ['Template Title', 'File'], $template_rows);

  // Shortcodes
  $shortcode_rows = [];
  foreach ($data['shortcodes'] as $tag => $info) {
    $used = $info['used_in'] ? implode(', ', $info['used_in']) : '<span style="color:gray;">Not used</span>';
    $shortcode_rows[] = ['[' . $tag . ']', $info['file'], $used];
  }
  wpsi_render_tab_content('shortcodes', 'Shortcodes', ['Shortcode', 'File', 'Used In'], $shortcode_rows);

  // Hooks
  $hook_rows = [];
  foreach ($data['hooks'] as $hook) $hook_rows[] = [$hook['type'], $hook['hook'], $hook['file']];
  wpsi_render_tab_content('hooks', 'Hooks', ['Type', 'Hook', 'Registered In'], $hook_rows);

  // REST APIs
  $api_rows = [];
  foreach ($data['apis'] as $api) {
    $used = $api['used_in'] ? implode('<br>', $api['used_in']) : '<span style="color:gray;">Not used</span>';
    $api_rows[] = [$api['namespace'] . $api['route'], $api['file'] . ' (line ' . $api['line'] . ')', $used];
  }
  wpsi_render_tab_content('apis', 'REST API Endpoints', ['Endpoint', 'Location', 'Used In'], $api_rows);

  // CDN Links
  $cdn_rows = [];
  foreach ($data['cdn_links'] as $cdn) $cdn_rows[] = [$cdn['lib'], $cdn['file'], $cdn['url']];
  wpsi_render_tab_content('cdn', 'CDN / JS Usage', ['Library', 'File', 'URL'], $cdn_rows);
  ?>
</div>

<!-- Export Buttons -->
<div class="export-buttons">
  <button id="exportExcel">Export Excel</button>
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

document.getElementById("exportExcel").addEventListener("click", () => {
  const wb = XLSX.utils.book_new();
  document.querySelectorAll("table[data-title]").forEach(table => {
    const title = table.dataset.title.replace(/[:\\\/\?\*\[\]]/g, '').substring(0, 31);
    const ws = XLSX.utils.table_to_sheet(table);
    XLSX.utils.book_append_sheet(wb, ws, title);
  });
  XLSX.writeFile(wb, "Site-Inspector-Report.xlsx");
});

// Plugin Pie Chart
new Chart(document.getElementById('pluginPieChart'), {
  type: 'pie',
  data: {
    labels: ['Active Plugins', 'Inactive Plugins'],
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
    labels: ['Published Pages', 'Draft Pages'],
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
</script>
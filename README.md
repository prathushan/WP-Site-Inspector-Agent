<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WP Site Inspector</title>
  <style>
    body {
      font-family: 'Segoe UI', Roboto, sans-serif;
      line-height: 1.6;
      background-color: #fdfdfd;
      color: #333;
      margin: 0;
      padding: 40px;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }

    h1, h2, h3 {
      color: #1a73e8;
    }

    h1 {
      font-size: 2.2em;
      border-bottom: 2px solid #ddd;
      padding-bottom: 0.3em;
      margin-bottom: 1em;
    }

    h2 {
      margin-top: 2em;
      font-size: 1.8em;
      border-bottom: 1px solid #ddd;
      padding-bottom: 0.2em;
    }

    a {
      color: #d63384;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    ul, ol {
      padding-left: 1.5em;
    }

    code {
      background: #f2f2f2;
      padding: 2px 5px;
      border-radius: 4px;
      font-family: monospace;
    }

    .badge {
      background: #1a73e8;
      color: #fff;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.85em;
      margin-left: 10px;
    }

    .hero-img {
      max-width: 100%;
      border-radius: 10px;
      margin: 20px 0;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .author {
      display: inline-block;
      margin-right: 10px;
    }

    .highlight {
      background: #fff8e1;
      border-left: 5px solid #ffc107;
      padding: 10px 15px;
      margin: 15px 0;
      font-style: italic;
    }

    .footer {
      font-size: 0.9em;
      color: #666;
      border-top: 1px solid #eee;
      padding-top: 20px;
      margin-top: 40px;
    }

    .feature-list li {
      margin-bottom: 8px;
    }

    .contributors {
      margin-top: 10px;
    }

    .contributors img {
      border-radius: 50%;
      width: 40px;
      margin-right: 10px;
    }
  </style>
</head>
<body>

  <h1>🛠️ WP Site Inspector – Your WordPress Debug & Discovery Co-Pilot</h1>

  <p class="highlight">
    Open-source plugin for developers, freelancers, and agencies. Get instant insights into shortcodes, hooks, APIs, logs, and more — no more guesswork.
  </p>

  <p>
    → ⭐ <a href="https://github.com/prathushan/WP-Site-Inspector">Star us on GitHub</a><br />
    → 🪲 <a href="https://github.com/prathushan/WP-Site-Inspector/issues">Report Bug</a> · 💡 <a href="https://github.com/prathushan/WP-Site-Inspector/issues">Request Feature</a>
  </p>

  <img src="./assets/site-inspector.png" alt="Site Inspector Screenshot" class="hero-img" />

  <h2>✨ Features</h2>
  <ul class="feature-list">
    <li>✅ Scan active and parent themes (no config needed)</li>
    <li>✅ List shortcodes, hooks, templates, post types</li>
    <li>✅ Detect REST APIs & CDN links inside themes</li>
    <li>✅ View file paths + line numbers</li>
    <li>✅ Export results to CSV</li>
    <li>✅ AI chatbot for logs & code (BYOK support)</li>
    <li>✅ Multilingual UI (English, French, Spanish)</li>
    <li>✅ One-click .zip full site backup</li>
  </ul>

  <h2>🤖 AI Debugging & Code Assistant</h2>

  <p>
    Connect your own API key from <strong>OpenAI</strong>, <strong>Claude</strong>, or <strong>DeepSeek</strong> to analyze logs and refactor code with AI — right from the WP admin.
  </p>

  <img src="./assets/Ask-AI.png" alt="Ask AI Screenshot" class="hero-img" />
  <img src="./assets/Code-AI.png" alt="Code AI Screenshot" class="hero-img" />
  <img src="./assets/byok.png" alt="BYOK" class="hero-img" />

  <h2>📦 Coming Soon</h2>
  <ul>
    <li>🔄 AI-powered auto-fix for logs</li>
    <li>🔧 Plugin folder scanning</li>
    <li>📚 File & function filtering</li>
    <li>📊 WP-CLI support</li>
    <li>🧠 Visual theme dependency maps</li>
  </ul>

  <h2>⚙️ Installation</h2>
  <ol>
    <li>Download the ZIP from GitHub</li>
    <li>Go to Plugins → Add New → Upload Plugin</li>
    <li>Upload, install, activate</li>
    <li>Start inspecting in WP Admin</li>
  </ol>

  <h2>🙌 Authors</h2>
  <p>
    Made with ❤️ by:
    <span class="author"><a href="https://github.com/prathushan">Prathusha</a></span>,
    <span class="author"><a href="https://github.com/PremKumar-Softscripts">Prem</a></span>,
    <span class="author"><a href="https://github.com/v-i-nay">Vinay</a></span>
  </p>

  <div class="footer">
    📃 MIT License • Contact: prathusha.nammi@gmail.com • <a href="https://github.com/prathushan/WP-Site-Inspector/issues">Open an Issue</a>
  </div>
</body>
</html>

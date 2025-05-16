=== WP Site Inspector ===
Contributors: prathushan, premkumar-softscripts, v-i-nay
Tags: developer tools, hooks, shortcodes, debug, theme analysis, inspect
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Instantly map shortcodes, hooks, APIs, templates, and more in any WordPress theme — a developer’s time-saving debug assistant.

== Description ==

**WP Site Inspector** is a must-have utility for WordPress developers, freelancers, and agencies working with custom or legacy themes. It helps you instantly scan the active theme (and parent theme) to uncover:

- Shortcodes (`add_shortcode`, `do_shortcode`)
- Action and filter hooks (`add_action`, `apply_filters`)
- External API and CDN references
- JavaScript and CSS file usage
- Custom post types
- Templates being used
- Plugins referenced via theme code
- Published pages and posts

Instead of manually searching through theme files, this tool shows you what’s used and where — with file paths and line numbers.

== Features ==

- Scans both active and parent theme directories
- Displays results with line numbers and file paths
- Output is only visible to logged-in administrators
- All dynamic data is escaped for security
- No database changes or external API calls
- Clean and readable interface — no setup required

== How It Helps Developers ==

Whether you're onboarding a new client or debugging a complex site, WP Site Inspector helps you:

- Understand custom functionality
- Audit faster
- Refactor safely
- Debug smarter
- Save time and improve code handovers

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wp-site-inspector` directory, or install via the WordPress plugin screen directly.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. Navigate to **Tools > Site Inspector** to view the scan results.

== Frequently Asked Questions ==

= Who can see the output? =
Only logged-in administrators have access to the scan results.

= Does this plugin modify my database? =
No, it only reads theme files and displays analysis results.

= Is it safe to use on production sites? =
Yes, as long as only trusted administrators have access.

== Screenshots ==
1. Visual output showing hooks and shortcodes
2. File path and line number display

== Changelog ==

= 1.0 =
* Initial release with core inspection tools.

== Upgrade Notice ==

= 1.0 =
First stable version. Inspect theme code quickly and securely.

== Roadmap ==

- File search and filter
- Plugin folder scan
- WP-CLI integration for terminal users

== License ==

This plugin is licensed under the GPLv2 or later.

== Author ==

Created with ❤️ by Prathusha, Prem, and Vinay  
Visit [https://github.com/prathushan](https://github.com/prathushan) for contributions or ideas.


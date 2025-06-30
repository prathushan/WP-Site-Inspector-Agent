<?php
    if (! defined('ABSPATH')) {
        exit;
    }

    // Add the test endpoint handler
    add_action('wp_ajax_wpsi_test_fix', function () {
        check_ajax_referer('wpsi_test_fix', 'nonce');
        wp_send_json_success(['status' => 'test_ok']);
    });
?>

<style>
#wpsi-fix-agent-modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    font-family: "Segoe UI", Roboto, sans-serif;
}

#wpsi-fix-agent-box {
    background: #fff;
    border-radius: 12px;
    padding: 30px 25px;
    max-width: 580px;
    width: 90%;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    text-align: center;
    animation: popIn 0.3s ease-out;
}

@keyframes popIn {
    from { transform: scale(0.9); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

#wpsi-fix-agent-box h2 {
    font-size: 20px;
    margin-bottom: 15px;
    color: #333;
}

#wpsi-agent-log-msg {
    font-size: 14px;
    color: #444;
    margin: 15px auto 10px;
    padding: 12px 14px;
    background: #f7f9fb;
    border-left: 4px solid #4b6cb7;
    text-align: left;
    word-break: break-word;
    max-height: 200px;
    overflow-y: auto;
    border-radius: 6px;
}

#wpsi-agent-status {
    font-size: 16px;
    margin-top: 15px;
    color: #333;
}

.wpsi-agent-spinner {
    margin: 18px auto;
    border: 4px solid #ccc;
    border-top: 4px solid #4b6cb7;
    border-radius: 50%;
    width: 42px;
    height: 42px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div id="wpsi-fix-agent-modal">
    <div id="wpsi-fix-agent-box">
        <h2>AI Fix in Progress...</h2>
        <div class="wpsi-agent-spinner"></div>
        <div id="wpsi-agent-log-msg">Loading error context...</div>
        <div id="wpsi-agent-status">Pending...</div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    window.wpsiStartFixAgent = function(logMessage) {
        $('#wpsi-fix-agent-modal').fadeIn();
        $('#wpsi-agent-log-msg').text(logMessage);
        $('#wpsi-agent-status').text('Analyzing issue...');

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'wpsi_fix_with_ai',
                message: logMessage
            },
            success: function(response) {
                if (response?.success && response?.data?.status === 'success') {
                    $('#wpsi-agent-status').html('<span style="color:green;"> Fix completed successfully at Line ' + response.data.line + '.</span>');
                } else {
                    const err = response?.data?.error || 'Unknown error';
                    const rollback = response?.data?.rollback;
                    const file = response?.data?.file || 'Unknown file';
                    const line = response?.data?.line || '—';

                    let msg = `<span style="color:red;"> Failed: ${err}</span>`;

                    if (rollback) {
                        msg += `<br><span style="color:#e67e22;">Rollback was applied. Please manually review:</span><br><strong>${file}</strong> at line <strong>${line}</strong>`;
                    }

                    $('#wpsi-agent-status').html(msg);
                }

                setTimeout(function() {
                    $('#wpsi-fix-agent-modal').fadeOut();
                    location.reload(); // Refresh to reflect changes or rollback
                }, 4000);
            },
            error: function(xhr, status, err) {
                $('#wpsi-agent-status').html('<span style="color:red;">AJAX error: ' + err + '</span>');
                setTimeout(() => $('#wpsi-fix-agent-modal').fadeOut(), 3000);
            }
        });
    };

    // Optional: fallback for dynamically bound elements
    $(document).on('click', '.solve-with-ai-button', function() {
        const message = $(this).data('message');
        if (message) {
            wpsiStartFixAgent(message);
        }
    });
});
</script>

<?php
if (!defined('WPINC')) {
    die;
}

$settings = get_option('airtable_viewer_settings');
?>

<div class="wrap">
    <h1>הגדרות Airtable Viewer</h1>

    <div class="notice notice-info">
        <p><strong>הגדרת התוסף:</strong></p>
        <ol>
            <li>הזן את מפתח ה-API של Airtable שלך (ניתן למצוא אותו בהגדרות החשבון ב-Airtable)</li>
            <li>הזן את מזהה הבסיס הדיפולטי (אופציונלי) שישמש כברירת מחדל בתבניות חדשות</li>
            <li>הגדר את זמן המטמון (Cache) כדי לשפר את הביצועים</li>
            <li>הוסף סגנונות CSS גלובליים שיחולו על כל התצוגות</li>
        </ol>
    </div>

    <?php settings_errors(); ?>

    <form method="post" action="">
        <?php wp_nonce_field('airtable_viewer_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="api_key">מפתח API</label></th>
                <td>
                    <input type="password" id="api_key" name="api_key" class="regular-text"
                           value="<?php echo esc_attr($settings['api_key']); ?>" required>
                    <p class="description">
                        מפתח ה-API של Airtable שלך. ניתן למצוא אותו 
                        <a href="https://airtable.com/account" target="_blank">בהגדרות החשבון של Airtable</a>.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="default_base_id">מזהה בסיס דיפולטי</label></th>
                <td>
                    <input type="text" id="default_base_id" name="default_base_id" class="regular-text"
                           value="<?php echo esc_attr($settings['default_base_id']); ?>">
                    <p class="description">
                        מזהה בסיס Airtable שישמש כברירת מחדל. ניתן למצוא אותו בדוקומנטציית ה-API של הבסיס.
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="cache_duration">זמן מטמון (שניות)</label></th>
                <td>
                    <input type="number" id="cache_duration" name="cache_duration" class="small-text"
                           value="<?php echo esc_attr($settings['cache_duration']); ?>" min="0">
                    <p class="description">
                        כמה זמן לשמור את תוצאות ה-API במטמון (בשניות). הגדר ל-0 כדי לבטל את המטמון.
                        <br>מומלץ: 300 שניות (5 דקות)
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="global_styles">סגנונות גלובליים</label></th>
                <td>
                    <textarea id="global_styles" name="global_styles" class="large-text code" rows="10"><?php 
                        echo esc_textarea($settings['global_styles']); 
                    ?></textarea>
                    <p class="description">
                        קוד CSS שיחול על כל תצוגות Airtable. יתווסף ל-&lt;head&gt; של האתר.
                        <br>דוגמה:
                        <pre>
.airtable-viewer-container {
    margin: 20px 0;
}
.airtable-viewer-pagination {
    text-align: center;
}
                        </pre>
                    </p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="submit" class="button button-primary">שמור הגדרות</button>
            <button type="button" id="test-connection" class="button">בדוק חיבור</button>
        </p>
    </form>

    <div class="airtable-viewer-help">
        <h3>טיפים להגדרה:</h3>
        <ul>
            <li>מומלץ להשתמש במטמון כדי לשפר את ביצועי האתר ולהפחית את מספר הקריאות ל-API</li>
            <li>אם אתה משתמש בבסיס Airtable אחד, הגדר אותו כברירת מחדל כדי לחסוך זמן ביצירת תבניות</li>
            <li>השתמש בסגנונות הגלובליים כדי לשמור על עיצוב אחיד בכל התצוגות</li>
            <li>בדוק את החיבור אחרי הזנת מפתח ה-API כדי לוודא שהוא תקין</li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#test-connection').click(function() {
        var $button = $(this);
        $button.prop('disabled', true).text('בודק חיבור...');
        
        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'airtable_viewer_test_connection',
                nonce: airtableViewerAdmin.nonce,
                api_key: $('#api_key').val()
            },
            success: function(response) {
                if (response.success) {
                    alert('החיבור הצליח! מפתח ה-API תקין.');
                } else {
                    alert('החיבור נכשל: ' + response.data);
                }
            },
            error: function() {
                alert('שגיאה בבדיקת החיבור. אנא נסה שנית.');
            },
            complete: function() {
                $button.prop('disabled', false).text('בדוק חיבור');
            }
        });
    });
});</script> 
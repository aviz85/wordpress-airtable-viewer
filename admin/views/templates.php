<?php
if (!defined('WPINC')) {
    die;
}

$templates = $this->template->get_all();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">תבניות Airtable</h1>
    <a href="?page=airtable-viewer&action=new" class="page-title-action">הוסף תבנית חדשה</a>
    
    <div class="notice notice-info">
        <p><strong>כיצד להשתמש בתוסף:</strong></p>
        <ol>
            <li>צור תבנית חדשה על ידי לחיצה על "הוסף תבנית חדשה"</li>
            <li>הגדר את פרטי התבנית (שם, מזהה בסיס נתונים, טבלה וכו')</li>
            <li>עצב את התצוגה באמצעות HTML עם משתנים מותאמים אישית (למשל: <code>{{שם_השדה}}</code>)</li>
            <li>העתק את קוד הקיצור (shortcode) והדבק אותו בכל מקום בו תרצה להציג את הנתונים</li>
        </ol>
    </div>
    
    <?php settings_errors(); ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>שם התבנית</th>
                <th>קוד קיצור</th>
                <th>מזהה בסיס</th>
                <th>טבלה</th>
                <th>עימוד</th>
                <th>פעולות</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="6">לא נמצאו תבניות. <a href="?page=airtable-viewer&action=new">צור תבנית חדשה</a>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><?php echo esc_html($template['name']); ?></td>
                        <td>
                            <code>[airtable_view template="<?php echo esc_attr($template['shortcode']); ?>"]</code>
                            <button class="button button-small copy-shortcode" data-shortcode='[airtable_view template="<?php echo esc_attr($template['shortcode']); ?>"]'>
                                העתק
                            </button>
                        </td>
                        <td><?php echo esc_html($template['base_id']); ?></td>
                        <td><?php echo esc_html($template['table_name']); ?></td>
                        <td><?php echo $template['pagination_enabled'] ? 'כן (' . esc_html($template['items_per_page']) . ' פריטים לעמוד)' : 'לא'; ?></td>
                        <td>
                            <a href="?page=airtable-viewer&action=edit&template_id=<?php echo esc_attr($template['id']); ?>" class="button button-small">
                                ערוך
                            </a>
                            <button class="button button-small button-link-delete delete-template" data-id="<?php echo esc_attr($template['id']); ?>">
                                מחק
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="airtable-viewer-help">
        <h3>טיפים לשימוש:</h3>
        <ul>
            <li>ניתן להשתמש בפרמטרים נוספים בקוד הקיצור, למשל: <code>[airtable_view template="products" filter="category=electronics" sort="price:desc"]</code></li>
            <li>ניתן להגדיר עימוד (pagination) לכל תבנית בנפרד</li>
            <li>השתמש במשתנים כמו <code>{{field_name}}</code> בתבנית ה-HTML כדי להציג שדות מ-Airtable</li>
            <li>ניתן להשתמש במשתנים מיוחדים כמו <code>{{index}}</code> ו-<code>{{record_id}}</code></li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').click(function() {
        var shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(function() {
            var $button = $(this);
            $button.text('הועתק!');
            setTimeout(function() {
                $button.text('העתק');
            }, 2000);
        }.bind(this));
    });

    $('.delete-template').click(function() {
        if (!confirm('האם אתה בטוח שברצונך למחוק תבנית זו? פעולה זו אינה ניתנת לביטול.')) {
            return;
        }

        var $row = $(this).closest('tr');
        var templateId = $(this).data('id');

        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'airtable_viewer_delete_template',
                template_id: templateId,
                nonce: airtableViewerAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        if ($('tbody tr').length === 0) {
                            $('tbody').html('<tr><td colspan="6">לא נמצאו תבניות. <a href="?page=airtable-viewer&action=new">צור תבנית חדשה</a>.</td></tr>');
                        }
                    });
                } else {
                    alert('שגיאה במחיקת התבנית: ' + response.data);
                }
            },
            error: function() {
                alert('שגיאה במחיקת התבנית. אנא נסה שנית.');
            }
        });
    });
});</script> 
<?php
if (!defined('WPINC')) {
    die;
}

$template_data = null;
if (isset($template_id) && $template_id > 0) {
    $template_data = $this->template->get($template_id);
    if (!$template_data) {
        wp_die('התבנית לא נמצאה.');
    }
}

$settings = get_option('airtable_viewer_settings');
?>

<div class="wrap">
    <h1><?php echo $template_data ? 'עריכת תבנית' : 'תבנית חדשה'; ?></h1>

    <div class="notice notice-info">
        <p><strong>מדריך מהיר ליצירת תבנית:</strong></p>
        <ol>
            <li>הזן שם ייחודי לתבנית וקוד קיצור (shortcode) שישמש להצגת התוכן</li>
            <li>הזן את מזהה הבסיס (Base ID) ושם הטבלה מ-Airtable</li>
            <li>הגדר את תבנית ה-HTML עם תגיות משתנים בפורמט <code>{{שם_השדה}}</code></li>
            <li>הפעל עימוד (pagination) אם נדרש והגדר כמה פריטים להציג בכל עמוד</li>
        </ol>
    </div>

    <?php settings_errors(); ?>

    <form id="template-form" method="post">
        <input type="hidden" name="action" value="airtable_viewer_save_template">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('airtable_viewer_admin_nonce'); ?>">
        <?php if ($template_data): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($template_data['id']); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="name">שם התבנית</label></th>
                <td>
                    <input type="text" id="name" name="name" class="regular-text" required
                           value="<?php echo esc_attr($template_data['name'] ?? ''); ?>">
                    <p class="description">שם תיאורי שיעזור לך לזהות את התבנית</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="shortcode">קוד קיצור</label></th>
                <td>
                    <input type="text" id="shortcode" name="shortcode" class="regular-text" required
                           value="<?php echo esc_attr($template_data['shortcode'] ?? ''); ?>">
                    <p class="description">מזהה ייחודי שישמש בקוד הקיצור. לדוגמה: [airtable_view template="your-shortcode"]</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="base_id">מזהה בסיס (Base ID)</label></th>
                <td>
                    <input type="text" id="base_id" name="base_id" class="regular-text" required
                           value="<?php echo esc_attr($template_data['base_id'] ?? $settings['default_base_id']); ?>">
                    <p class="description">מזהה הבסיס מ-Airtable. ניתן למצוא אותו ב-API documentation של הבסיס</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="table_name">שם הטבלה</label></th>
                <td>
                    <input type="text" id="table_name" name="table_name" class="regular-text" required
                           value="<?php echo esc_attr($template_data['table_name'] ?? ''); ?>">
                    <p class="description">שם הטבלה ב-Airtable ממנה יוצגו הנתונים</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="formula">נוסחת סינון</label></th>
                <td>
                    <input type="text" id="formula" name="formula" class="regular-text"
                           value="<?php echo esc_attr($template_data['query_settings']['formula'] ?? ''); ?>">
                    <p class="description">נוסחת Airtable לסינון רשומות. השאר ריק להצגת כל הרשומות</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sort">מיון</label></th>
                <td>
                    <input type="text" id="sort" name="sort" class="regular-text"
                           value="<?php echo esc_attr($template_data['query_settings']['sort'] ?? ''); ?>">
                    <p class="description">הגדרת מיון. לדוגמה: field:asc או field:desc</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="view">שם התצוגה</label></th>
                <td>
                    <input type="text" id="view" name="view" class="regular-text"
                           value="<?php echo esc_attr($template_data['query_settings']['view'] ?? ''); ?>">
                    <p class="description">אופציונלי: שם תצוגה ספציפית מהבסיס ב-Airtable</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="prefix_html">HTML מקדים</label></th>
                <td>
                    <textarea id="prefix_html" name="prefix_html" class="large-text code" rows="5"><?php 
                        echo esc_textarea($template_data['prefix_html'] ?? ''); 
                    ?></textarea>
                    <p class="description">קוד HTML שיוצג לפני רשימת הרשומות (למשל: <code>&lt;ul class="my-list"&gt;</code>)</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="main_html">תבנית HTML ראשית</label></th>
                <td>
                    <textarea id="main_html" name="main_html" class="large-text code" rows="10" required><?php 
                        echo esc_textarea($template_data['main_html'] ?? ''); 
                    ?></textarea>
                    <p class="description">
                        תבנית HTML עבור כל רשומה. השתמש ב-<code>{{שם_השדה}}</code> להצגת ערכים מהשדות.<br>
                        משתנים מיוחדים: <code>{{index}}</code> (מספר הרשומה), <code>{{record_id}}</code> (מזהה הרשומה)
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="suffix_html">HTML מסיים</label></th>
                <td>
                    <textarea id="suffix_html" name="suffix_html" class="large-text code" rows="5"><?php 
                        echo esc_textarea($template_data['suffix_html'] ?? ''); 
                    ?></textarea>
                    <p class="description">קוד HTML שיוצג אחרי רשימת הרשומות (למשל: <code>&lt;/ul&gt;</code>)</p>
                </td>
            </tr>
            <tr>
                <th scope="row">עימוד (Pagination)</th>
                <td>
                    <label>
                        <input type="checkbox" name="pagination_enabled" value="1"
                               <?php checked($template_data['pagination_enabled'] ?? false); ?>>
                        הפעל עימוד
                    </label>
                    <p class="description">חלק את התוצאות למספר עמודים</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="items_per_page">פריטים בעמוד</label></th>
                <td>
                    <input type="number" id="items_per_page" name="items_per_page" class="small-text"
                           value="<?php echo esc_attr($template_data['items_per_page'] ?? 10); ?>" min="1">
                    <p class="description">כמה פריטים להציג בכל עמוד כאשר העימוד מופעל</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary">שמור תבנית</button>
            <a href="?page=airtable-viewer" class="button">ביטול</a>
        </p>
    </form>

    <div class="airtable-viewer-help">
        <h3>דוגמה לתבנית HTML:</h3>
        <pre>
<!-- Prefix HTML -->
&lt;ul class="products"&gt;

<!-- Main HTML -->
&lt;li class="product"&gt;
    &lt;h3&gt;{{name}}&lt;/h3&gt;
    &lt;p class="price"&gt;₪{{price}}&lt;/p&gt;
    &lt;div class="description"&gt;{{description}}&lt;/div&gt;
&lt;/li&gt;

<!-- Suffix HTML -->
&lt;/ul&gt;
        </pre>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#template-form').submit(function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        
        $submitButton.prop('disabled', true).text('שומר...');
        
        $.ajax({
            url: airtableViewerAdmin.ajaxurl,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    window.location.href = '?page=airtable-viewer&message=saved';
                } else {
                    alert('שגיאה בשמירת התבנית: ' + response.data);
                    $submitButton.prop('disabled', false).text('שמור תבנית');
                }
            },
            error: function() {
                alert('שגיאה בשמירת התבנית. אנא נסה שנית.');
                $submitButton.prop('disabled', false).text('שמור תבנית');
            }
        });
    });
});</script> 
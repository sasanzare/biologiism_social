<?php
/**
 * Plugin Name: Biologiism Social Contact
 * Description: Lightweight WhatsApp and Bale contact launcher.
 * Version: 1.1.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: biologiism-social
 */
if (!defined('ABSPATH')) { exit; }

function bio_social_digits($value) {
    $value = strtr((string) $value, array_combine(
        array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'),
        array('0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9')
    ));
    return preg_replace('/\D+/', '', $value);
}
function bio_social_sanitize($input) {
    $input = is_array($input) ? $input : array();
    $phone = bio_social_digits($input['whatsapp'] ?? '');
    if (strpos($phone, '00') === 0) { $phone = substr($phone, 2); }
    if (strpos($phone, '09') === 0 && strlen($phone) === 11) { $phone = '98' . substr($phone, 1); }
    if ($phone && !preg_match('/^[1-9][0-9]{7,14}$/', $phone)) {
        add_settings_error('bio_social', 'invalid_phone', 'شماره واتساپ نامعتبر است؛ شماره موبایل ایران یا شماره بین‌المللی وارد کنید.');
        $phone = bio_social_digits((get_option('bio_social_options', array()))['whatsapp'] ?? '');
    }
    $raw_url = trim((string) ($input['bale'] ?? ''));
    $url = esc_url_raw($raw_url, array('http', 'https'));
    if ($raw_url !== '' && (!$url || !wp_http_validate_url($url))) {
        add_settings_error('bio_social', 'invalid_url', 'لینک بله نامعتبر است؛ آدرس کامل با https:// وارد کنید.');
        $url = (get_option('bio_social_options', array()))['bale'] ?? '';
    }
    return array('whatsapp' => $phone, 'bale' => $url);
}
function bio_social_settings_init() {
    register_setting('bio_social_group', 'bio_social_options', array('sanitize_callback' => 'bio_social_sanitize'));
}
add_action('admin_init', 'bio_social_settings_init');
function bio_social_admin_menu() {
    add_options_page('ارتباط اجتماعی', 'ارتباط اجتماعی', 'manage_options', 'bio-social', 'bio_social_settings_page');
}
add_action('admin_menu', 'bio_social_admin_menu');
function bio_social_settings_page() {
    if (!current_user_can('manage_options')) { return; }
    $options = get_option('bio_social_options', array());
    ?>
    <div class="wrap" dir="rtl"><h1>تنظیمات ارتباط اجتماعی</h1>
        <form action="options.php" method="post">
            <?php settings_fields('bio_social_group'); ?>
            <table class="form-table" role="presentation">
                <tr><th scope="row"><label for="bio-whatsapp">شماره واتساپ</label></th><td><input class="regular-text" dir="ltr" id="bio-whatsapp" name="bio_social_options[whatsapp]" type="tel" value="<?php echo esc_attr($options['whatsapp'] ?? '989339104889'); ?>"><p class="description">مثال: 09339104889 یا شماره با کد کشور؛ برای پنهان‌کردن گزینه، خالی بگذارید.</p></td></tr>
                <tr><th scope="row"><label for="bio-bale">لینک ارتباط در بله</label></th><td><input class="regular-text" dir="ltr" id="bio-bale" name="bio_social_options[bale]" type="url" value="<?php echo esc_attr($options['bale'] ?? ''); ?>" placeholder="https://ble.ir/username"><p class="description">لینک کامل گفت‌وگو را وارد و آزمایش کنید؛ تا زمان واردکردن لینک، گزینه بله نمایش داده نمی‌شود.</p></td></tr>
            </table><?php submit_button('ذخیره تنظیمات'); ?>
        </form>
    </div><?php
}
function bio_social_render() {
    $options = get_option('bio_social_options', array());
    $phone = bio_social_digits($options['whatsapp'] ?? '989339104889');
    $bale = esc_url($options['bale'] ?? '', array('http', 'https'));
    if (!$phone && !$bale) { return; }
    ?>
    <style>
    .bio-contact{position:fixed;right:max(20px,env(safe-area-inset-right));bottom:max(20px,env(safe-area-inset-bottom));z-index:9999;direction:rtl;font-family:inherit;color:#202b2a}.bio-contact summary{list-style:none;display:flex;align-items:center;justify-content:center;gap:8px;min-height:54px;padding:0 18px;border-radius:28px;background:#168b69;color:#fff;cursor:pointer;box-shadow:0 5px 20px #0003;font-size:15px;font-weight:700}.bio-contact summary::-webkit-details-marker{display:none}.bio-contact summary svg{width:23px;height:23px;fill:none;stroke:currentColor;stroke-width:1.8}.bio-contact summary:focus-visible,.bio-contact a:focus-visible{outline:3px solid #f3b633;outline-offset:3px}.bio-contact-panel{position:absolute;bottom:calc(100% + 10px);right:0;min-width:230px;max-width:calc(100vw - 40px);padding:8px;background:#fff;border:1px solid #e4ece8;border-radius:16px;box-shadow:0 8px 28px #0002}.bio-contact-panel a{display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;color:#173b31;text-decoration:none;font-size:14px;white-space:nowrap}.bio-contact-panel .bio-contact-icon{width:22px;height:22px;flex:none}.bio-contact-panel .bio-contact-whatsapp{color:#1b9e62}.bio-contact-panel .bio-contact-bale{color:#00b894}.bio-contact-panel a:hover{background:#edf8f2;color:#126849}
    </style>
    <details class="bio-contact"><summary aria-label="راه‌های ارتباط با ما"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.4 0-2.7-.3-3.8-.9L3 21l1.9-5.5a8.5 8.5 0 1 1 16.1-4Z"/><path d="M8 11.5h9M8 8.5h9M8 14.5h6"/></svg><span>ارتباط با ما</span></summary>
        <div class="bio-contact-panel">
            <?php if ($phone) : ?><a href="<?php echo esc_url('https://wa.me/' . $phone); ?>" target="_blank" rel="noopener noreferrer"><svg class="bio-contact-icon bio-contact-whatsapp" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 11.8a8.5 8.5 0 0 1-12.7 7.4L3 20.5l1.3-4.6A8.5 8.5 0 1 1 20.5 11.8Z"/><path d="M8.3 7.6c.3-.3.6-.3.8.1l1 2c.1.3 0 .5-.3.8l-.6.6c.7 1.5 2 2.8 3.5 3.5l.7-.7c.2-.2.5-.2.7-.1l1.9.9c.4.2.4.5.2.8-.5.8-1.3 1.1-2.2.9-3.1-.7-6.1-3.7-6.8-6.8-.2-.8.2-1.6 1.1-2Z"/></svg><span>ارتباط از طریق واتساپ ↗</span></a><?php endif; ?>
            <?php if ($bale) : ?><a href="<?php echo $bale; ?>" target="_blank" rel="noopener noreferrer"><svg class="bio-contact-icon bio-contact-bale" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="6"/><path d="M8 6v8a3.5 3.5 0 1 0 3.5-3.5H8"/><path d="m13.7 7.8 1.3 1.3 2.5-2.6"/></svg><span>ارتباط از طریق بله ↗</span></a><?php endif; ?>
        </div>
    </details><?php
}
add_action('wp_footer', 'bio_social_render');

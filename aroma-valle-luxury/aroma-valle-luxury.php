<?php
/**
 * Plugin Name: Aroma Valle' Luxury Storefront
 * Description: A complete, responsive luxury perfume storefront for Aroma Valle' with original imagery, curated sections and WooCommerce integration.
 * Version: 2.6.17
 * Author: Aroma Valle'
 * Text Domain: aroma-valle-luxury
 */
if (!defined('ABSPATH')) { exit; }

define('AVLUX_VERSION', '2.6.17');
define('AVLUX_URL', plugin_dir_url(__FILE__));
define('AVLUX_PATH', plugin_dir_path(__FILE__));

function avlux_defaults() {
    return array(
        'brand' => 'Aroma Valle\'',
        'eyebrow' => 'THE ART OF PERSONAL FRAGRANCE',
        'headline' => "A scent that\nbecomes your signature.",
        'subheading' => 'Discover captivating fragrances crafted for unforgettable moments. Find the notes that feel unmistakably you.',
        'announcement' => 'A world of fragrance, delivered to your door · Nationwide delivery across Pakistan',
        'whatsapp' => '923211000926',
        'hero_image' => AVLUX_URL . 'assets/hero-perfume.jpg',
        'gold' => '#c6a15b',
        'shop_page' => 0,
        'about_page' => 0,
        'contact_page' => 0,
        'collections_page' => 0,
        'catalog_page' => 0,
    );
}
function avlux_options() { return wp_parse_args(get_option('avlux_options', array()), avlux_defaults()); }

// Migrate existing installations to the requested exact brand spelling.
function avlux_migrate_brand_spelling() {
    $saved = get_option('avlux_options', array());
    if (is_array($saved) && isset($saved['brand']) && $saved['brand'] === 'Aroma Valle') {
        $saved['brand'] = "Aroma Valle'";
        update_option('avlux_options', $saved);
    }
    $home = get_page_by_path('aroma-valle-home');
    if ($home instanceof WP_Post && $home->post_title === 'Aroma Valle Home') {
        wp_update_post(array('ID' => $home->ID, 'post_title' => "Aroma Valle' Home"));
    }
}
add_action('plugins_loaded', 'avlux_migrate_brand_spelling', 20);

function avlux_register_settings() {
    register_setting('avlux_settings_group', 'avlux_options', 'avlux_sanitize_options');
    add_settings_section('avlux_main', 'Brand & storefront settings', function(){ echo '<p>Personalize the luxury storefront. Your original design and imagery are included with the plugin.</p>'; }, 'avlux-settings');
    $fields = array(
        'brand' => array('Brand name', 'text'),
        'eyebrow' => array('Hero eyebrow', 'text'),
        'headline' => array('Hero headline (use a new line if desired)', 'textarea'),
        'subheading' => array('Hero description', 'textarea'),
        'announcement' => array('Announcement bar', 'text'),
        'whatsapp' => array('WhatsApp number (country code, digits only)', 'text'),
        'hero_image' => array('Hero image URL', 'text'),
        'gold' => array('Accent colour (hex)', 'text'),
    );
    foreach ($fields as $key => $meta) {
        add_settings_field('avlux_'.$key, esc_html($meta[0]), 'avlux_render_field', 'avlux-settings', 'avlux_main', array('key'=>$key, 'type'=>$meta[1]));
    }
}
add_action('admin_init', 'avlux_register_settings');
function avlux_sanitize_options($input) {
    $d = avlux_defaults(); $out = array();
    foreach (array('brand','eyebrow','announcement','whatsapp') as $k) $out[$k] = isset($input[$k]) ? sanitize_text_field($input[$k]) : $d[$k];
    foreach (array('headline','subheading') as $k) $out[$k] = isset($input[$k]) ? sanitize_textarea_field($input[$k]) : $d[$k];
    $out['hero_image'] = isset($input['hero_image']) ? esc_url_raw($input['hero_image']) : $d['hero_image'];
    $out['gold'] = (isset($input['gold']) && preg_match('/^#[0-9a-fA-F]{6}$/', $input['gold'])) ? $input['gold'] : $d['gold'];
    $old = get_option('avlux_options', array());
    foreach (array('shop_page','about_page','contact_page','collections_page','catalog_page') as $k) $out[$k] = absint($old[$k] ?? $d[$k]);
    return $out;
}
function avlux_render_field($args) {
    $o = avlux_options(); $k = $args['key']; $name = 'avlux_options['.$k.']';
    if ($args['type'] === 'textarea') printf('<textarea class="large-text" rows="3" name="%s">%s</textarea>', esc_attr($name), esc_textarea($o[$k]));
    else printf('<input class="regular-text avlux-setting-image" type="text" name="%s" value="%s">', esc_attr($name), esc_attr($o[$k]));
    if ($k === 'hero_image') echo '<button type="button" class="button avlux-setting-pick">Choose / Upload Image</button><script>jQuery(function($){$(document).on("click",".avlux-setting-pick",function(e){e.preventDefault();var field=$(this).prev("input");var frame=wp.media({title:"Choose hero image",button:{text:"Use image"},multiple:false,library:{type:"image"}});frame.on("select",function(){field.val(frame.state().get("selection").first().toJSON().url);});frame.open();});});</script><p class="description">Select or upload the homepage hero image from Media Library.</p>';
    if ($k === 'whatsapp') echo '<p class="description">Example: 923001234567. Do not include + or spaces.</p>';
}

/* v2.3 full site editor and add-product workflow */
function avlux_content_defaults() {
 return array(
 'home_collections_eyebrow'=>'A WORLD OF NOTES','home_collections_title'=>'Find your fragrance.','home_collections_text'=>'From luminous and fresh to deep and sensual, explore a scent for every side of you.',
 'shop_eyebrow'=>"THE AROMA VALLE' COLLECTION",'shop_title'=>'Find your signature scent.','shop_text'=>'Explore the fragrance collection and add your favourites to your shopping bag. All prices are in PKR.',
 'collections_page_eyebrow'=>'DISCOVER THE HOUSE COLLECTIONS','collections_page_title'=>'Find your signature.','collections_page_text'=>"Explore the Aroma Valle' fragrance wardrobe by collection, mood and character.", 
 'home_bestsellers_eyebrow'=>'THE EDIT','home_bestsellers_title'=>'Most loved.','home_bestsellers_text'=>'Discover the fragrances making a lasting impression.',
 'story_eyebrow'=>"THE AROMA VALLE' PHILOSOPHY",'story_title'=>'More than a fragrance. A feeling.','story_text_1'=>'We believe fragrance is the invisible detail that makes a moment unforgettable. It is a personal ritual, a quiet expression of character and a memory that lingers.','story_text_2'=>'Explore expressive scent profiles and discover the one that feels like your own signature.','story_image'=>'',
 'quote_text'=>'A beautiful fragrance is remembered long after the moment has passed.','quote_credit'=>"THE AROMA VALLE' SIGNATURE",'cta_eyebrow'=>'YOUR NEXT CHAPTER STARTS HERE','cta_title'=>'Find the scent that stays with you.',
 'hero_button'=>'Discover the collection','hero_note'=>'FIND THE NOTES THAT FEEL LIKE YOU','header_cta'=>'Explore','nav_home'=>'Home','nav_shop'=>'Shop','nav_collections'=>'Collections','nav_philosophy'=>'Our Philosophy','nav_about'=>'About','nav_contact'=>'Contact','footer_tagline'=>'Leave a lasting impression.',
 'promise_1_title'=>'Curated fragrances','promise_1_text'=>'Thoughtfully selected scents','promise_2_title'=>'Nationwide delivery','promise_2_text'=>'Across Pakistan','promise_3_title'=>'Personal assistance','promise_3_text'=>'Here for your fragrance journey','promise_4_title'=>'Secure checkout','promise_4_text'=>'Shop with confidence',
 'about_eyebrow'=>'OUR PHILOSOPHY','about_title'=>'The art of being remembered.','about_image'=>'','about_text_1'=>"At Aroma Valle', we believe fragrance is deeply personal. The right scent can capture a feeling, define a moment and become part of the story you leave behind.",'about_text_2'=>'Our fragrance house celebrates expressive scent profiles, timeless character and the pleasure of finding a fragrance that feels unmistakably yours.',
 'contact_eyebrow'=>'PERSONAL SERVICE','contact_title'=>'We would love to hear from you.','contact_image'=>'','contact_text'=>'Need help choosing a fragrance or have a question about an order? Our team is happy to assist.','contact_email'=>'iakali27701@gmail.com',
 'collection_1_title'=>'For Him','collection_1_eyebrow'=>'THE CLASSICS','collection_1_text'=>'Confident, fresh and distinctive fragrances selected for him.','collection_1_image'=>'',
 'collection_2_title'=>'For Her','collection_2_eyebrow'=>'THE ELEGANCE','collection_2_text'=>'Expressive, graceful and captivating scents selected for her.','collection_2_image'=>'',
 'collection_3_title'=>'Unisex','collection_3_eyebrow'=>'THE ARTISTRY','collection_3_text'=>'Balanced fragrances made to be enjoyed beyond labels.','collection_3_image'=>'',
 'collection_4_title'=>'Signature Blends','collection_4_eyebrow'=>'THE HOUSE EDIT','collection_4_text'=>'Twenty original blend ideas with 30ml, 50ml and 100ml prices.','collection_4_image'=>''
 );
}
function avlux_content() { return wp_parse_args(get_option('avlux_site_content',array()),avlux_content_defaults()); }

function avlux_content_page() {
 if (!current_user_can('manage_options')) return;
 wp_enqueue_media(); $c=avlux_content();
 $groups=array(
 'Shop & Collections pages'=>array('shop_eyebrow'=>'Shop eyebrow','shop_title'=>'Shop heading','shop_text'=>'Shop introduction','collections_page_eyebrow'=>'Collections page eyebrow','collections_page_title'=>'Collections page heading','collections_page_text'=>'Collections page description'),
 'Homepage sections'=>array('home_collections_eyebrow'=>'Collections eyebrow','home_collections_title'=>'Collections heading','home_collections_text'=>'Collections description','home_bestsellers_eyebrow'=>'Best sellers eyebrow','home_bestsellers_title'=>'Best sellers heading','home_bestsellers_text'=>'Best sellers description','story_eyebrow'=>'Story eyebrow','story_title'=>'Story heading','story_text_1'=>'Story paragraph 1','story_text_2'=>'Story paragraph 2','story_image'=>'Story image','quote_text'=>'Quote text','quote_credit'=>'Quote attribution','cta_eyebrow'=>'Final CTA eyebrow','cta_title'=>'Final CTA heading','hero_button'=>'Hero button text','hero_note'=>'Hero note','header_cta'=>'Header button text','nav_home'=>'Home navigation label','nav_shop'=>'Shop navigation label','nav_collections'=>'Collections navigation label','nav_philosophy'=>'Philosophy navigation label','nav_about'=>'About navigation label','nav_contact'=>'Contact navigation label','promise_1_title'=>'Promise 1 title','promise_1_text'=>'Promise 1 text','promise_2_title'=>'Promise 2 title','promise_2_text'=>'Promise 2 text','promise_3_title'=>'Promise 3 title','promise_3_text'=>'Promise 3 text','promise_4_title'=>'Promise 4 title','promise_4_text'=>'Promise 4 text','footer_tagline'=>'Footer tagline'),
 'Collection cards'=>array('collection_1_title'=>'For Him title','collection_1_eyebrow'=>'For Him eyebrow','collection_1_text'=>'For Him description','collection_1_image'=>'For Him image','collection_2_title'=>'For Her title','collection_2_eyebrow'=>'For Her eyebrow','collection_2_text'=>'For Her description','collection_2_image'=>'For Her image','collection_3_title'=>'Unisex title','collection_3_eyebrow'=>'Unisex eyebrow','collection_3_text'=>'Unisex description','collection_3_image'=>'Unisex image','collection_4_title'=>'Signature Blends title','collection_4_eyebrow'=>'Signature Blends eyebrow','collection_4_text'=>'Signature Blends description','collection_4_image'=>'Signature Blends image'),
 'About page'=>array('about_eyebrow'=>'About eyebrow','about_title'=>'About heading','about_image'=>'About page image','about_text_1'=>'About paragraph 1','about_text_2'=>'About paragraph 2'),
 'Contact page'=>array('contact_eyebrow'=>'Contact eyebrow','contact_title'=>'Contact heading','contact_image'=>'Contact page image','contact_text'=>'Contact description','contact_email'=>'Contact / order email')
 );
 ?>
 <div class="wrap avlux-content-editor"><h1>Aroma Valle' — Website Content Editor</h1><p>Edit homepage sections, collection cards, About and Contact pages. Choose or upload images with the Media Library picker. Save to publish the changes on your website.</p>
 <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="avlux_save_site_content"><?php wp_nonce_field('avlux_save_site_content','avlux_content_nonce'); ?>
 <?php foreach($groups as $heading=>$fields): ?><h2><?php echo esc_html($heading); ?></h2><table class="form-table"><tbody>
 <?php foreach($fields as $key=>$label): $is_image=(strpos($key,'_image')!==false); $is_long=(strpos($key,'text')!==false || in_array($key,array('story_title','cta_title','about_title','contact_title','quote_text'))); ?>
 <tr><th><label for="avc-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th><td>
 <?php if($is_image): ?><input class="regular-text avlux-content-image" id="avc-<?php echo esc_attr($key); ?>" type="url" name="content[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($c[$key]); ?>" placeholder="Image URL"><button type="button" class="button avlux-content-pick">Choose / Upload Image</button><?php if($c[$key]): ?><p><img src="<?php echo esc_url($c[$key]); ?>" style="max-width:180px;max-height:100px;object-fit:cover"></p><?php endif; ?>
 <?php elseif($is_long): ?><textarea class="large-text" rows="3" id="avc-<?php echo esc_attr($key); ?>" name="content[<?php echo esc_attr($key); ?>]"><?php echo esc_textarea($c[$key]); ?></textarea>
 <?php else: ?><input class="regular-text" id="avc-<?php echo esc_attr($key); ?>" type="text" name="content[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($c[$key]); ?>"><?php endif; ?>
 </td></tr><?php endforeach; ?></tbody></table><?php endforeach; ?>
 <?php submit_button('Save website content'); ?></form>
 <script>jQuery(function($){$(document).on('click','.avlux-content-pick',function(e){e.preventDefault();var input=$(this).siblings('.avlux-content-image');var frame=wp.media({title:'Choose website image',button:{text:'Use image'},multiple:false,library:{type:'image'}});frame.on('select',function(){input.val(frame.state().get('selection').first().toJSON().url);});frame.open();});});</script>
 <style>.avlux-content-editor .form-table{background:#fff;border:1px solid #ddd;padding:12px 18px}.avlux-content-editor h2{margin-top:30px;padding:12px;background:#eee}</style></div>
 <?php
}
function avlux_save_site_content() {
 if (!current_user_can('manage_options')) wp_die('Not allowed.');
 check_admin_referer('avlux_save_site_content','avlux_content_nonce');
 $defaults=avlux_content_defaults(); $posted=isset($_POST['content'])&&is_array($_POST['content'])?wp_unslash($_POST['content']):array(); $clean=array();
 foreach($defaults as $key=>$default) {
  $value=$posted[$key]??$default;
  if(strpos($key,'_image')!==false) $clean[$key]=esc_url_raw($value);
  elseif(strpos($key,'text')!==false || in_array($key,array('story_title','cta_title','about_title','contact_title','quote_text'))) $clean[$key]=sanitize_textarea_field($value);
  else $clean[$key]=sanitize_text_field($value);
 }
 update_option('avlux_site_content',$clean,false);
 if(is_email($clean['contact_email'])) update_option('avlux_order_email',$clean['contact_email'],false);
 wp_safe_redirect(add_query_arg(array('page'=>'avlux-content','saved'=>'1'),admin_url('admin.php'))); exit;
}
add_action('admin_post_avlux_save_site_content','avlux_save_site_content');

function avlux_add_product_page() {
 if(!current_user_can('manage_options')) return;
 wp_enqueue_media(); ?>
 <div class="wrap"><h1>Aroma Valle' — Add New Product</h1><p>Add a new perfume or signature blend. It will appear in the catalogue and can be edited later in Products & Prices.</p><div class="notice notice-info inline"><p>After adding, manage its description, image, visibility, stock status and prices under Products & Prices.</p></div>
 <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="avlux_create_product"><?php wp_nonce_field('avlux_create_product','avlux_new_product_nonce'); ?>
 <table class="form-table"><tr><th>Product name</th><td><input class="regular-text" name="product[name]" required></td></tr><tr><th>Product type</th><td><select name="product[kind]"><option value="perfume">Individual perfume</option><option value="blend">Signature blend</option></select></td></tr><tr><th>Collection</th><td><select name="product[gender]"><option>Male</option><option>Female</option><option>Unisex</option></select></td></tr><tr><th>Brand / Inspired by</th><td><input class="regular-text" name="product[brand]"></td></tr><tr><th>Fragrance family / notes</th><td><input class="regular-text" name="product[family]" placeholder="e.g. smoky, warm, woody, amber"></td></tr><tr><th>Customer-facing description</th><td><textarea class="large-text" rows="4" name="product[description]" placeholder="Write the product description customers will see on the website"></textarea></td></tr><tr><th>Strength</th><td><input class="regular-text" name="product[strength]"></td></tr><tr><th>Product image</th><td><input class="regular-text avlux-new-image" type="url" name="product[image_url]"><button type="button" class="button avlux-new-pick">Choose / Upload Image</button></td></tr>
 <?php foreach(array('30ml','50ml','100ml') as $size): ?><tr><th><?php echo esc_html($size); ?> Regular Price (PKR)</th><td><input type="number" min="0" name="product[prices][<?php echo esc_attr($size); ?>]" value="0"></td></tr><tr><th><?php echo esc_html($size); ?> Discount (%)</th><td><input type="number" min="0" max="99.99" step="0.01" name="product[discount_percent][<?php echo esc_attr($size); ?>]" value="0"></td></tr><?php endforeach; ?></table><?php submit_button('Add product to website'); ?></form>
 <script>jQuery(function($){$('.avlux-new-pick').on('click',function(e){e.preventDefault();var input=$('.avlux-new-image');var frame=wp.media({title:'Choose product image',button:{text:'Use image'},multiple:false,library:{type:'image'}});frame.on('select',function(){input.val(frame.state().get('selection').first().toJSON().url);});frame.open();});});</script></div><?php
}
function avlux_create_product() {
 if(!current_user_can('manage_options')) wp_die('Not allowed.');
 check_admin_referer('avlux_create_product','avlux_new_product_nonce');
 $p=isset($_POST['product'])&&is_array($_POST['product'])?wp_unslash($_POST['product']):array();
 $name=sanitize_text_field($p['name']??''); if(!$name) wp_die('Product name is required.');
 $kind=($p['kind']??'')==='blend'?'blend':'perfume'; $id=sanitize_key('custom_'.wp_generate_password(8,false,false));
 $prices=array();$sales=array();$discounts=array();
 foreach(array('30ml','50ml','100ml') as $size){$regular=max(0,(float)($p['prices'][$size]??0));$pct=min(99.99,max(0,(float)($p['discount_percent'][$size]??0)));$prices[$size]=$regular;$discounts[$size]=$pct;$sales[$size]=($regular>0&&$pct>0)?round($regular*(1-$pct/100)):0;}
 $new=array('id'=>$id,'kind'=>$kind,'name'=>$name,'gender'=>sanitize_text_field($p['gender']??'Unisex'),'brand'=>sanitize_text_field($p['brand']??''),'family'=>sanitize_text_field($p['family']??''),'notes'=>sanitize_text_field($p['family']??''),'description'=>sanitize_textarea_field($p['description']??''),'strength'=>sanitize_text_field($p['strength']??''),'image_url'=>esc_url_raw($p['image_url']??''),'visibility'=>'active','stock_status'=>'instock','prices'=>$prices,'sale_prices'=>$sales,'discount_percent'=>$discounts);
 $custom=get_option('avlux_custom_products',array());if(!is_array($custom))$custom=array();$custom[]=$new;update_option('avlux_custom_products',$custom,false);
 wp_safe_redirect(add_query_arg(array('page'=>'avlux-products','avlux_group'=>$kind==='blend'?'blends':'perfumes','avlux_page'=>1,'created'=>'1'),admin_url('admin.php')));exit;
}
add_action('admin_post_avlux_create_product','avlux_create_product');

function avlux_delete_custom_product() {
    if (!current_user_can('manage_options')) wp_die('You are not allowed to do this.');
    $id = sanitize_key(wp_unslash($_GET['product_id'] ?? ''));
    if (!$id || strpos($id, 'custom_') !== 0) wp_die('Invalid custom product.');
    check_admin_referer('avlux_delete_custom_'.$id);
    $items = get_option('avlux_custom_products', array());
    if (!is_array($items)) $items = array();
    $items = array_values(array_filter($items, function($item) use ($id) { return !is_array($item) || ($item['id'] ?? '') !== $id; }));
    update_option('avlux_custom_products', $items, false);
    $overrides = get_option('avlux_catalog_overrides', array());
    if (is_array($overrides) && isset($overrides[$id])) { unset($overrides[$id]); update_option('avlux_catalog_overrides', $overrides, false); }
    wp_safe_redirect(add_query_arg(array('page'=>'avlux-products','deleted'=>'1'), admin_url('admin.php'))); exit;
}
add_action('admin_post_avlux_delete_custom_product','avlux_delete_custom_product');

function avlux_admin_menu() { add_menu_page('Aroma Valle\' Store Manager', 'Aroma Valle\'', 'manage_options', 'avlux-manager', 'avlux_manager_page', 'dashicons-store', 56); add_submenu_page('avlux-manager', 'Website Content', 'Website Content', 'manage_options', 'avlux-content', 'avlux_content_page'); add_submenu_page('avlux-manager', 'Add New Product', 'Add New Product', 'manage_options', 'avlux-add-product', 'avlux_add_product_page'); add_submenu_page('avlux-manager', 'Store Settings', 'Store Settings', 'manage_options', 'avlux-settings', 'avlux_settings_page'); add_submenu_page('avlux-manager', 'Products & Prices', 'Products & Prices', 'manage_options', 'avlux-products', 'avlux_products_page'); }
add_action('admin_menu', 'avlux_admin_menu');
function avlux_settings_page() { if (!current_user_can('manage_options')) return; wp_enqueue_media(); ?>
<div class="wrap"><h1>Aroma Valle — Luxury Storefront</h1><p>Update your brand details, hero image and storefront content below.</p><form method="post" action="options.php"><?php settings_fields('avlux_settings_group'); do_settings_sections('avlux-settings'); submit_button('Save storefront settings'); ?></form><hr><h2>Homepage shortcode</h2><p>Use <code>[aroma_valle_luxury_home]</code> on any page or in a shortcode block.</p></div>
<?php }

function avlux_manager_page() {
    if (!current_user_can('manage_options')) return;
    $data = avlux_catalog_data();
    echo '<div class="wrap"><h1>Aroma Valle\' Store Manager</h1><p>Manage your luxury storefront content from one place.</p>';
    if (isset($_GET['updated'])) echo '<div class="notice notice-success is-dismissible"><p>Product details, pictures, visibility, stock and prices have been saved.</p></div>';
    if (isset($_GET['created'])) echo '<div class="notice notice-success is-dismissible"><p>New product added successfully.</p></div>';
    if (isset($_GET['deleted'])) echo '<div class="notice notice-success is-dismissible"><p>Custom product permanently deleted.</p></div>';
    echo '<div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:20px">';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:22px;min-width:220px"><h2>'.count($data['perfumes']??array()).' Perfumes</h2><p>Update photos and individual selling prices.</p><a class="button button-primary" href="'.esc_url(admin_url('admin.php?page=avlux-products')).'">Manage perfumes & blends</a></div>';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:22px;min-width:220px"><h2>'.count($data['blends']??array()).' Signature Blends</h2><p>Manage images and 30ml, 50ml, 100ml prices.</p><a class="button" href="'.esc_url(admin_url('admin.php?page=avlux-products')).'">Manage blends</a></div>';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:22px;min-width:220px"><h2>Website Content</h2><p>Edit homepage, collections, story, About and Contact pages.</p><a class="button button-primary" href="'.esc_url(admin_url('admin.php?page=avlux-content')).'">Edit website pages</a></div>';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:22px;min-width:220px"><h2>Add New Product</h2><p>Add perfumes and signature blends whenever needed.</p><a class="button" href="'.esc_url(admin_url('admin.php?page=avlux-add-product')).'">Add product</a></div>';
    echo '<div style="background:#fff;border:1px solid #ddd;padding:22px;min-width:220px"><h2>Store Settings</h2><p>Update brand, homepage text, hero image and WhatsApp.</p><a class="button" href="'.esc_url(admin_url('admin.php?page=avlux-settings')).'">Open settings</a></div>';
    echo '</div></div>';
}

function avlux_products_page() {
    if (!current_user_can('manage_options')) return;
    $data = avlux_catalog_data();
    wp_enqueue_media();
    ?>
    <div class="wrap avlux-admin-wrap">
      <h1>Aroma Valle' — Products, Pictures, Regular & Sale Prices</h1>
      <p>Signature Blend prices and 10% sale prices are preloaded from your workbook. All 89 individual perfumes are preloaded with editable starter prices: 30ml Rs. 1,500, 50ml Rs. 2,250 and 100ml Rs. 3,250. These are suggested starter prices, not supplied individual retail prices; change them to your final prices before launch. You can update product names, descriptions, photos, collection, visibility, stock status, regular prices and optional sale prices whenever you like. Set Website status to Hidden to temporarily remove a product from the public catalogue without deleting it. Enter a sale price lower than the regular price to show both prices on the storefront. Leave sale price blank to disable the discount. Changes are saved in WordPress and remain after plugin updates.</p>
      <div class="notice notice-success inline"><p><strong>Input-limit fix:</strong> Products are now edited in pages of 15 items. Save one page at a time; all previously saved products remain unchanged. This avoids the PHP max_input_vars 1,000 limit.</p></div>
      <div class="notice notice-info inline"><p><strong>How to update:</strong> Add an image URL or click “Choose image” to select/upload from Media Library. Enter the regular price and discount percentage for each bottle size. The sale price will calculate automatically whenever you change the regular price or discount percentage. You can use a different discount for each product and size. Leave discount at 0% to show the regular price only.</p></div>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="avlux_save_catalog">
        <?php wp_nonce_field('avlux_save_catalog','avlux_catalog_nonce'); ?>
        <?php
          $active_group = isset($_GET['avlux_group']) && $_GET['avlux_group']==='blends' ? 'blends' : 'perfumes';
          $active_page = max(1, absint($_GET['avlux_page'] ?? 1));
          $per_page = 15;
          $group_titles = array('perfumes'=>'Individual Perfumes','blends'=>'Signature Blends');
          $all_group_items = array_values($data[$active_group] ?? array());
          $total_items = count($all_group_items);
          $total_pages = max(1, (int)ceil($total_items/$per_page));
          $active_page = min($active_page,$total_pages);
          $page_items = array_slice($all_group_items,($active_page-1)*$per_page,$per_page);
        ?>
        <input type="hidden" name="avlux_group" value="<?php echo esc_attr($active_group); ?>">
        <input type="hidden" name="avlux_page" value="<?php echo esc_attr($active_page); ?>">
        <h2><?php echo esc_html($group_titles[$active_group]); ?> <span style="color:#777;font-size:13px;font-weight:400">(<?php echo $total_items; ?> items · page <?php echo $active_page; ?> of <?php echo $total_pages; ?>)</span></h2>
        <p class="avlux-admin-tabs">
          <a class="button <?php echo $active_group==='perfumes'?'button-primary':''; ?>" href="<?php echo esc_url(add_query_arg(array('page'=>'avlux-products','avlux_group'=>'perfumes','avlux_page'=>1),admin_url('admin.php'))); ?>">Individual Perfumes (<?php echo count($data['perfumes']??array()); ?>)</a>
          <a class="button <?php echo $active_group==='blends'?'button-primary':''; ?>" href="<?php echo esc_url(add_query_arg(array('page'=>'avlux-products','avlux_group'=>'blends','avlux_page'=>1),admin_url('admin.php'))); ?>">Signature Blends (<?php echo count($data['blends']??array()); ?>)</a>
        </p>
        <table class="widefat striped avlux-products-table"><thead><tr><th style="width:20%">Product & Description</th><th style="width:14%">Collection / Brand</th><th style="width:18%">Product Image</th><th style="width:12%">Visibility / Stock</th><th>30ml Regular (PKR)</th><th>30ml Discount (%)</th><th>30ml Sale (PKR)</th><th>50ml Regular (PKR)</th><th>50ml Discount (%)</th><th>50ml Sale (PKR)</th><th>100ml Regular (PKR)</th><th>100ml Discount (%)</th><th>100ml Sale (PKR)</th></tr></thead><tbody>
          <?php foreach ($page_items as $item): $id=$item['id']; $ov=get_option('avlux_catalog_overrides',array()); $saved=$ov[$id] ?? array(); ?>
            <tr>
              <td style="min-width:190px">
                <input type="text" style="width:100%;margin-bottom:5px" name="catalog[<?php echo esc_attr($id); ?>][name]" value="<?php echo esc_attr($saved['name'] ?? $item['name']); ?>" aria-label="Product name">
                <input type="text" style="width:100%;margin-bottom:5px" name="catalog[<?php echo esc_attr($id); ?>][family]" value="<?php echo esc_attr($saved['family'] ?? ($item['family'] ?? ($item['notes'] ?? ''))); ?>" placeholder="Fragrance family / notes">
                <textarea style="width:100%;min-height:76px;margin-bottom:5px" name="catalog[<?php echo esc_attr($id); ?>][description]" placeholder="Customer-facing product description"><?php echo esc_textarea($saved['description'] ?? ($item['description'] ?? '')); ?></textarea>
                <small><?php echo esc_html($id); ?></small><input type="hidden" name="catalog[<?php echo esc_attr($id); ?>][kind]" value="<?php echo esc_attr($group); ?>">
                <?php if (strpos($id,'custom_')===0): ?><br><a class="button-link-delete" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=avlux_delete_custom_product&product_id='.rawurlencode($id)), 'avlux_delete_custom_'.$id)); ?>" onclick="return confirm('Permanently delete this custom product? This cannot be undone.');">Delete product</a><?php endif; ?>
              </td>
              <td style="min-width:145px">
                <select name="catalog[<?php echo esc_attr($id); ?>][gender]" style="width:100%;margin-bottom:5px">
                  <?php foreach(array('Male','Female','Unisex') as $g): ?><option value="<?php echo esc_attr($g); ?>" <?php selected($saved['gender'] ?? ($item['gender'] ?? 'Unisex'),$g); ?>><?php echo esc_html($g); ?></option><?php endforeach; ?>
                </select>
                <input type="text" style="width:100%;margin-bottom:5px" name="catalog[<?php echo esc_attr($id); ?>][brand]" value="<?php echo esc_attr($saved['brand'] ?? ($item['brand'] ?? '')); ?>" placeholder="Brand / inspired by">
                <input type="text" style="width:100%" name="catalog[<?php echo esc_attr($id); ?>][strength]" value="<?php echo esc_attr($saved['strength'] ?? ($item['strength'] ?? '')); ?>" placeholder="Strength">
              </td>
              <td><input class="regular-text avlux-image-url" style="width:75%" type="url" name="catalog[<?php echo esc_attr($id); ?>][image_url]" value="<?php echo esc_attr($saved['image_url'] ?? ($item['image_url'] ?? '')); ?>" placeholder="Image URL"><button type="button" class="button avlux-pick-image">Choose image</button></td>
              <td style="min-width:130px"><label>Website status<br><select name="catalog[<?php echo esc_attr($id); ?>][visibility]" style="width:100%"><option value="active" <?php selected($saved['visibility'] ?? ($item['visibility'] ?? 'active'),'active'); ?>>Active</option><option value="hidden" <?php selected($saved['visibility'] ?? ($item['visibility'] ?? 'active'),'hidden'); ?>>Hidden</option></select></label><br><br><label>Stock status<br><select name="catalog[<?php echo esc_attr($id); ?>][stock_status]" style="width:100%"><option value="instock" <?php selected($saved['stock_status'] ?? ($item['stock_status'] ?? 'instock'),'instock'); ?>>In stock</option><option value="outofstock" <?php selected($saved['stock_status'] ?? ($item['stock_status'] ?? 'instock'),'outofstock'); ?>>Out of stock</option></select></label></td>
              <?php foreach(array('30ml','50ml','100ml') as $size):
                $regular_value=(float)($saved['prices'][$size] ?? ($item['prices'][$size] ?? 0));
                $sale_value=(float)($saved['sale_prices'][$size] ?? ($item['sale_prices'][$size] ?? 0));
                $default_pct=($regular_value>0 && $sale_value>0 && $sale_value<$regular_value) ? round((1-($sale_value/$regular_value))*100,2) : 0;
                $discount_value=isset($saved['discount_percent'][$size]) ? (float)$saved['discount_percent'][$size] : $default_pct;
                $calculated_sale=($regular_value>0 && $discount_value>0) ? round($regular_value*(1-$discount_value/100)) : 0;
              ?>
                <td><input class="avlux-regular-price" data-size="<?php echo esc_attr($size); ?>" type="number" min="0" step="1" name="catalog[<?php echo esc_attr($id); ?>][prices][<?php echo esc_attr($size); ?>]" value="<?php echo esc_attr($regular_value ?: ''); ?>" placeholder="Regular"></td>
                <td><input class="avlux-discount-percent" data-size="<?php echo esc_attr($size); ?>" type="number" min="0" max="99.99" step="0.01" name="catalog[<?php echo esc_attr($id); ?>][discount_percent][<?php echo esc_attr($size); ?>]" value="<?php echo esc_attr($discount_value ?: '0'); ?>" placeholder="0"></td>
                <td><input class="avlux-sale-price" data-size="<?php echo esc_attr($size); ?>" type="number" min="0" step="1" name="catalog[<?php echo esc_attr($id); ?>][sale_prices][<?php echo esc_attr($size); ?>]" value="<?php echo esc_attr($calculated_sale ?: ''); ?>" placeholder="Auto-calculated" readonly></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody></table>
        <p class="avlux-admin-pagination">
          <?php if($active_page>1): ?><a class="button" href="<?php echo esc_url(add_query_arg(array('page'=>'avlux-products','avlux_group'=>$active_group,'avlux_page'=>$active_page-1),admin_url('admin.php'))); ?>">← Previous</a><?php endif; ?>
          <strong>Showing <?php echo (($active_page-1)*$per_page)+1; ?>–<?php echo min($active_page*$per_page,$total_items); ?> of <?php echo $total_items; ?></strong>
          <?php if($active_page<$total_pages): ?><a class="button" href="<?php echo esc_url(add_query_arg(array('page'=>'avlux-products','avlux_group'=>$active_group,'avlux_page'=>$active_page+1),admin_url('admin.php'))); ?>">Next →</a><?php endif; ?>
        </p>
        <?php submit_button('Save this page of products'); ?>
      </form>
      <p><em>Tip: Use square or portrait product photos with a clean background for the best catalogue appearance.</em></p>
    </div>
    <script>
    jQuery(function($){
      function recalc(row,size){
        var regular=parseFloat(row.find('.avlux-regular-price[data-size="'+size+'"]').val())||0;
        var pct=parseFloat(row.find('.avlux-discount-percent[data-size="'+size+'"]').val())||0;
        pct=Math.min(99.99,Math.max(0,pct));
        row.find('.avlux-discount-percent[data-size="'+size+'"]').val(pct);
        var sale=(regular>0 && pct>0)?Math.round(regular*(1-pct/100)): '';
        row.find('.avlux-sale-price[data-size="'+size+'"]').val(sale);
      }
      $(document).on('input change','.avlux-regular-price,.avlux-discount-percent',function(){
        var field=$(this),row=field.closest('tr'),size=field.data('size');recalc(row,size);
      });
      $(document).on('click','.avlux-pick-image',function(e){e.preventDefault();var button=$(this),field=button.siblings('.avlux-image-url');var frame=wp.media({title:'Select product image',button:{text:'Use this image'},multiple:false,library:{type:'image'}});frame.on('select',function(){var attachment=frame.state().get('selection').first().toJSON();field.val(attachment.url);});frame.open();});
    });
    </script>
    <style>.avlux-admin-tabs,.avlux-admin-pagination{display:flex;align-items:center;gap:10px;margin:14px 0}.avlux-admin-pagination{justify-content:space-between;max-width:520px}.avlux-products-table{margin:12px 0 28px}.avlux-products-table td{vertical-align:middle}.avlux-products-table input[type=number]{width:92px}.avlux-products-table .avlux-sale-price{background:#f0f0f1;font-weight:600}.avlux-products-table small{color:#777}</style>
    <?php
}
function avlux_save_catalog() {
    if (!current_user_can('manage_options')) wp_die('You are not allowed to do this.');
    check_admin_referer('avlux_save_catalog','avlux_catalog_nonce');
    $posted = isset($_POST['catalog']) && is_array($_POST['catalog']) ? wp_unslash($_POST['catalog']) : array();
    $clean = get_option('avlux_catalog_overrides', array());
    if (!is_array($clean)) $clean = array();
    foreach ($posted as $id=>$item) {
        $id = sanitize_key($id); if (!$id || !is_array($item)) continue;
        $row = array();
        if (isset($item['image_url'])) $row['image_url'] = esc_url_raw($item['image_url']);
        foreach (array('name','brand','gender','family','strength','notes') as $field) {
            if (isset($item[$field])) $row[$field] = sanitize_text_field($item[$field]);
        }
        if (isset($item['description'])) $row['description'] = sanitize_textarea_field($item['description']);
        if (isset($item['visibility'])) $row['visibility'] = ($item['visibility'] === 'hidden') ? 'hidden' : 'active';
        if (isset($item['stock_status'])) $row['stock_status'] = ($item['stock_status'] === 'outofstock') ? 'outofstock' : 'instock';
        if (isset($item['price'])) $row['price'] = ($item['price'] === '') ? '' : max(0, (float)$item['price']);
        if (isset($item['sale_price'])) $row['sale_price'] = ($item['sale_price'] === '') ? '' : max(0, (float)$item['sale_price']);
        if (isset($item['prices']) && is_array($item['prices'])) {
            foreach (array('30ml','50ml','100ml') as $size) if (isset($item['prices'][$size])) $row['prices'][$size] = ($item['prices'][$size] === '') ? '' : max(0, (float)$item['prices'][$size]);
        }
        if (isset($item['discount_percent']) && is_array($item['discount_percent'])) {
            foreach (array('30ml','50ml','100ml') as $size) {
                if (!isset($item['discount_percent'][$size])) continue;
                $pct = min(99.99, max(0, (float)$item['discount_percent'][$size]));
                $row['discount_percent'][$size] = $pct;
                $regular = isset($row['prices'][$size]) ? (float)$row['prices'][$size] : 0;
                if ($regular <= 0 && isset($item['prices'][$size])) $regular = max(0, (float)$item['prices'][$size]);
                $row['sale_prices'][$size] = ($regular > 0 && $pct > 0) ? round($regular * (1 - $pct / 100)) : '';
            }
        }
        $existing = get_option('avlux_catalog_overrides', array());
        $clean[$id] = array_merge((isset($existing[$id]) && is_array($existing[$id])) ? $existing[$id] : array(), $row);
    }
    update_option('avlux_catalog_overrides', $clean, false);
    $return_group = (isset($_POST['avlux_group']) && $_POST['avlux_group']==='blends') ? 'blends' : 'perfumes';
    $return_page = max(1, absint($_POST['avlux_page'] ?? 1));
    wp_safe_redirect(add_query_arg(array('page'=>'avlux-products','avlux_group'=>$return_group,'avlux_page'=>$return_page,'updated'=>'1'), admin_url('admin.php'))); exit;
}
add_action('admin_post_avlux_save_catalog','avlux_save_catalog');

function avlux_create_page_once($title, $slug, $content) {
    $existing = get_page_by_path($slug);
    if ($existing instanceof WP_Post) return (int)$existing->ID;
    $id = wp_insert_post(array('post_title'=>$title, 'post_name'=>$slug, 'post_content'=>$content, 'post_status'=>'publish', 'post_type'=>'page'), true);
    return is_wp_error($id) ? 0 : (int)$id;
}
function avlux_activate() {
    $home_id = avlux_create_page_once('Aroma Valle\' Home', 'aroma-valle-home', '[aroma_valle_luxury_home]');
    $shop_id = 0;
    if (class_exists('WooCommerce')) {
        $shop_id = (int)get_option('woocommerce_shop_page_id');
        if (!$shop_id || !get_post($shop_id)) {
            $shop_id = avlux_create_page_once('Shop', 'shop', '[products limit="12" columns="3" paginate="true"]');
            if ($shop_id) update_option('woocommerce_shop_page_id', $shop_id);
        }
    } else {
        $shop_id = avlux_create_page_once('Shop', 'shop', '[aroma_valle_shop_intro]');
    }
    $about_id = avlux_create_page_once('Our Story', 'our-story', '[aroma_valle_about]');
    $contact_id = avlux_create_page_once('Contact', 'contact', '[aroma_valle_contact]');
    $collections_id = avlux_create_page_once("Aroma Valle' Collections", 'collections', '[aroma_valle_collections]');
    $catalog_id = avlux_create_page_once("Shop Fragrances", 'fragrance-collection', '[aroma_valle_catalog]');
    $o = avlux_options(); $o['shop_page'] = $shop_id; $o['about_page'] = $about_id; $o['contact_page'] = $contact_id; $o['collections_page'] = $collections_id; $o['catalog_page'] = $catalog_id; update_option('avlux_options', $o);
    if ($home_id && (get_option('show_on_front') !== 'page' || !get_option('page_on_front'))) { update_option('show_on_front','page'); update_option('page_on_front',$home_id); }
}
register_activation_hook(__FILE__, 'avlux_activate');
function avlux_ensure_collections_page() {
    $o = avlux_options();
    $changed = false;
    if (empty($o['collections_page']) || !get_post((int)$o['collections_page'])) {
        $id = avlux_create_page_once("Aroma Valle' Collections", 'collections', '[aroma_valle_collections]');
        if ($id) { $o['collections_page'] = $id; $changed = true; }
    }
    if (empty($o['catalog_page']) || !get_post((int)$o['catalog_page'])) {
        $id = avlux_create_page_once('Shop Fragrances', 'fragrance-collection', '[aroma_valle_catalog]');
        if ($id) { $o['catalog_page'] = $id; $changed = true; }
    }
    if ($changed) update_option('avlux_options', $o);
}
add_action('init', 'avlux_ensure_collections_page', 30);


function avlux_enqueue_assets() {
    wp_register_style('avlux-storefront', AVLUX_URL.'assets/storefront.css', array(), AVLUX_VERSION);
    wp_enqueue_style('avlux-storefront');
}
add_action('wp_enqueue_scripts', 'avlux_enqueue_assets');

function avlux_url($page, $fallback) {
    $o = avlux_options();
    if ($page === 'shop' && !empty($o['catalog_page'])) {
        $catalog_id = absint($o['catalog_page']);
        if (get_post($catalog_id)) return get_permalink($catalog_id);
    }
    $id = absint($o[$page.'_page'] ?? 0);
    return $id ? get_permalink($id) : home_url($fallback);
}
function avlux_standalone_header($active = 'shop') {
    $o = avlux_options(); $c = avlux_content();
    ob_start(); ?>
    <div class="avlux-announcement">A world of fragrance, delivered to your door · Nationwide delivery across Pakistan</div>
    <header class="avlux-standalone-header avlux-global-header">
      <a class="avlux-logo avlux-logo-image" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(AVLUX_URL . 'assets/aroma-valle-logo.png'); ?>" alt="Aroma Valle' — Signature Scents"></a>
      <nav aria-label="Main navigation">
        <a href="<?php echo esc_url(home_url('/')); ?>" <?php echo $active==='home'?'aria-current="page"':''; ?>>Home</a>
        <a href="<?php echo esc_url(avlux_url('shop','/shop/')); ?>" <?php echo $active==='shop'?'aria-current="page"':''; ?>>Shop</a>
        <a href="<?php echo esc_url(avlux_url('collections','/collections/')); ?>" <?php echo $active==='collections'?'aria-current="page"':''; ?>>Collections</a>
        <a href="<?php echo esc_url(home_url('/').'#avlux-story'); ?>">Our Philosophy</a>
        <a href="<?php echo esc_url(avlux_url('about','/our-story/')); ?>" <?php echo $active==='about'?'aria-current="page"':''; ?>>About</a>
        <a href="<?php echo esc_url(avlux_url('contact','/contact/')); ?>" <?php echo $active==='contact'?'aria-current="page"':''; ?>>Contact</a>
      </nav>
      <a class="avlux-header-basket" href="<?php echo esc_url(home_url('/checkout/')); ?>" aria-label="Open shopping cart">♧ <span>Cart</span> <b id="avlux-v16-header-count">0</b></a>
      <a class="avlux-header-cta" href="<?php echo esc_url(avlux_url('shop','/shop/')); ?>">Shop now ↗</a>
    </header>
    <?php return ob_get_clean();
}
function avlux_standalone_footer() {
    $o = avlux_options();
    ob_start(); ?>
    <footer class="avlux-standalone-footer avlux-global-footer">
      <a class="avlux-logo avlux-logo-image" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(AVLUX_URL . 'assets/aroma-valle-logo.png'); ?>" alt="Aroma Valle' — Signature Scents"></a>
      <p>Leave a lasting impression.</p>
      <div><a href="<?php echo esc_url(avlux_url('shop','/shop/')); ?>">Shop fragrances</a><a href="<?php echo esc_url(avlux_url('collections','/collections/')); ?>">Collections</a><a href="<?php echo esc_url(avlux_url('about','/our-story/')); ?>">Our story</a><a href="<?php echo esc_url(avlux_url('contact','/contact/')); ?>">Contact</a></div>
      <small>© <?php echo esc_html(wp_date('Y')); ?> Aroma Valle'. All rights reserved.</small>
    </footer>
    <?php return ob_get_clean();
}

function avlux_whatsapp_url($message='Hello Aroma Valle\', I would like to know more about your fragrances.') { $o=avlux_options(); return 'https://wa.me/'.preg_replace('/\D/','',$o['whatsapp']).'?text='.rawurlencode($message); }
function avlux_catalog_data() {
    static $data = null;
    if ($data === null) {
        $file = AVLUX_PATH . 'assets/catalog.json';
        $raw = file_exists($file) ? file_get_contents($file) : '';
        $data = $raw ? json_decode($raw, true) : array('perfumes'=>array(),'blends'=>array());
        if (!is_array($data)) $data = array('perfumes'=>array(),'blends'=>array());
        $overrides = get_option('avlux_catalog_overrides', array());
        if (is_array($overrides)) {
            foreach (array('perfumes','blends') as $group) {
                foreach (($data[$group] ?? array()) as $i => $item) {
                    $id = $item['id'] ?? '';
                    if ($id && isset($overrides[$id]) && is_array($overrides[$id])) {
                        $ov = $overrides[$id];
                        foreach (array('name','brand','gender','family','notes','strength','image_url','description','visibility','stock_status') as $key) {
                            if (isset($ov[$key]) && $ov[$key] !== '') $item[$key] = $ov[$key];
                        }
                        if (array_key_exists('price', $ov)) $item['price'] = $ov['price'];
                        if (array_key_exists('sale_price', $ov)) $item['sale_price'] = $ov['sale_price'];
                        if (isset($ov['prices']) && is_array($ov['prices'])) {
                            $item['prices'] = array_merge($item['prices'] ?? array(), $ov['prices']);
                        }
                        if (isset($ov['sale_prices']) && is_array($ov['sale_prices'])) {
                            $item['sale_prices'] = array_merge($item['sale_prices'] ?? array(), $ov['sale_prices']);
                        }
                        if (isset($ov['discount_percent']) && is_array($ov['discount_percent'])) {
                            $item['discount_percent'] = array_merge($item['discount_percent'] ?? array(), $ov['discount_percent']);
                        }
                        $data[$group][$i] = $item;
                    }
                }
            }
        }
        // v2.4 bundled product-specific imagery for all preloaded perfumes.
        foreach (array('perfumes','blends') as $image_group) {
            foreach (($data[$image_group] ?? array()) as $image_index => $image_item) {
                if (empty($image_item['image_url']) && !empty($image_item['id'])) {
                    $image_file = sanitize_file_name($image_item['id']) . '.jpg';
                    if (file_exists(AVLUX_PATH . 'assets/products/' . $image_file)) {
                        $image_item['image_url'] = AVLUX_URL . 'assets/products/' . $image_file;
                        $data[$image_group][$image_index] = $image_item;
                    }
                }
            }
        }
        $custom_products=get_option('avlux_custom_products',array());
        if(is_array($custom_products)) {
            foreach($custom_products as $custom) {
                if(!is_array($custom)||empty($custom['id'])) continue;
                $group=(($custom['kind']??'')==='blend')?'blends':'perfumes';
                $id=$custom['id'];
                $overrides=get_option('avlux_catalog_overrides',array());
                if(isset($overrides[$id]) && is_array($overrides[$id])) {
                    $ov=$overrides[$id];
                    foreach(array('name','brand','gender','family','notes','strength','image_url','description','visibility','stock_status') as $key) if(isset($ov[$key]) && $ov[$key]!=='') $custom[$key]=$ov[$key];
                    if(isset($ov['prices']) && is_array($ov['prices'])) $custom['prices']=array_merge($custom['prices']??array(),$ov['prices']);
                    if(isset($ov['sale_prices']) && is_array($ov['sale_prices'])) $custom['sale_prices']=array_merge($custom['sale_prices']??array(),$ov['sale_prices']);
                }
                $data[$group][]=$custom;
            }
        }
    }
    return $data;
}
function avlux_public_catalog_data() {
    $data = avlux_catalog_data();
    foreach (array('perfumes','blends') as $group) {
        $data[$group] = array_values(array_filter($data[$group] ?? array(), function($product) {
            return ($product['visibility'] ?? 'active') !== 'hidden';
        }));
    }
    return $data;
}
function avlux_catalog_shortcode($atts = array()) {
    $atts = shortcode_atts(array('limit'=>0, 'type'=>'all', 'gender'=>'all', 'kind'=>'all', 'standalone'=>'auto'), $atts, 'aroma_valle_catalog');
    $standalone = (string)$atts['standalone'] === '1';
    if ((string)$atts['standalone'] === 'auto' && is_page()) {
        $page_content = (string)get_post_field('post_content', get_queried_object_id());
        $standalone = !has_shortcode($page_content, 'aroma_valle_luxury_home') && !has_shortcode($page_content, 'av3_aroma_valle_home') && !has_shortcode($page_content, 'aroma_valle_collections');
    }
    $data = avlux_public_catalog_data(); $items = array();
    if ($atts['type'] !== 'blends') $items = array_merge($items, $data['perfumes'] ?? array());
    if ($atts['type'] !== 'perfumes') $items = array_merge($items, $data['blends'] ?? array());
    $search = isset($_GET['avlux_q']) ? sanitize_text_field(wp_unslash($_GET['avlux_q'])) : '';
    $gender = isset($_GET['avlux_gender']) ? sanitize_text_field(wp_unslash($_GET['avlux_gender'])) : sanitize_text_field($atts['gender']);
    $kind = isset($_GET['avlux_kind']) ? sanitize_text_field(wp_unslash($_GET['avlux_kind'])) : sanitize_text_field($atts['kind']);
    $filtered = array();
    foreach ($items as $item) {
        if ($kind && $kind !== 'all' && $item['kind'] !== $kind) continue;
        if ($gender && $gender !== 'all' && strcasecmp($item['gender'], $gender) !== 0) continue;
        $haystack = strtolower(implode(' ', array($item['name'],$item['brand'] ?? '',$item['family'] ?? '',$item['notes'] ?? '',implode(' ', $item['perfumes'] ?? array()))));
        if ($search && strpos($haystack, strtolower($search)) === false) continue;
        $filtered[] = $item;
    }
    if (absint($atts['limit']) > 0) $filtered = array_slice($filtered, 0, absint($atts['limit']));
    $o = avlux_options(); $image = esc_url($o['hero_image']);
    ob_start(); ?>
    <?php if ($standalone): ?><div class="avlux-store-page avlux-store-catalog-page"><?php echo avlux_standalone_header('shop'); ?><main class="avlux-catalog-page-shell"><div class="avlux-catalog-page-intro"><span class="avlux-eyebrow">THE AROMA VALLE' COLLECTION</span><h1>Find your <em>signature scent.</em></h1><p>Explore the fragrance wardrobe by collection, mood and character.</p></div><?php endif; ?>
    <div class="avlux-catalog <?php echo $standalone ? 'avlux-catalog-standalone' : ''; ?>">
      <?php if (!$standalone): ?><div class="avlux-catalog-head"><span class="avlux-eyebrow">THE AROMA VALLE' COLLECTION</span><h2>Find your <em>signature scent.</em></h2><p>Explore our fragrance shelf and signature blends, curated for every mood and moment.</p></div><?php endif; ?>
      <form class="avlux-catalog-filters" method="get" action="<?php echo esc_url(get_permalink()); ?>">
        <input type="search" name="avlux_q" value="<?php echo esc_attr($search); ?>" placeholder="Search fragrance, notes or brand…" aria-label="Search fragrances">
        <select name="avlux_gender" aria-label="Filter by gender"><option value="all">All collections</option><?php foreach (array('Male','Female','Unisex') as $g): ?><option value="<?php echo esc_attr($g); ?>" <?php selected($gender,$g); ?>><?php echo esc_html($g); ?></option><?php endforeach; ?></select>
        <select name="avlux_kind" aria-label="Filter by product type"><option value="all">All products</option><option value="perfume" <?php selected($kind,'perfume'); ?>>Individual perfumes</option><option value="blend" <?php selected($kind,'blend'); ?>>Signature blends</option></select>
        <button type="submit">Filter collection</button><a href="<?php echo esc_url(remove_query_arg(array('avlux_q','avlux_gender','avlux_kind'))); ?>">Reset</a>
      </form>
      <div class="avlux-catalog-count">Showing <?php echo count($filtered); ?> fragrance<?php echo count($filtered)===1?'':'s'; ?> <span>· Prices in PKR</span></div>
      <div class="avlux-product-grid">
      <?php foreach ($filtered as $item): $is_blend = $item['kind']==='blend'; $message = 'Hello Aroma Valle\', I would like to order/enquire about '.$item['name'].'.'; $wa = avlux_whatsapp_url($message); ?>
        <article class="avlux-product-card">
          <div class="avlux-product-image"><img src="<?php echo esc_url(!empty($item['image_url']) ? $item['image_url'] : $image); ?>" alt="<?php echo esc_attr($item['name']); ?> fragrance" loading="lazy"><span><?php echo $is_blend ? 'SIGNATURE BLEND' : 'FRAGRANCE'; ?></span></div>
          <div class="avlux-product-info"><div class="avlux-product-meta"><?php echo esc_html($item['gender']); ?><?php if (!empty($item['strength'])) echo ' · '.esc_html($item['strength']); ?></div>
            <h3><?php echo esc_html($item['name']); ?></h3>
            <?php if (!$is_blend && !empty($item['brand'])): ?><p class="avlux-product-brand">Inspired by <?php echo esc_html($item['brand']); ?></p><?php endif; ?>
            <p class="avlux-product-notes"><?php echo esc_html($item['description'] ?? ($is_blend ? ($item['notes'] ?? '') : ($item['family'] ?? ''))); ?></p>
            <?php if ($is_blend): ?><div class="avlux-blend-prices"><?php foreach (array('30ml','50ml','100ml') as $size): $price=$item['prices'][$size] ?? null; if ($price): ?><span><small><?php echo esc_html($size); ?></small><b>Rs. <?php echo esc_html(number_format_i18n($price)); ?></b></span><?php endif; endforeach; ?></div><?php else: ?><?php if (!empty($item['price'])): ?><div class="avlux-price-request">Rs. <?php echo esc_html(number_format_i18n((float)$item['price'])); ?></div><?php else: ?><div class="avlux-price-request">Price on request</div><?php endif; ?><?php endif; ?>
            <a class="avlux-product-cta" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener">Enquire on WhatsApp <span>↗</span></a>
          </div>
        </article>
      <?php endforeach; ?>
      </div>
      <?php if (!$filtered): ?><div class="avlux-no-results">No fragrances matched your search. Try another name or reset the filters.</div><?php endif; ?>
      <?php if (!$standalone): ?><p class="avlux-catalog-note">Individual perfume selling prices were not provided in the workbook, so these are shown as “Price on request”. Signature blend prices are taken from the workbook’s Final Rate 30ML, 50ML and 100ML columns.</p><?php endif; ?>
    </div>
    <?php if ($standalone): ?></main><?php echo avlux_standalone_footer(); ?></div><?php endif; ?>
    <?php return ob_get_clean();
}
add_shortcode('aroma_valle_catalog','avlux_catalog_shortcode');


function avlux_collection_shop_link($gender = 'all', $kind = 'all') {
    $url = avlux_url('shop', '/shop/');
    $args = array();
    if ($gender !== 'all') $args['avlux_gender'] = $gender;
    if ($kind !== 'all') $args['avlux_kind'] = $kind;
    return $args ? add_query_arg($args, $url) : $url;
}
function avlux_collections_shortcode($atts = array()) {
    $atts = shortcode_atts(array('mode'=>'full'), $atts, 'aroma_valle_collections');
    $mode = $atts['mode'];
    $c=avlux_content();
    $collections=array();
    foreach(array(1,2,3,4) as $n) {
      $collections[]=array('title'=>$c['collection_'.$n.'_title'],'eyebrow'=>$c['collection_'.$n.'_eyebrow'],'gender'=>array(1=>'Male',2=>'Female',3=>'Unisex',4=>'all')[$n],'kind'=>$n===4?'blend':'perfume','description'=>$c['collection_'.$n.'_text'],'image'=>$c['collection_'.$n.'_image'],'class'=>'avlux-collection-'.array(1=>'one',2=>'two',3=>'three',4=>'four')[$n]);
    }
    ob_start(); ?>
    <div class="avlux-collections-live">
      <?php if ($mode !== 'featured'): ?>
      <?php echo avlux_standalone_header('collections'); ?>
      <div class="avlux-collections-intro"><span class="avlux-eyebrow"><?php echo esc_html($c['collections_page_eyebrow']); ?></span><h1><?php echo esc_html($c['collections_page_title']); ?></h1><p><?php echo esc_html($c['collections_page_text']); ?></p></div>
      <?php endif; ?>
      <div class="avlux-collection-grid avlux-collection-grid-live">
      <?php foreach ($collections as $i=>$c): ?>
        <?php $fallback_images = array(1=>add_query_arg('ver', AVLUX_VERSION, AVLUX_URL.'assets/collections/for-him.jpg'),2=>add_query_arg('ver', AVLUX_VERSION, AVLUX_URL.'assets/collections/for-her.jpg'),3=>add_query_arg('ver', AVLUX_VERSION, AVLUX_URL.'assets/collections/unisex.jpg'),4=>add_query_arg('ver', AVLUX_VERSION, AVLUX_URL.'assets/collections/signature-blends.jpg')); $card_image = $fallback_images[$i+1]; // v2.6.2: force fresh text-free bundled collection photography. ?>
        <a class="avlux-collection <?php echo esc_attr($c['class']); ?>" style="background-image:linear-gradient(180deg,rgba(15,12,9,.02) 20%,rgba(15,12,9,.78) 100%),url('<?php echo esc_url($card_image); ?>');" href="<?php echo esc_url(avlux_collection_shop_link($c['gender'],$c['kind'])); ?>">
          <span><?php echo esc_html(sprintf('%02d / %s',$i+1,$c['eyebrow'])); ?></span><strong><?php echo esc_html($c['title']); ?></strong><i>Explore collection ↗</i>
        </a>
      <?php endforeach; ?>
      </div>
      <?php foreach ($collections as $c): ?>
        <section class="avlux-live-collection">
          <div class="avlux-live-collection-heading"><div><span class="avlux-eyebrow"><?php echo esc_html($c['eyebrow']); ?></span><h2><?php echo esc_html($c['title']); ?></h2><p><?php echo esc_html($c['description']); ?></p></div><a href="<?php echo esc_url(avlux_collection_shop_link($c['gender'],$c['kind'])); ?>">View all in collection ↗</a></div>
          <?php
            if ($c['kind'] === 'blend') echo do_shortcode('[aroma_valle_catalog limit="4" type="blends"]');
            else echo do_shortcode('[aroma_valle_catalog limit="4" type="perfumes" gender="'.esc_attr($c['gender']).'"]');
          ?>
        </section>
      <?php endforeach; ?>
      <?php if ($mode !== 'featured'): ?><footer class="avlux-standalone-footer"><a class="avlux-logo" href="<?php echo esc_url(home_url('/')); ?>"><span class="avlux-mark">✧</span><span><?php echo esc_html(avlux_options()['brand']); ?><small>FRAGRANCE HOUSE</small></span></a><p>Leave a lasting impression.</p><div><a href="<?php echo esc_url(avlux_url('shop','/shop/')); ?>">Shop fragrances</a><a href="<?php echo esc_url(avlux_url('about','/our-story/')); ?>">Our story</a><a href="<?php echo esc_url(avlux_url('contact','/contact/')); ?>">Contact</a></div><small>© <?php echo esc_html(wp_date('Y')); ?> Aroma Valle'. All rights reserved.</small></footer><?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('aroma_valle_collections','avlux_collections_shortcode');

function avlux_home_shortcode() {
    $o=avlux_options(); $c=avlux_content(); $shop=avlux_url('shop','/shop/'); $about=avlux_url('about','/our-story/'); $contact=avlux_url('contact','/contact/');
    $hero=esc_url($o['hero_image']); $gold=esc_attr($o['gold']);
    $headline=nl2br(esc_html($o['headline']));
    ob_start(); ?>
    <div class="avlux" style="--avlux-gold:<?php echo $gold; ?>;--avlux-hero:url('<?php echo $hero; ?>')">
      <div class="avlux-announcement"><?php echo esc_html($o['announcement']); ?></div>
      <header class="avlux-header"><a class="avlux-logo avlux-logo-image" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(AVLUX_URL . 'assets/aroma-valle-logo.png'); ?>" alt="Aroma Valle' — Signature Scents"></a><nav class="avlux-nav"><a href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html($c['nav_home']); ?></a><a href="<?php echo esc_url($shop); ?>"><?php echo esc_html($c['nav_shop']); ?></a><a href="<?php echo esc_url(avlux_url('collections', '/collections/')); ?>"><?php echo esc_html($c['nav_collections']); ?></a><a href="#avlux-story"><?php echo esc_html($c['nav_philosophy']); ?></a><a href="<?php echo esc_url($about); ?>"><?php echo esc_html($c['nav_about']); ?></a><a href="<?php echo esc_url($contact); ?>"><?php echo esc_html($c['nav_contact']); ?></a></nav><a class="avlux-header-cta" href="<?php echo esc_url($shop); ?>"><?php echo esc_html($c['header_cta']); ?> <span>↗</span></a></header>
      <section class="avlux-hero"><div class="avlux-hero-copy"><span class="avlux-eyebrow"><?php echo esc_html($o['eyebrow']); ?></span><h1><?php echo $headline; ?></h1><p><?php echo esc_html($o['subheading']); ?></p><div class="avlux-actions"><a class="avlux-btn avlux-btn-gold" href="<?php echo esc_url($shop); ?>"><?php echo esc_html($c['hero_button']); ?> <span>→</span></a><a class="avlux-text-link" href="<?php echo esc_url(avlux_whatsapp_url()); ?>" target="_blank" rel="noopener">Speak to our team ↗</a></div><div class="avlux-hero-note"><span>✦</span> <?php echo esc_html($c['hero_note']); ?></div></div><div class="avlux-hero-image" role="img" aria-label="Luxury perfume bottle with warm golden light"></div><div class="avlux-hero-index">01 <i></i> 04</div></section>
      <section class="avlux-promises"><div><b>✧</b><span><strong><?php echo esc_html($c['promise_1_title']); ?></strong><small><?php echo esc_html($c['promise_1_text']); ?></small></span></div><div><b>◇</b><span><strong><?php echo esc_html($c['promise_2_title']); ?></strong><small><?php echo esc_html($c['promise_2_text']); ?></small></span></div><div><b>♡</b><span><strong><?php echo esc_html($c['promise_3_title']); ?></strong><small><?php echo esc_html($c['promise_3_text']); ?></small></span></div><div><b>✦</b><span><strong><?php echo esc_html($c['promise_4_title']); ?></strong><small><?php echo esc_html($c['promise_4_text']); ?></small></span></div></section>
      <section class="avlux-section avlux-collections" id="avlux-collections"><div class="avlux-section-heading"><div><span class="avlux-eyebrow"><?php echo esc_html($c['home_collections_eyebrow']); ?></span><h2><?php echo esc_html($c['home_collections_title']); ?></h2></div><p><?php echo esc_html($c['home_collections_text']); ?></p></div><?php echo do_shortcode('[aroma_valle_collections mode="featured"]'); ?></section>
      <section class="avlux-section avlux-bestsellers"><div class="avlux-center-heading"><span class="avlux-eyebrow"><?php echo esc_html($c['home_bestsellers_eyebrow']); ?></span><h2><?php echo esc_html($c['home_bestsellers_title']); ?></h2><p><?php echo esc_html($c['home_bestsellers_text']); ?></p></div><?php echo do_shortcode('[aroma_valle_catalog limit="4" type="blends"]'); ?></section>
      <section class="avlux-story" id="avlux-story"><div class="avlux-story-image" role="img" aria-label="Our fragrance story" style="<?php echo $c['story_image'] ? 'background-image:url('.esc_url($c['story_image']).')' : ''; ?>"></div><div class="avlux-story-copy"><span class="avlux-eyebrow"><?php echo esc_html($c['story_eyebrow']); ?></span><h2><?php echo esc_html($c['story_title']); ?></h2><p><?php echo esc_html($c['story_text_1']); ?></p><p><?php echo esc_html($c['story_text_2']); ?></p><a class="avlux-underlined" href="<?php echo esc_url($about); ?>">Discover our story <span>→</span></a></div></section>
      <section class="avlux-quote"><span>✧</span><blockquote><?php echo esc_html($c['quote_text']); ?></blockquote><small><?php echo esc_html($c['quote_credit']); ?></small></section>
      <section class="avlux-final-cta"><span class="avlux-eyebrow"><?php echo esc_html($c['cta_eyebrow']); ?></span><h2><?php echo esc_html($c['cta_title']); ?></h2><a class="avlux-btn avlux-btn-gold" href="<?php echo esc_url($shop); ?>">Shop the collection <span>→</span></a></section>
      <footer class="avlux-footer"><a class="avlux-logo avlux-logo-image" href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(AVLUX_URL . 'assets/aroma-valle-logo.png'); ?>" alt="Aroma Valle' — Signature Scents"></a><p><?php echo esc_html($c['footer_tagline']); ?></p><div><a href="<?php echo esc_url($shop); ?>">Shop</a><a href="<?php echo esc_url(avlux_url('collections', '/collections/')); ?>">Collections</a><a href="<?php echo esc_url($about); ?>">Our Story</a><a href="<?php echo esc_url($contact); ?>">Contact</a><a href="<?php echo esc_url(avlux_whatsapp_url()); ?>" target="_blank" rel="noopener">WhatsApp</a></div><small>© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($o['brand']); ?>. All rights reserved.</small></footer>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('aroma_valle_luxury_home','avlux_home_shortcode');
// Backward-compatible alias so an existing Aroma Valle homepage using the earlier shortcode keeps working.
add_shortcode('av3_aroma_valle_home','avlux_home_shortcode');

function avlux_about_shortcode() { $c=avlux_content(); ob_start(); ?><div class="avlux-store-page avlux-about-page"><?php echo avlux_standalone_header('about'); ?><div class="avlux-inner-page"><?php if(!empty($c['about_image'])): ?><img class="avlux-page-image" src="<?php echo esc_url($c['about_image']); ?>" alt="About Aroma Valle'"><?php endif; ?><span class="avlux-eyebrow"><?php echo esc_html($c['about_eyebrow']); ?></span><h1><?php echo esc_html($c['about_title']); ?></h1><p><?php echo esc_html($c['about_text_1']); ?></p><p><?php echo esc_html($c['about_text_2']); ?></p><a class="avlux-btn avlux-btn-dark" href="<?php echo esc_url(avlux_url('shop','/shop/')); ?>">Explore the collection →</a></div><?php echo avlux_standalone_footer(); ?></div><?php return ob_get_clean(); }
add_shortcode('aroma_valle_about','avlux_about_shortcode');
function avlux_contact_shortcode() { $c=avlux_content(); $email=avlux_v16_store_email(); $o=avlux_options(); $phone=preg_replace('/\D/','',$o['whatsapp']); $display_phone='+' . $phone; ob_start(); ?><div class="avlux-store-page avlux-contact-page"><?php echo avlux_standalone_header('contact'); ?><div class="avlux-inner-page"><?php if(!empty($c['contact_image'])): ?><img class="avlux-page-image" src="<?php echo esc_url($c['contact_image']); ?>" alt="Contact Aroma Valle'"><?php endif; ?><span class="avlux-eyebrow"><?php echo esc_html($c['contact_eyebrow']); ?></span><h1><?php echo esc_html($c['contact_title']); ?></h1><p><?php echo esc_html($c['contact_text']); ?></p><div class="avlux-contact-actions"><p><a class="avlux-btn avlux-btn-dark" href="mailto:<?php echo esc_attr($email); ?>">Email us: <?php echo esc_html($email); ?></a></p><p><a class="avlux-btn avlux-btn-dark" href="tel:<?php echo esc_attr('+'.$phone); ?>">Call us: <?php echo esc_html($display_phone); ?></a></p><p><a class="avlux-btn avlux-btn-gold" href="<?php echo esc_url(avlux_whatsapp_url('Hello Aroma Valle’, I would like to enquire about your fragrances.')); ?>" target="_blank" rel="noopener">Chat on WhatsApp ↗</a></p></div></div><?php echo avlux_standalone_footer(); ?></div><?php return ob_get_clean(); }
add_shortcode('aroma_valle_contact','avlux_contact_shortcode');
function avlux_shop_intro_shortcode() { return do_shortcode('[aroma_valle_catalog standalone="1"]'); }

function avlux_body_class($classes) {
    $content = is_page() ? (string)get_post_field('post_content', get_queried_object_id()) : '';
    $store_shortcodes = array('aroma_valle_luxury_home','av3_aroma_valle_home','aroma_valle_collections','aroma_valle_catalog','aroma_valle_shop_intro','aroma_valle_about','aroma_valle_contact','aroma_valle_checkout');
    $is_store_page = false;
    foreach ($store_shortcodes as $shortcode) {
        if ($content && has_shortcode($content, $shortcode)) { $is_store_page = true; break; }
    }
    if (function_exists('is_shop') && (is_shop() || (function_exists('is_product_taxonomy') && is_product_taxonomy()))) {
        $is_store_page = true; $classes[] = 'avlux-catalog-page-active';
    }
    if ($is_store_page) $classes[] = 'avlux-storefront-page-active';
    if ($content && (has_shortcode($content, 'aroma_valle_luxury_home') || has_shortcode($content, 'av3_aroma_valle_home'))) $classes[] = 'avlux-page-active';
    if ($content && has_shortcode($content, 'aroma_valle_collections')) $classes[] = 'avlux-collections-page-active';
    if ($content && has_shortcode($content, 'aroma_valle_checkout')) $classes[] = 'avlux-checkout-page-active';
    if ($content && (has_shortcode($content, 'aroma_valle_catalog') || has_shortcode($content, 'aroma_valle_shop_intro'))) $classes[] = 'avlux-catalog-page-active';
    return array_unique($classes);
}
add_filter('body_class','avlux_body_class');


/* Aroma Valle' v1.6.0: editable contact email and complete COD checkout */
function avlux_v16_store_email() {
    $email=get_option('avlux_order_email','iakali27701@gmail.com');
    return is_email($email) ? $email : 'iakali27701@gmail.com';
}
function avlux_v16_email_settings_page() {
    if (!current_user_can('manage_options')) return;
    if (isset($_GET['saved'])) echo '<div class="notice notice-success"><p>Store email saved.</p></div>';
    ?>
    <div class="wrap"><h1>Aroma Valle' — Contact & Order Email</h1>
    <p>New customer orders and contact enquiries will be sent to this email address.</p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <input type="hidden" name="action" value="avlux_save_store_email">
      <?php wp_nonce_field('avlux_save_store_email','avlux_email_nonce'); ?>
      <table class="form-table"><tr><th><label for="avlux_order_email">Store email address</label></th><td><input class="regular-text" type="email" id="avlux_order_email" name="store_email" value="<?php echo esc_attr(avlux_v16_store_email()); ?>" required></td></tr></table>
      <?php submit_button('Save email address'); ?>
    </form></div>
    <?php
}
function avlux_v16_save_store_email() {
    if (!current_user_can('manage_options')) wp_die('Not allowed.');
    check_admin_referer('avlux_save_store_email','avlux_email_nonce');
    $email=sanitize_email(wp_unslash($_POST['store_email'] ?? ''));
    if (!is_email($email)) wp_die('Please enter a valid email address.');
    update_option('avlux_order_email',$email,false);
    wp_safe_redirect(add_query_arg(array('page'=>'avlux-email-settings','saved'=>'1'),admin_url('admin.php'))); exit;
}
add_action('admin_post_avlux_save_store_email','avlux_v16_save_store_email');
function avlux_v16_add_email_menu() {
    add_submenu_page('avlux-manager',"Contact & Order Email",'Contact & Order Email','manage_options','avlux-email-settings','avlux_v16_email_settings_page');
}
add_action('admin_menu','avlux_v16_add_email_menu',99);

function avlux_v16_register_order_type() {
    register_post_type('avlux_order',array(
        'labels'=>array('name'=>"Aroma Valle' Orders",'singular_name'=>'Customer Order','menu_name'=>'Customer Orders'),
        'public'=>false,'show_ui'=>true,'show_in_menu'=>'avlux-manager','capability_type'=>'post','map_meta_cap'=>true,
        'supports'=>array('title'),'menu_icon'=>'dashicons-cart'
    ));
}
add_action('init','avlux_v16_register_order_type',5);
function avlux_v16_order_columns($cols) { return array('cb'=>$cols['cb']??'','title'=>'Order reference','av_customer'=>'Customer','av_total'=>'Total (PKR)','date'=>'Date'); }
add_filter('manage_avlux_order_posts_columns','avlux_v16_order_columns');
function avlux_v16_order_column_content($col,$post_id) {
    $d=get_post_meta($post_id,'_avlux_v16_order',true); if (!is_array($d)) return;
    if ($col==='av_customer') echo esc_html(($d['name']??'').' · '.($d['phone']??''));
    if ($col==='av_total') echo 'Rs. '.esc_html(number_format_i18n((float)($d['total']??0)));
}
add_action('manage_avlux_order_posts_custom_column','avlux_v16_order_column_content',10,2);
function avlux_v16_order_box() { add_meta_box('avlux_v16_order','Customer & order details','avlux_v16_render_order_box','avlux_order','normal','high'); }
add_action('add_meta_boxes','avlux_v16_order_box');
function avlux_v16_render_order_box($post) {
    $d=get_post_meta($post->ID,'_avlux_v16_order',true); if (!is_array($d)) {echo '<p>No details.</p>';return;}
    echo '<p><b>Customer:</b> '.esc_html($d['name']).'</p><p><b>Phone:</b> '.esc_html($d['phone']).'</p><p><b>Email:</b> '.esc_html($d['email']).'</p><p><b>City:</b> '.esc_html($d['city']).'</p><p><b>Address:</b><br>'.nl2br(esc_html($d['address'])).'</p><p><b>Payment:</b> Cash on Delivery</p><hr><h3>Items</h3><ul>';
    foreach ($d['items'] as $it) echo '<li>'.esc_html($it['name'].' '.$it['size'].' × '.$it['qty']).' — Rs. '.esc_html(number_format_i18n($it['line_total'])).'</li>';
    echo '</ul><p><b>Subtotal:</b> Rs. '.esc_html(number_format_i18n($d['subtotal']??$d['total'])).'</p><p><b>Discount:</b> Rs. '.esc_html(number_format_i18n($d['discount']??0)).'</p><h3>Total after discount: Rs. '.esc_html(number_format_i18n($d['total'])).'</h3><p><b>Customer notes:</b><br>'.nl2br(esc_html($d['notes'])).'</p>';
}

function avlux_v16_place_order() {
    check_admin_referer('avlux_place_order','avlux_order_nonce');
    $name=sanitize_text_field(wp_unslash($_POST['customer_name']??''));
    $phone=sanitize_text_field(wp_unslash($_POST['customer_phone']??''));
    $email=sanitize_email(wp_unslash($_POST['customer_email']??''));
    $city=sanitize_text_field(wp_unslash($_POST['customer_city']??''));
    $address=sanitize_textarea_field(wp_unslash($_POST['customer_address']??''));
    $notes=sanitize_textarea_field(wp_unslash($_POST['customer_notes']??''));
    if (!$name||!$phone||!is_email($email)||!$city||!$address||empty($_POST['order_terms'])) wp_die('Please complete all required delivery details.');
    $cart=json_decode(wp_unslash($_POST['cart_payload']??''),true);
    if (!is_array($cart)||!$cart) wp_die('Your shopping bag is empty.');
    $catalog=avlux_public_catalog_data(); $products=array();
    foreach (array_merge($catalog['perfumes']??array(),$catalog['blends']??array()) as $p) $products[$p['id']]=$p;
    $lines=array(); $total=0; $subtotal=0; $discount_total=0;
    foreach ($cart as $row) {
        if (!is_array($row)) continue;
        $id=sanitize_key($row['id']??''); if (!$id||!isset($products[$id])) continue;
        $p=$products[$id]; if (($p['stock_status'] ?? 'instock') === 'outofstock') continue; $qty=max(1,min(10,absint($row['qty']??1))); $size='';
        $size=sanitize_text_field($row['size']??'');
        if (!in_array($size,array('30ml','50ml','100ml'),true)) continue;
        $regular=(float)($p['prices'][$size]??0);
        $sale=(float)($p['sale_prices'][$size]??0);
        if (($p['kind']??'')==='perfume' && $regular<=0 && $size==='30ml') {
            $regular=(float)($p['price']??0);
            $sale=(float)($p['sale_price']??0);
        }
        $unit=($sale>0 && $regular>0 && $sale<$regular)?$sale:$regular;
        // Recalculate using trusted server-side catalogue values; never accept browser prices.
        if ($unit<=0) continue;
        $line=$unit*$qty; $total+=$line; $subtotal+=$regular*$qty; $discount_total+=max(0,$regular-$unit)*$qty;
        $lines[]=array('name'=>sanitize_text_field($p['name']),'size'=>$size,'qty'=>$qty,'unit_price'=>$unit,'regular_price'=>$regular,'discount'=>max(0,$regular-$unit),'line_total'=>$line);
    }
    if (!$lines||$total<=0) wp_die('No valid priced products were found. Please refresh the shop and try again.');
    $ref='AV-'.strtoupper(wp_generate_password(8,false,false));
    $order=array('reference'=>$ref,'name'=>$name,'phone'=>$phone,'email'=>$email,'city'=>$city,'address'=>$address,'notes'=>$notes,'items'=>$lines,'subtotal'=>$subtotal,'discount'=>$discount_total,'total'=>$total,'payment'=>'Cash on Delivery');
    $post_id=wp_insert_post(array('post_type'=>'avlux_order','post_status'=>'publish','post_title'=>$ref),true);
    if (is_wp_error($post_id)||!$post_id) wp_die('Your order could not be saved. Please try again.');
    update_post_meta($post_id,'_avlux_v16_order',$order);
    $rows=''; foreach ($lines as $it) $rows.='<tr><td>'.esc_html($it['name'].' '.$it['size']).'</td><td>'.esc_html($it['qty']).'</td><td>Rs. '.esc_html(number_format_i18n($it['unit_price'])).'</td><td>Rs. '.esc_html(number_format_i18n($it['line_total'])).'</td></tr>';
    $body='<h2>New Aroma Valle\' COD order</h2><p><b>Order reference:</b> '.esc_html($ref).'</p><h3>Customer details</h3><p>'.esc_html($name).'<br>'.esc_html($phone).'<br>'.esc_html($email).'<br>'.esc_html($city).'<br>'.nl2br(esc_html($address)).'</p><h3>Order items</h3><table border="1" cellpadding="7" cellspacing="0"><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr>'.$rows.'</table><p>Subtotal: Rs. '.esc_html(number_format_i18n($subtotal)).'</p><p>Discount: Rs. '.esc_html(number_format_i18n($discount_total)).'</p><h3>Total payable after discount: Rs. '.esc_html(number_format_i18n($total)).'</h3><p>Payment method: Cash on Delivery</p><p>Notes: '.nl2br(esc_html($notes)).'</p>';
    $headers=array('Content-Type: text/html; charset=UTF-8','Reply-To: Aroma Valle\' <'.avlux_v16_store_email().'>');
    wp_mail(avlux_v16_store_email(),'New COD order '.$ref,$body,$headers);
    $customer_body='<h2>Thank you for your order!</h2><p>Dear '.esc_html($name).', your order from Aroma Valle\' has been received successfully.</p><p><b>Order reference:</b> '.esc_html($ref).'</p><h3>Order summary</h3><table border="1" cellpadding="7" cellspacing="0"><tr><th>Product</th><th>Qty</th><th>Amount</th></tr>'.$rows.'</table><p>Subtotal: Rs. '.esc_html(number_format_i18n($subtotal)).'</p><p>Discount: Rs. '.esc_html(number_format_i18n($discount_total)).'</p><p><b>Total payable after discount: Rs. '.esc_html(number_format_i18n($total)).'</b></p><p>Payment method: Cash on Delivery. Our team may contact you to confirm your order.</p><p>Thank you for choosing Aroma Valle\'.</p>'; 
    wp_mail($email,"Aroma Valle' order confirmation — ".$ref,$customer_body,$headers);
    $back=wp_get_referer(); if (!$back) $back=home_url('/');
    wp_safe_redirect(add_query_arg(array('avlux_order'=>'received','order_ref'=>$ref),remove_query_arg(array('avlux_order','order_ref'),$back))); exit;
}
add_action('admin_post_nopriv_avlux_place_order','avlux_v16_place_order');
add_action('admin_post_avlux_place_order','avlux_v16_place_order');


function avlux_v2613_ensure_checkout_page() {
    if (get_option('avlux_v2613_checkout_page')) return;
    $page=get_page_by_path('checkout');
    if (!$page) {
        $id=wp_insert_post(array('post_title'=>'Checkout','post_name'=>'checkout','post_status'=>'publish','post_type'=>'page','post_content'=>'[aroma_valle_checkout]'));
        if (!is_wp_error($id) && $id) update_option('avlux_v2613_checkout_page',absint($id),false);
    } else {
        if (!has_shortcode($page->post_content,'aroma_valle_checkout')) {
            wp_update_post(array('ID'=>$page->ID,'post_content'=>'[aroma_valle_checkout]'));
        }
        update_option('avlux_v2613_checkout_page',absint($page->ID),false);
    }
}
add_action('init','avlux_v2613_ensure_checkout_page',30);

function avlux_v2613_checkout_shortcode() {
    ob_start(); ?>
    <div class="avlux-store-page avlux-checkout-page"><?php echo avlux_standalone_header('shop'); ?>
    <section class="avlux-v16-checkout avlux-v2613-checkout">
      <div class="avlux-catalog-head"><span class="avlux-eyebrow">YOUR ORDER</span><h1>Your shopping <em>cart.</em></h1><p>Review your fragrances, discount and delivery details.</p></div>
      <div id="avlux-v2613-lines"></div>
      <div class="avlux-v2613-summary">
       <div><span>Subtotal</span><strong id="avlux-v2613-subtotal">Rs. 0</strong></div>
       <div><span>Discount</span><strong id="avlux-v2613-discount">− Rs. 0</strong></div>
       <div class="avlux-v2613-grand"><span>Total after discount</span><strong id="avlux-v2613-total">Rs. 0</strong></div>
      </div>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="avlux-v16-form" id="avlux-v2613-form">
       <input type="hidden" name="action" value="avlux_place_order"><?php wp_nonce_field('avlux_place_order','avlux_order_nonce'); ?><input type="hidden" name="cart_payload" id="avlux-v2613-payload">
       <h3>Delivery information</h3>
       <div class="avlux-v16-fields">
        <label>Full name *<input name="customer_name" autocomplete="name" required maxlength="120"></label>
        <label>Mobile number *<input name="customer_phone" type="tel" autocomplete="tel" required maxlength="30" placeholder="03XXXXXXXXX"></label>
        <label>Email address *<input name="customer_email" type="email" autocomplete="email" required maxlength="190"></label>
        <label>City *<input name="customer_city" autocomplete="address-level2" required maxlength="100"></label>
        <label class="wide">Complete delivery address *<textarea name="customer_address" autocomplete="street-address" required rows="3" maxlength="1000" placeholder="House, street, area and nearby landmark"></textarea></label>
        <label class="wide">Order notes (optional)<textarea name="customer_notes" rows="2" maxlength="1000"></textarea></label>
       </div>
       <div class="avlux-v16-cod"><strong>Payment method</strong><p>Cash on Delivery (COD) — pay in cash when your order arrives.</p></div>
       <label class="avlux-v16-terms"><input type="checkbox" name="order_terms" value="1" required> I confirm that my order and delivery details are correct.</label>
       <button type="submit" id="avlux-v2613-submit" disabled>Place Cash on Delivery order →</button>
       <small>Your order will be recorded in the store and an email notification will be sent to the Aroma Valle' team.</small>
      </form>
    </section>
    <?php echo avlux_standalone_footer(); ?></div>
    <script>
    (function(){
      const key='avlux_v16_cart',shopUrl=<?php echo wp_json_encode(esc_url(avlux_url('shop','/shop/'))); ?>,money=n=>'Rs. '+Number(n||0).toLocaleString('en-PK',{maximumFractionDigits:0});
      const esc=s=>String(s||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
      let cart=[];try{cart=JSON.parse(localStorage.getItem(key)||'[]');if(!Array.isArray(cart))cart=[];}catch(e){cart=[];}
      const lines=document.getElementById('avlux-v2613-lines'),payload=document.getElementById('avlux-v2613-payload'),submit=document.getElementById('avlux-v2613-submit');
      function render(){
        const subtotal=cart.reduce((a,x)=>a+Number(x.regular||x.price)*x.qty,0),total=cart.reduce((a,x)=>a+Number(x.price)*x.qty,0),discount=Math.max(0,subtotal-total);
        lines.innerHTML=cart.length?cart.map((x,i)=>'<div class="avlux-v2613-item"><img src="'+esc(x.image)+'" alt=""><div class="avlux-v2613-item-info"><b>'+esc(x.name)+'</b><small>'+esc(x.size)+' · Qty '+x.qty+'</small><small>Unit price: '+money(x.price)+'</small></div><strong>'+money(x.price*x.qty)+'</strong><button type="button" data-remove="'+i+'" aria-label="Remove item">Remove</button></div>').join(''):'<div class="avlux-v2613-empty"><p>Your cart is empty.</p><a class="avlux-v2613-continue" href="'+esc(shopUrl)+'">← Continue shopping</a></div>';
        document.getElementById('avlux-v2613-subtotal').textContent=money(subtotal);
        document.getElementById('avlux-v2613-discount').textContent='− '+money(discount);
        document.getElementById('avlux-v2613-total').textContent=money(total);
        payload.value=JSON.stringify(cart.map(x=>({id:x.id,size:x.size,qty:x.qty})));
        document.querySelectorAll('#avlux-v16-header-count').forEach(el=>el.textContent=cart.reduce((a,x)=>a+x.qty,0));
        submit.disabled=!cart.length;
        try{localStorage.setItem(key,JSON.stringify(cart));}catch(e){}
      }
      document.addEventListener('click',e=>{const b=e.target.closest('[data-remove]');if(!b)return;cart.splice(Number(b.dataset.remove),1);render();});
      render();
    })();
    </script>
    <?php return ob_get_clean();
}
add_shortcode('aroma_valle_checkout','avlux_v2613_checkout_shortcode');

// Render a proper priced catalogue and COD cart instead of sending shoppers to WhatsApp.
function avlux_v16_catalog_shortcode($atts=array()) {
    $atts=shortcode_atts(array('limit'=>0,'type'=>'all','gender'=>'all','kind'=>'all'),$atts,'aroma_valle_catalog');
    $d=avlux_public_catalog_data(); $items=array();
    if ($atts['type']!=='blends') $items=array_merge($items,$d['perfumes']??array());
    if ($atts['type']!=='perfumes') $items=array_merge($items,$d['blends']??array());
    $q=sanitize_text_field(wp_unslash($_GET['avlux_q']??''));
    $gender=sanitize_text_field(wp_unslash($_GET['avlux_gender']??$atts['gender']));
    $kind=sanitize_text_field(wp_unslash($_GET['avlux_kind']??$atts['kind']));
    $filtered=array();
    foreach ($items as $p) {
        if ($kind!=='all'&&$kind!==''&&($p['kind']??'')!==$kind) continue;
        if ($gender!=='all'&&$gender!==''&&strcasecmp($p['gender']??'',$gender)!==0) continue;
        $hay=strtolower(implode(' ',array($p['name'],$p['brand']??'',$p['family']??'',$p['notes']??'')));
        if ($q&&strpos($hay,strtolower($q))===false) continue;
        $filtered[]=$p;
    }
    $full=absint($atts['limit'])===0; if (!$full) $filtered=array_slice($filtered,0,absint($atts['limit']));
    $o=avlux_options(); $fallback=esc_url($o['hero_image']);
    ob_start(); ?>
    <?php if ($full): ?><div class="avlux-store-page avlux-store-catalog-page"><?php echo avlux_standalone_header('shop'); ?><?php endif; ?>
    <div class="avlux-catalog avlux-v16-catalog">
    <?php if ($full): ?>
      <?php $site_content=avlux_content(); ?><div class="avlux-catalog-head"><span class="avlux-eyebrow"><?php echo esc_html($site_content['shop_eyebrow']); ?></span><h2><?php echo esc_html($site_content['shop_title']); ?></h2><p><?php echo esc_html($site_content['shop_text']); ?></p></div>
      <form class="avlux-catalog-filters" method="get" action="<?php echo esc_url(get_permalink()); ?>"><input type="search" name="avlux_q" value="<?php echo esc_attr($q); ?>" placeholder="Search fragrance or brand..."><select name="avlux_gender"><option value="all">All collections</option><?php foreach(array('Male'=>'For Him','Female'=>'For Her','Unisex'=>'Unisex') as $g=>$label): ?><option value="<?php echo esc_attr($g); ?>" <?php selected($gender,$g); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select><select name="avlux_kind"><option value="all">All products</option><option value="perfume" <?php selected($kind,'perfume'); ?>>Perfumes</option><option value="blend" <?php selected($kind,'blend'); ?>>Signature blends</option></select><button type="submit">Apply filters</button><a href="<?php echo esc_url(remove_query_arg(array('avlux_q','avlux_gender','avlux_kind'))); ?>">Reset</a></form>
      <div class="avlux-catalog-count">Showing <?php echo count($filtered); ?> products <span>· Prices in PKR</span></div>
      <div class="avlux-v16-cartbar"><strong>Shopping bag <span id="avlux-v16-count">0</span></strong><a class="avlux-v16-checkout-link" href="<?php echo esc_url(home_url('/checkout/')); ?>">View cart & checkout →</a></div>
    <?php endif; ?>
    <div class="avlux-product-grid">
    <?php foreach ($filtered as $p):
        $blend=($p['kind']??'')==='blend';
        $prices=$p['prices']??array();
        $sale_prices=$p['sale_prices']??array();
        $img=!empty($p['image_url'])?$p['image_url']:$fallback;
        $available=count(array_filter($prices,function($x){return (float)$x>0;}))>0;
        $in_stock = ($p['stock_status'] ?? 'instock') !== 'outofstock';
        $first_size=''; $first_regular=0; $first_sale=0; $first_effective=0;
        foreach(array('30ml','50ml','100ml') as $sz) {
            $reg=(float)($prices[$sz]??0); $sale=(float)($sale_prices[$sz]??0);
            if($reg>0) { $first_size=$sz; $first_regular=$reg; $first_sale=($sale>0&&$sale<$reg)?$sale:0; $first_effective=$first_sale?:$reg; break; }
        }
    ?>
      <article class="avlux-product-card">
        <div class="avlux-product-image"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p['name']); ?>" loading="lazy"><span><?php echo $blend?'SIGNATURE BLEND':'FRAGRANCE'; ?></span></div>
        <div class="avlux-product-info">
          <div class="avlux-product-meta"><?php echo esc_html($p['gender']??''); ?><?php if(!empty($p['strength'])) echo ' · '.esc_html($p['strength']); ?></div>
          <h3><?php echo esc_html($p['name']); ?></h3>
          <?php if(!$blend&&!empty($p['brand'])): ?><p class="avlux-product-brand">Inspired by <?php echo esc_html($p['brand']); ?></p><?php endif; ?>
          <p class="avlux-product-notes"><?php echo esc_html($p['description']??($blend?($p['notes']??''):($p['family']??''))); ?></p>
          <label class="avlux-v16-size-label" for="avlux-size-<?php echo esc_attr($p['id']); ?>">Choose bottle size</label>
          <select class="avlux-v16-size" id="avlux-size-<?php echo esc_attr($p['id']); ?>" data-v16-size>
            <?php foreach(array('30ml','50ml','100ml') as $sz):
              $regular=(float)($prices[$sz]??0); $sale=(float)($sale_prices[$sz]??0);
              $effective=($sale>0&&$regular>0&&$sale<$regular)?$sale:$regular;
              if($regular>0): ?>
                <option value="<?php echo esc_attr($sz); ?>" data-price="<?php echo esc_attr($effective); ?>" data-regular="<?php echo esc_attr($regular); ?>" data-sale="<?php echo esc_attr($effective<$regular?$effective:0); ?>" <?php selected($sz,$first_size); ?>>
                  <?php echo esc_html($sz.' — Rs. '.number_format_i18n($effective).($effective<$regular?' (was Rs. '.number_format_i18n($regular).')':'')); ?>
                </option>
              <?php endif; endforeach; ?>
          </select>
          <div class="avlux-price-request avlux-v17-price" data-v16-price>
            <?php if($first_regular>0&&$first_sale>0): ?><del>Rs. <?php echo esc_html(number_format_i18n($first_regular)); ?></del> <strong>Rs. <?php echo esc_html(number_format_i18n($first_sale)); ?></strong><span class="avlux-sale-badge">SALE</span>
            <?php elseif($first_regular>0): ?>Rs. <?php echo esc_html(number_format_i18n($first_regular)); ?>
            <?php else: ?>Price not set yet<?php endif; ?>
          </div>
          <?php if($available && $in_stock): ?>
            <button type="button" class="avlux-product-cta avlux-v16-add" data-id="<?php echo esc_attr($p['id']); ?>" data-name="<?php echo esc_attr($p['name']); ?>" data-kind="<?php echo $blend?'blend':'perfume'; ?>" data-image="<?php echo esc_url($img); ?>">Add to cart <span>＋</span></button>
          <?php elseif(!$in_stock): ?>
            <span class="avlux-v16-unpriced" aria-disabled="true">Out of stock</span>
          <?php else: ?>
            <span class="avlux-v16-unpriced">Add prices in Store Manager to enable ordering</span>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
    <?php if (!$full): ?>
      <div class="avlux-v16-preview-cart"><span>Shopping bag: <b data-avlux-bag-count>0</b> item(s)</span><a href="<?php echo esc_url(avlux_url('shop','/shop/')); ?>">View bag & checkout →</a></div>
    <?php endif; ?>
    <?php if (!$filtered): ?><div class="avlux-no-results">No products matched your search.</div><?php endif; ?>
    <?php if($full): ?>
    <section class="avlux-v16-checkout" id="avlux-v16-checkout" hidden><div class="avlux-catalog-head"><span class="avlux-eyebrow">YOUR ORDER</span><h2>Your shopping <em>bag.</em></h2><p>Review your items and complete your delivery details below.</p></div><div id="avlux-v16-lines"></div><div class="avlux-v16-total">Total payable <strong id="avlux-v16-total">Rs. 0</strong></div>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="avlux-v16-form" id="avlux-v16-form"><input type="hidden" name="action" value="avlux_place_order"><?php wp_nonce_field('avlux_place_order','avlux_order_nonce'); ?><input type="hidden" name="cart_payload" id="avlux-v16-payload"><h3>Delivery information</h3><div class="avlux-v16-fields"><label>Full name *<input name="customer_name" autocomplete="name" required maxlength="120"></label><label>Mobile number *<input name="customer_phone" type="tel" autocomplete="tel" required maxlength="30" placeholder="03XXXXXXXXX"></label><label>Email address *<input name="customer_email" type="email" autocomplete="email" required maxlength="190"></label><label>City *<input name="customer_city" autocomplete="address-level2" required maxlength="100"></label><label class="wide">Complete delivery address *<textarea name="customer_address" autocomplete="street-address" required rows="3" maxlength="1000" placeholder="House, street, area and nearby landmark"></textarea></label><label class="wide">Order notes (optional)<textarea name="customer_notes" rows="2" maxlength="1000"></textarea></label></div><div class="avlux-v16-cod"><strong>Payment method</strong><p>Cash on Delivery (COD) — pay in cash when your order arrives.</p></div><label class="avlux-v16-terms"><input type="checkbox" name="order_terms" value="1" required> I confirm that my order and delivery details are correct.</label><button type="submit" id="avlux-v16-submit" disabled>Place Cash on Delivery order →</button><small>Your order will be recorded in the store and an email notification will be sent to the Aroma Valle' team.</small></form></section>
    <?php if(isset($_GET['avlux_order'])&&$_GET['avlux_order']==='received'): ?><div class="avlux-v16-success"><h2>Thank you — your order has been received.</h2><p>Your Cash on Delivery order is submitted. Aroma Valle' will contact you to confirm delivery.</p><?php if(!empty($_GET['order_ref'])): ?><strong>Reference: <?php echo esc_html(sanitize_text_field(wp_unslash($_GET['order_ref']))); ?></strong><?php endif; ?></div><?php endif; ?>
    <?php endif; ?>
    <script>
    (function(){
      if(window.avluxV20CartBound)return;window.avluxV20CartBound=true;
      const key='avlux_v16_cart';
      const money=n=>'Rs. '+Number(n||0).toLocaleString('en-PK',{maximumFractionDigits:0});
      const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
      let cart=[];try{cart=JSON.parse(localStorage.getItem(key)||'[]');if(!Array.isArray(cart))cart=[];}catch(e){cart=[];}
      if(new URLSearchParams(location.search).get('avlux_order')==='received'){cart=[];localStorage.removeItem(key);}
      function update(){
        document.querySelectorAll('[data-avlux-bag-count]').forEach(el=>el.textContent=cart.reduce((a,x)=>a+x.qty,0));
        const count=document.getElementById('avlux-v16-count');if(count)count.textContent=cart.reduce((a,x)=>a+x.qty,0);
        document.querySelectorAll('#avlux-v16-header-count').forEach(el=>el.textContent=cart.reduce((a,x)=>a+x.qty,0));
        const lines=document.getElementById('avlux-v16-lines'),totalEl=document.getElementById('avlux-v16-total'),payload=document.getElementById('avlux-v16-payload'),submit=document.getElementById('avlux-v16-submit');
        if(lines){
          const sum=cart.reduce((a,x)=>a+x.price*x.qty,0);if(totalEl)totalEl.textContent=money(sum);
          lines.innerHTML=cart.length?cart.map((x,i)=>'<div class="avlux-v16-line"><div><b>'+esc(x.name)+'</b><small>'+esc(x.size)+' · '+money(x.price)+' each</small></div><div><span>× '+x.qty+'</span> <b>'+money(x.price*x.qty)+'</b> <button type="button" data-v16-remove="'+i+'">Remove</button></div></div>').join(''):'<p>Your shopping bag is empty. Add products to continue.</p>';
          if(payload)payload.value=JSON.stringify(cart.map(x=>({id:x.id,size:x.size,qty:x.qty})));
          if(submit)submit.disabled=!cart.length;
        }
        try{localStorage.setItem(key,JSON.stringify(cart));}catch(e){}
      }
      document.addEventListener('change',function(e){
        const sel=e.target.closest('[data-v16-size]');if(!sel)return;
        const opt=sel.selectedOptions[0],card=sel.closest('.avlux-product-card'),box=card&&card.querySelector('[data-v16-price]');if(!opt||!box)return;
        const reg=Number(opt.dataset.regular||0),sale=Number(opt.dataset.sale||0),price=Number(opt.dataset.price||0);
        box.innerHTML=sale>0&&sale<reg?'<del>'+money(reg)+'</del> <strong>'+money(price)+'</strong><span class="avlux-sale-badge">SALE</span>':money(price);
      });
      document.addEventListener('click',function(e){
        const add=e.target.closest('.avlux-v16-add');
        if(add){
          const card=add.closest('.avlux-product-card'),sel=card&&card.querySelector('[data-v16-size]'),opt=sel&&sel.selectedOptions[0];
          if(!opt){alert('Please select a bottle size.');return;}
          const size=sel.value,price=Number(opt.dataset.price||0);if(price<=0){alert('This product price is not set.');return;}
          const old=cart.find(x=>x.id===add.dataset.id&&x.size===size);
          if(old)old.qty=Math.min(10,old.qty+1);else cart.push({id:add.dataset.id,name:add.dataset.name,size,price,regular:Number(opt.dataset.regular||price),image:add.dataset.image||'',qty:1});
          update();add.textContent='Added to cart ✓';setTimeout(()=>{add.innerHTML='Add to cart <span>＋</span>';},1100);return;
        }
        const open=e.target.closest('#avlux-v16-open');
        if(open){const panel=document.getElementById('avlux-v16-checkout');if(panel){panel.hidden=!panel.hidden;if(!panel.hidden)panel.scrollIntoView({behavior:'smooth'});}return;}
        const remove=e.target.closest('[data-v16-remove]');
        if(remove){cart.splice(Number(remove.dataset.v16Remove),1);update();return;}
      });
      document.addEventListener('submit',function(e){
        if(e.target&&e.target.id==='avlux-v16-form'){
          const payload=document.getElementById('avlux-v16-payload');if(payload)payload.value=JSON.stringify(cart.map(x=>({id:x.id,size:x.size,qty:x.qty})));
        }
      });
      update();
    })();
    </script>
    </div>
    <?php if ($full): ?><?php echo avlux_standalone_footer(); ?></div><?php endif; ?>
    <?php return ob_get_clean();
}
add_shortcode('aroma_valle_catalog','avlux_v16_catalog_shortcode');

function avlux_v16_contact_shortcode() {
    $email=avlux_v16_store_email(); $o=avlux_options(); $phone=preg_replace('/\D/','',$o['whatsapp']); $display='+' . $phone;
    $wa=avlux_whatsapp_url("Hello Aroma Valle', I would like to enquire about your fragrances.");
    ob_start(); ?>
    <div class="avlux-store-page avlux-contact-page"><?php echo avlux_standalone_header('contact'); ?>
    <div class="avlux-inner-page avlux-v16-contact">
      <span class="avlux-eyebrow">PERSONAL SERVICE</span><h1>We would love to <em>hear from you.</em></h1>
      <p>For fragrance questions, order assistance or feedback, our team is happy to assist.</p>
      <div class="avlux-v16-email"><span>Email</span><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div>
      <div class="avlux-v16-email"><span>Phone / WhatsApp</span><a href="tel:<?php echo esc_attr('+'.$phone); ?>"><?php echo esc_html($display); ?></a></div>
      <div class="avlux-contact-actions">
        <a class="avlux-btn avlux-btn-dark" href="mailto:<?php echo esc_attr($email); ?>?subject=<?php echo rawurlencode("Aroma Valle' enquiry"); ?>">Send an email ↗</a>
        <a class="avlux-btn avlux-whatsapp-contact" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener"><svg viewBox="0 0 32 32" aria-hidden="true"><path fill="currentColor" d="M16 0C7.2 0 .1 7.1.1 15.9c0 2.8.7 5.5 2.1 7.9L0 32l8.4-2.2a15.8 15.8 0 0 0 7.6 1.9h.1c8.8 0 15.9-7.1 15.9-15.9A15.8 15.8 0 0 0 16 0zm0 28.9h-.1a13 13 0 0 1-6.6-1.8l-.5-.3-5 1.3 1.3-4.9-.3-.5a13 13 0 1 1 11.2 6.2zm7.1-9.7c-.4-.2-2.2-1.1-2.6-1.2-.4-.1-.6-.2-.9.2-.3.4-1 1.2-1.2 1.5-.2.3-.5.3-.9.1-.4-.2-1.6-.6-3.1-1.9-1.1-1-1.9-2.2-2.1-2.6-.2-.4 0-.6.2-.8l.6-.7c.2-.2.3-.4.4-.7.1-.3 0-.5 0-.7-.1-.2-.9-2.1-1.2-2.8-.3-.7-.6-.6-.9-.6h-.8c-.3 0-.7.1-1 .5-.4.4-1.3 1.3-1.3 3.2s1.3 3.7 1.5 4c.2.3 2.6 4 6.3 5.6.9.4 1.6.6 2.1.8.9.3 1.7.3 2.3.2.7-.1 2.2-.9 2.5-1.8.3-.9.3-1.6.2-1.8-.1-.2-.4-.3-.8-.5z"/></svg> WhatsApp</a>
      </div>
    </div><?php echo avlux_standalone_footer(); ?></div>
    <?php return ob_get_clean();
}
add_shortcode('aroma_valle_contact','avlux_v16_contact_shortcode');

function avlux_v16_home_shortcode() {
    $html=avlux_home_shortcode();
    // Normalize a previously saved literal backslash-n and correct the hero text rendering.
    $html=str_replace(array('\\\\n','\\n'),' ',$html);
    $html=preg_replace('/<a class="avlux-text-link"[^>]*>.*?<\/a>/s','<a class="avlux-text-link" href="'.esc_url(avlux_url('shop','/shop/')).'">Shop fragrances ↗</a>',$html,1);
    $email=esc_attr(avlux_v16_store_email());
    $html=preg_replace('/<a href="https:\/\/wa\.me\/[^"]+" target="_blank" rel="noopener">WhatsApp<\/a>/','<a href="mailto:'.$email.'">Email us</a>',$html);
    return $html;
}
add_shortcode('aroma_valle_luxury_home','avlux_v16_home_shortcode');
add_shortcode('av3_aroma_valle_home','avlux_v16_home_shortcode');


/* Aroma Valle' 2.6.11: confirmed phone number, brand logo and WhatsApp icon. */



function avlux_v2615_fixed_header_styles() {
    if (is_admin()) return;
    echo '<style id="avlux-v2615-fixed-header">
    body.avlux-storefront-page-active{padding-top:132px!important}
    .avlux-announcement{position:fixed!important;left:0!important;right:0!important;top:0!important;width:100%!important;height:42px!important;min-height:42px!important;box-sizing:border-box!important;display:flex!important;align-items:center!important;justify-content:center!important;padding:8px 15px!important;z-index:100050!important}
    .avlux-standalone-header{position:fixed!important;left:0!important;right:0!important;top:42px!important;width:100%!important;min-height:88px!important;height:88px!important;box-sizing:border-box!important;padding:8px clamp(18px,5vw,76px)!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:24px!important;z-index:100040!important;background:#fbf8f2!important;border-bottom:1px solid #e5dccd!important}
    .avlux-standalone-header .avlux-logo-image{display:flex!important;align-items:center!important;justify-content:flex-start!important;flex:0 0 190px!important;width:190px!important;height:74px!important;max-width:190px!important;overflow:visible!important;position:relative!important;z-index:2!important}
    .avlux-standalone-header .avlux-logo-image img{display:block!important;width:190px!important;height:74px!important;max-width:190px!important;max-height:74px!important;object-fit:contain!important;object-position:left center!important;visibility:visible!important;opacity:1!important}
    .avlux-standalone-header nav{display:flex!important;flex:1 1 auto!important;align-items:center!important;justify-content:center!important;gap:clamp(12px,2vw,28px)!important;flex-wrap:nowrap!important;min-width:0!important}
    .avlux-standalone-header nav a{white-space:nowrap!important}
    .avlux-header-basket,.avlux-standalone-header .avlux-header-cta{flex:0 0 auto!important;white-space:nowrap!important}
    body.admin-bar.avlux-storefront-page-active{padding-top:164px!important}
    body.admin-bar .avlux-announcement{top:32px!important}
    body.admin-bar .avlux-standalone-header{top:74px!important}
    @media(max-width:900px){
      body.avlux-storefront-page-active{padding-top:150px!important}
      .avlux-standalone-header{height:108px!important;min-height:108px!important;flex-wrap:wrap!important;justify-content:space-between!important;gap:4px 12px!important;padding:6px 14px!important}
      .avlux-standalone-header .avlux-logo-image{flex-basis:140px!important;width:140px!important;height:56px!important}
      .avlux-standalone-header .avlux-logo-image img{width:140px!important;height:56px!important;max-width:140px!important;max-height:56px!important}
      .avlux-standalone-header nav{order:3!important;flex:1 0 100%!important;justify-content:center!important;gap:clamp(9px,2vw,18px)!important}
      .avlux-standalone-header nav a{font-size:9px!important;padding:4px 0!important}
      body.admin-bar.avlux-storefront-page-active{padding-top:182px!important}
      body.admin-bar .avlux-standalone-header{top:74px!important;height:108px!important}
    }
    @media(max-width:600px){
      body.avlux-storefront-page-active{padding-top:142px!important}
      .avlux-announcement{height:38px!important;min-height:38px!important;font-size:8px!important;letter-spacing:1px!important}
      .avlux-standalone-header{top:38px!important;height:104px!important;min-height:104px!important;padding:5px 10px!important}
      .avlux-standalone-header .avlux-logo-image{flex-basis:118px!important;width:118px!important;height:48px!important}
      .avlux-standalone-header .avlux-logo-image img{width:118px!important;height:48px!important;max-width:118px!important;max-height:48px!important}
      .avlux-standalone-header nav{gap:10px!important;overflow-x:auto!important;justify-content:flex-start!important}
      .avlux-standalone-header nav a{font-size:8px!important;letter-spacing:.5px!important}
      .avlux-header-basket{margin:0 4px!important;font-size:11px!important}
      .avlux-standalone-header .avlux-header-cta{font-size:9px!important;padding:8px!important}
      body.admin-bar.avlux-storefront-page-active{padding-top:188px!important}
      body.admin-bar .avlux-announcement{top:46px!important}
      body.admin-bar .avlux-standalone-header{top:84px!important;height:104px!important}
    }
    </style>';
}
add_action('wp_head','avlux_v2615_fixed_header_styles',1);

function avlux_v2612_early_styles() {
 if (is_admin()) return;
 echo '<style id="avlux-v2612-early-css">.avlux-v16-cartbar{position:sticky;top:34px;z-index:1000;box-shadow:0 5px 18px rgba(0,0,0,.12)}
 .avlux-v16-checkout-link{display:inline-flex;align-items:center;justify-content:center;background:#c6a15b;color:#17130f!important;text-decoration:none!important;padding:13px 20px;font-weight:700}
 .avlux-v16-cartbar{display:flex;justify-content:space-between;align-items:center}
 .avlux-logo-image{display:flex!important;align-items:center;max-width:190px}.avlux-logo-image img{display:block!important;width:190px!important;height:auto!important;max-width:190px!important;max-height:74px!important;object-fit:contain}@media(max-width:600px){.avlux-logo-image{max-width:150px}.avlux-logo-image img{width:150px!important;max-width:150px!important;max-height:58px!important}}
 .avlux-v2613-checkout{max-width:1120px;margin:45px auto;padding:clamp(20px,4vw,48px);background:#f5f0e7;color:#211c16}
 .avlux-v2613-item{display:grid;grid-template-columns:76px 1fr auto auto;gap:16px;align-items:center;padding:14px 0;border-bottom:1px solid #d8cbb2}
 .avlux-v2613-item img{width:76px;height:82px;object-fit:cover;background:#eee}
 .avlux-v2613-item-info small{display:block;margin-top:5px;color:#756b5d}
 .avlux-v2613-item>button{border:0;background:none;color:#8c713f;text-decoration:underline;cursor:pointer}
 .avlux-v2613-summary{max-width:520px;margin:24px 0 24px auto;padding:18px 0;border-top:1px solid #c9b999;border-bottom:1px solid #c9b999}
 .avlux-v2613-summary>div{display:flex;justify-content:space-between;gap:20px;padding:8px 0}
 .avlux-v2613-summary .avlux-v2613-grand{font-size:19px;font-weight:700;border-top:1px solid #c9b999;margin-top:8px;padding-top:16px}
 .avlux-v2613-empty{padding:22px;background:#fff}
 @media(max-width:620px){.avlux-v16-cartbar{top:0}.avlux-v2613-item{grid-template-columns:58px 1fr auto;gap:10px}.avlux-v2613-item img{width:58px;height:66px}.avlux-v2613-item>button{grid-column:2}.avlux-v2613-summary{max-width:none}}
 
 .avlux-header-basket{display:inline-flex;align-items:center;gap:7px;margin:0 16px;color:#211c16!important;text-decoration:none!important;font-size:13px;white-space:nowrap}
 .avlux-header-basket b{display:inline-flex;align-items:center;justify-content:center;min-width:21px;height:21px;padding:0 5px;border-radius:50%;background:#c6a15b;color:#17130f}
 @media(max-width:760px){.avlux-header-basket{margin:8px 0;font-size:12px}}

 .avlux-v2613-continue{display:inline-flex;align-items:center;justify-content:center;margin-top:10px;padding:13px 22px;background:#c6a15b;color:#17130f!important;text-decoration:none!important;font-weight:700}
 .avlux-v2613-continue:hover{background:#b58f4e;color:#17130f!important}
</style>';
}
add_action('wp_head','avlux_v2612_early_styles',1);
function avlux_v2611_force_confirmed_phone() {
    if (get_option('avlux_phone_2611_migrated') === 'yes') return;
    $saved=get_option('avlux_options',array());
    if (!is_array($saved)) $saved=array();
    $saved['whatsapp']='923211000926';
    update_option('avlux_options',$saved,false);
    update_option('avlux_phone_2611_migrated','yes',false);
}
add_action('plugins_loaded','avlux_v2611_force_confirmed_phone',30);

function avlux_v2611_contact_shortcut() {
    if (is_admin()) return;
    $o=avlux_options(); $phone=preg_replace('/\D/','',$o['whatsapp']);
    if (!$phone) return;
    $url='https://wa.me/'.$phone.'?text='.rawurlencode("Hello Aroma Valle', I would like to enquire about your fragrances.");
    echo '<a class="avlux-floating-whatsapp" href="'.esc_url($url).'" target="_blank" rel="noopener" aria-label="Chat with Aroma Valle on WhatsApp"><svg viewBox="0 0 32 32" aria-hidden="true"><path fill="currentColor" d="M16 0C7.2 0 .1 7.1.1 15.9c0 2.8.7 5.5 2.1 7.9L0 32l8.4-2.2a15.8 15.8 0 0 0 7.6 1.9h.1c8.8 0 15.9-7.1 15.9-15.9A15.8 15.8 0 0 0 16 0zm0 28.9h-.1a13 13 0 0 1-6.6-1.8l-.5-.3-5 1.3 1.3-4.9-.3-.5a13 13 0 1 1 11.2 6.2zm7.1-9.7c-.4-.2-2.2-1.1-2.6-1.2-.4-.1-.6-.2-.9.2-.3.4-1 1.2-1.2 1.5-.2.3-.5.3-.9.1-.4-.2-1.6-.6-3.1-1.9-1.1-1-1.9-2.2-2.1-2.6-.2-.4 0-.6.2-.8l.6-.7c.2-.2.3-.4.4-.7.1-.3 0-.5 0-.7-.1-.2-.9-2.1-1.2-2.8-.3-.7-.6-.6-.9-.6h-.8c-.3 0-.7.1-1 .5-.4.4-1.3 1.3-1.3 3.2s1.3 3.7 1.5 4c.2.3 2.6 4 6.3 5.6.9.4 1.6.6 2.1.8.9.3 1.7.3 2.3.2.7-.1 2.2-.9 2.5-1.8.3-.9.3-1.6.2-1.8-.1-.2-.4-.3-.8-.5z"/></svg><span>WhatsApp</span></a>';
    echo '<style>.avlux-logo-image img{display:block;width:190px;height:auto;max-height:74px;object-fit:contain}.avlux-standalone-header .avlux-logo-image,.avlux-standalone-footer .avlux-logo-image{display:flex;align-items:center}.avlux-floating-whatsapp{position:fixed;right:22px;bottom:24px;z-index:99999;display:inline-flex;align-items:center;gap:9px;padding:13px 17px;border-radius:30px;background:#25D366;color:#fff!important;text-decoration:none!important;font:600 14px/1.2 Arial,sans-serif;box-shadow:0 5px 22px rgba(0,0,0,.22)}.avlux-floating-whatsapp svg,.avlux-whatsapp-contact svg{width:23px;height:23px;flex:0 0 23px}.avlux-floating-whatsapp:hover{background:#128C7E;color:#fff!important;transform:translateY(-2px)}.avlux-contact-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-top:22px}.avlux-whatsapp-contact{display:inline-flex;align-items:center;gap:9px;background:#25D366!important;color:#fff!important;border-color:#25D366!important}.avlux-whatsapp-contact:hover{background:#128C7E!important}@media(max-width:600px){.avlux-floating-whatsapp{right:14px;bottom:16px;padding:12px 14px}.avlux-logo-image img{width:150px;max-height:58px}}</style>';
}
add_action('wp_footer','avlux_v2611_contact_shortcut',99);

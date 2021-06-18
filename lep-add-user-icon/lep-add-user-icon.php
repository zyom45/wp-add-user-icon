<?php
/**
 * Add User Icon
 *
 * Plugin Name: Add User Icon
 * Plugin URI:  https://wordpress.org/plugins/
 * Description: ユーザーアイコンを追加。
 * Version:     1.0
 * Author:      Lepus
 * Author URI:  https://github.com/zyom45
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: lep-add-user-icon
 * Domain Path: /languages
 *
 */

if ( ! class_exists( 'LepUserIcon' ) ) :
class LepUserIcon {
    public function __construct() {
    }
    /*
        * Initialize the class and start calling our hooks and filters
    */
    public function init() {
        add_action( 'init', array($this,'lep_user_icon_session_start' ));
        //add_shortcode
        add_shortcode('lep_user_icon_form', array($this, 'lep_user_icon_add_form_func'));
        add_action('wp_enqueue_scripts', array($this, 'lep_user_icon_enqueue_scripts'));
        
        //get_avatar
        add_filter( 'get_avatar' , array($this, 'lep_user_icon_avatar') , 1 , 5 );
    }


    public function lep_user_icon_session_start()
    {
        if( session_status() !== PHP_SESSION_ACTIVE ) {
            session_start();
        }
    }
    /*=============================================
     * 01:user icon form
     ============================================== */
    /**
     * shortcode: [lep_user_icon_form]
     */
    public function lep_user_icon_add_form_func()
    {
        if(is_user_logged_in()) {
            global $post;
            $post_id = $post->ID;
            $userID = get_current_user_id();
            $noimage = plugins_url('/assets/images/noicon.png', __FILE__);
            $message = '';

            if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
                if( isset( $_SESSION["key"], $_POST["key"] ) && $_SESSION["key"] == $_POST["key"] ) {
                    unset( $_SESSION["key"] );
                    
                    // nonce が有効で、ユーザーがこの投稿を編集可能であるかチェック。
                    if ( 
                        isset( $_POST['lep_aui_image_upload_nonce'], $_POST['post_id'] ) 
                        && wp_verify_nonce( $_POST['lep_aui_image_upload_nonce'], 'lep_aui_image_upload' )
                    ) {
                        // nonce が有効で、ユーザーが権限を持つので、続けて大丈夫。
                        if(!empty($_POST['submit_lep_aui_image_upload'])){
                                // 下記のファイルに依存するのでフロントエンドではインクルードする必要がある。
                                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                require_once( ABSPATH . 'wp-admin/includes/media.php' );
                                
                                // WordPress にアップロードを処理させる。
                                // 注意: 'lep_aui_image_upload' は上のフォームで file input 要素の name 属性。
                                $attachment_id = media_handle_upload( 'lep_aui_image_upload', $_POST['post_id'] );
                                
                                if ( is_wp_error( $attachment_id ) ) {
                                    // 画像のアップロード中にエラーが起きた。
                                    $_SESSION['status'] = 'lep_message_error';
                                    $_SESSION['message'] = '画像のアップロードに失敗しました';
                                } else {
                                    // 画像のアップロードに成功
                                    update_user_meta( $userID, 'lep_icon_attachment_id', $attachment_id );
                                    $_SESSION['status'] = 'lep_message_success';
                                    $_SESSION['message'] = '画像のアップロードに成功しました';
                                }
                        } else {
                            delete_user_meta( $userID, 'lep_icon_attachment_id' );
                            $_SESSION['status'] = 'lep_message_error';
                            $_SESSION['message'] = '画像を削除しました';
                        }
                        $message = '<p class="lep_message '.$_SESSION['status'].'">'.$_SESSION['message'].'</p>';
                    }
                }
            }
            $_SESSION["key"] = md5(uniqid().mt_rand());
            require plugin_dir_path( __FILE__ ).'/pages/icon-form.php';

        } else {
            return '<p class="lep_message lep_message_error">ログインしてください</p>';
        }
    }


    /*=============================================
     * 03:CSS/JSの読み込み
     ============================================== */
    /**
     * JS / CSS
     */
    public function lep_user_icon_enqueue_scripts()
    {
        wp_enqueue_style( 'lep-aui-css', plugins_url('/assets/css/lep-aui.css', __FILE__));
        //wp_enqueue_script('script-lep-aui', plugins_url('/assets/js/lep-aui.js', __FILE__), array('jquery'), '1.0.0', true);
    }

    public static function get_icon($ID = null){
        $userID = is_null($ID) ? get_current_user_id() : $ID;
        $image_id = get_user_meta( $userID,'lep_icon_attachment_id');
        if ( $image_id ) {
            return wp_get_attachment_image($image_id[0], 'thumbnail' );
        } else {
            return '<img src="'. plugins_url('/assets/images/noicon.png', __FILE__) .'" alt="no image">';
        }
    }
    
    public function lep_user_icon_avatar($avatar, $id_or_email, $size, $default, $alt){
        $user = false;
        $default = plugins_url('/assets/images/noicon.png', __FILE__);

        if ( is_numeric( $id_or_email ) ) {
            $id = (int) $id_or_email;
            $user = get_user_by( 'id' , $id );
        } elseif ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->user_id ) ) {
                $id = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }
        } else {
            $user = get_user_by( 'email', $id_or_email );	
        }
        if ( $user && is_object( $user ) ) {
            $image_id = get_user_meta( $user->data->ID,'lep_icon_attachment_id');
            if ( $image_id ) {
                $avatar = wp_get_attachment_url($image_id[0], 'thumbnail' );  // カスタム画像の URL
            } else {
                $avatar = $default;
            }
            $avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }

        return $avatar;
    }
}

$LepUserIcon = new LepUserIcon();
$LepUserIcon -> init();

endif;
<div id="lep_aui" class="lep_aui_form_field">
    <form id="lep_aui_image_upload" method="post" action="#" enctype="multipart/form-data">
        <input type="hidden" name="key" value="<?php echo htmlspecialchars( $_SESSION["key"], ENT_QUOTES );?>">
        <input type="file" name="lep_aui_image_upload" id="lep_aui_image_upload"  multiple="false" />
        <input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>" />
        <?php wp_nonce_field( 'lep_aui_image_upload', 'lep_aui_image_upload_nonce' ); ?>
        <input class="lep_aui_image_upload_btn" name="submit_lep_aui_image_upload" type="submit" value="アップロード" />
        <input class="lep_delete_btn"　name="submit_lep_aui_image_delete" type="submit" value="削除">
    </form>
    <div class="lep_aui_user_image">
        <h3>現在のアイコン画像</h3>
        <div class="lep_aui_user_icon">
            <figure>
            <?php $image_id = get_user_meta( $userID,'lep_icon_attachment_id');
            if ( $image_id ) {
                echo wp_get_attachment_image($image_id[0], 'thumbnail' );
            } else {
                echo '<img src="'. $noimage .'" alt="no image">';
            } ?>
            </figure>
        </div>
    </div>
    <div class="lep_message_wrap"><?php echo $message; ?></div>
</div>
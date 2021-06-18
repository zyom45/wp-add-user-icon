
jQuery(document).ready( function($) {
    var url = document.location;
    var $path = url.origin;
    var index = $lui_url.indexOf('html');
    $path += $lui_url.slice(index + 4);
    $path += 'lep-add-user-icon-delete.php';
    console.log($path);
    $('.lep_delete_btn').click(function(){
        $.ajax({
            type: 'post',
            url: $path, //送信先PHPファイル
            data: {'func' : 'lep_user_icon_delete'}, //POSTするデータ
            success: function(res){ //正常に処理が完了した時
                console.log('success');
                console.log(res);
                $('.lep_message_wrap').innerHTML = '<p class="lep_message lep_message_success">削除しました</p>';
            },
            error: function(res) {
               console.log('error');
            }
        });
    });
});
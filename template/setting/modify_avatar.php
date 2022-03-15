<!--{use_layout layout/admin_main}-->
<?php
$head_info = [
    'title' => YiluPHP::I()->lang('menu_modify_avatar'),
];
?>

<style type="text/css">
    #crop_avatar {
        height: 300px;
        overflow: hidden;
        padding: 0;
        background: #ccc;
    }
    .new_avatar,
    .current_avatar{
        width: 150px;
        float: left;
        margin-right: 10px;
    }
    .pic_button{
        float: left;
        height: 100px;
        margin-top: 70px;
    }
    .pic_button a{
        width: 100px;
        height: 100px;
        display: table-cell;
        padding: 6px;
    }
    #current_avatar {
        width: 150px;
        height: 150px;
    }
    .img-preview {
        width: 150px;
        height: 150px;
        overflow: hidden;
        border: dotted 1px #ccc;
    }
    .crop_top,
    .crop_bottom,
    .crop_left,
    .crop_right {
        opacity: 0.8;
        -moz-opacity: 0.8;
        position: absolute;
        background: #fff;
    }
    .crop_left {
        width: 50%;
        margin-left: -100px;
        top:0;
        left: 0;
        height: 200px;
    }
    .crop_right {
        width: 50%;
        margin-right: -100px;
        top:0;
        right: 0;
        height: 200px;
    }
    .crop_top {
        width: 100%;
        top:0;
        left: 0;
        height: 0;
    }
    .crop_bottom {
        width: 100%;
        bottom:0;
        left: 0;
        height: 0;
    }
    .crop_kuang {
        position: absolute;
        border: dotted #666 1px;
        width: 200px;
        height: 200px;
        margin-left: -100px;
        top:0;
        left: 50%;
    }
</style>

<?php echo load_static('/include/css_cropper.shtml'); ?>

    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="current_avatar">
                <div>
                    <?php echo YiluPHP::I()->lang('current_avatar'); ?>
                </div>
                <div>
                    <img id="current_avatar" src="<?php echo $self_info['avatar']; ?>" />
                </div>
            </div>
            <div class="new_avatar">
                <div>
                    <?php echo YiluPHP::I()->lang('new_avatar'); ?>
                    <input type="file" class="form-control" name="selectfile" id="selectfile" style="display: none" />
                </div>
                <div>
                    <div class="img-preview"></div>
                </div>
            </div>
            <div class="pic_button">
                <a href="javascript:$('#selectfile').click();" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <?php echo YiluPHP::I()->lang('select_a_photo'); ?>
                </a>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-12">
            <div id="crop_avatar">
                <img src="<?php echo $config['default_avatar']; ?>">
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg btn-block" id="save_avatar_btn" type="submit"><?php echo YiluPHP::I()->lang('save'); ?></button>

<div class="mb-5"></div>
<?php echo load_static('/include/js_cropper.shtml'); ?>
<script>
    (function() {
        var $image = $('#crop_avatar > img');

        $image.cropper({
            aspectRatio: 1 / 1,
            dragCrop: false,
            preview: '.img-preview',
            crop: function(data) {
                // Output the result data for cropping image.
            }
            // dragType:"move",
            // setDragMode:"move"
        });
        // $('#crop_avatar > img').on({
        //   'dragmove.cropper': function (e) {
        //     console.log(e.type, e.dragType);
        //   }
        // });
        // $('#crop_avatar > img').cropper('dragType', 'move');
        // $image.cropper("setDragMode", "move");

        $('input[type=file][name=selectfile]').change(function(){
            var file=this.files[0];
            if (file.type!='image/jpeg' && file.type!='image/png' && file.type!='image/gif' && file.type!='image/bmp') {
                $(document).dialog({
                    type: "notice"
                    , position: "bottom"
                    , dialogClass: "dialog_warn"
                    , infoText: getLang("please_select_a_picture_file")
                    , autoClose: 3000
                    , overlayShow: false
                });
                return;
            }
            var reader=new FileReader();
            reader.onload=function(){
                // 通过 reader.result 来访问生成的 DataURL
                var url=reader.result;
                // $image.cropper('reset');
                $image.cropper('replace', url);
            };
            reader.readAsDataURL(file);
        });

        $("#save_avatar_btn").click(function(e){
            e.preventDefault();
            var $form = $(e.target);
            if ($form.attr('novalidate')=='novalidate') {
                return false;
            }
            var img = $image.cropper('getCroppedCanvas', {
                width: 300,
                height: 300
            });
            // console.log(img.toDataURL());
            var toast = loading();
            $.post("<?php echo url_pre_lang(); ?>/setting/save_avatar", {avatar:img.toDataURL()}, function(data) {
                toast.close();
                if (data.code===0) {
                    toast.dialog({
                        overlayClose: true
                        , titleShow: false
                        , content: data.msg
                        , onClickConfirmBtn: function(){
                            window.location.reload();
                        }
                    });
                }
                else {
                    $(document).dialog({
                        type: "notice"
                        , position: "bottom"
                        , dialogClass: "dialog_warn"
                        , infoText: data.msg
                        , autoClose: 3000
                        , overlayShow: false
                    });
                }
            }, 'json');
        });
    })();
</script>
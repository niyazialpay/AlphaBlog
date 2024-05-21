<style>
    .tab-menu {
        list-style: none;
        margin:0;
        padding:0;
        background: #eee;
        border-bottom: 1px solid #999;
    }
    .tab-menu li {
        display: inline-block;
        padding: 10px;
    }
    .active-tab {
        background: #999;
        box-shadow: inset -3px 0 8px -5px #111, inset 3px 0 8px -5px #111;
    }
    .active-tab a {
        color: #fff;
    }
</style>
<script>
    let comment_save_url = '{{route('admin.post.comments.create')}}';

    function ApproveComment(id, approve = true){
        let post_link = '{{route('admin.post.comments')}}/' + id;
        let message;
        let title;

        if(approve){
            post_link += '/approve';
            message = '{{__('comments.success_approve')}}';
            title = '{{__('comments.approved')}}';
        }
        else{
            post_link += '/disapprove';
            message = '{{__('comments.success_disapprove')}}';
            title = '{{__('comments.disapproved')}}';
        }

        $.ajax({
            url: post_link,
            type: 'POST',
            data: {
                _token: '{{csrf_token()}}'
            },
            success: function () {
                Swal.fire(
                    title,
                    message,
                    'success'
                );
                window.location.reload();
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: xhr.responseJSON.message
                });
            }
        });
    }
    function DeleteComment(id, force = false){
        Swal.fire({
            title: '{{__('general.are_you_sure')}}',
            text: "{{__('general.you_wont_be_able_to_revert_this')}}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{__('general.delete_confirm_yes')}}',
            cancelButtonText: '{{__('general.delete_confirm_no')}}',
        }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.post.comments')}}/'+id+'/delete' + (force ? '/permanent' : ''),
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function () {
                            Swal.fire(
                                '{{__('general.deleted')}}',
                                '{{__('comments.comment_deleted')}}',
                                'success'
                            );
                            window.location.reload();
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: xhr.responseJSON.message
                            });
                        }
                    });
                }
            }
        )
    }
    function RestoreComment(id){
        Swal.fire({
            title: '{{__('general.are_you_sure')}}',
            text: "{{__('comments.restore_sure')}}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '{{__('general.restore_it')}}',
            cancelButtonText: '{{__('general.cancel')}}',
        }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{route('admin.post.comments')}}/'+id+'/restore',
                        type: 'POST',
                        data: {
                            _token: '{{csrf_token()}}'
                        },
                        success: function () {
                            Swal.fire(
                                '{{__('general.restored')}}',
                                '{{__('comments.comment_restored')}}',
                                'success'
                            );
                            window.location.reload();
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: xhr.responseJSON.message
                            });
                        }
                    });
                }
            }
        );
    }
    function EditComment(id){
        $.ajax({
            url: '{{route('admin.post.comments')}}/'+id+'/edit',
            type: 'POST',
            data: {
                _token: '{{csrf_token()}}'
            },
            success: function (response) {
                if(response.user_id){
                    $('#user').val(response.user_id);
                    $('#comment_name_input').hide();
                    $('#comment_email_input').hide();
                    $('#user_select_input').show();
                }
                else{
                    $('#comment_name').val(response.name);
                    $('#comment_email').val(response.email);
                    $('#comment_name_input').show();
                    $('#comment_email_input').show();
                    $('#user_select_input').hide();
                }
                $('#post_title').text(response.post.title);
                $('#comment').val(response.comment);
                tinyMCE.activeEditor.setContent(response.comment);
                $('#post_id').val(response.post_id);
                $('#created_date').val(moment(response.created_at).format("YYYY-MM-DD HH:mm"));
                $('#commentModal').modal('show');
                comment_save_url = '{{route('admin.post.comments.create')}}/'+id;
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: xhr.responseJSON.message
                });
            }
        });
    }
    function NewComment(blog_id, post_title){
        $('#commentEditForm').trigger('reset');
        $('#post_id').val(blog_id);
        $('#post_title').text(post_title);
        $('#user').val('{{auth()->user()->id}}');
        $('#comment_name_input').show();
        $('#comment_email_input').show();
        $('#user_select_input').show();
        $('#commentModal').modal('show');
        comment_save_url = '{{route('admin.post.comments.create')}}';
    }

    $(document).ready(function(){
        let tinymce_comment_settings = {
            selector: 'textarea#comment',
            height: 400,
            promotion: false,
            language: '{{app('default_language')->code}}',
            branding: false,
            license_key: 'gpl',
            toolbar: 'undo redo | insert | style select | bold italic | font | fontsize select | link',
            menubar : false,
            statusbar:false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'pagebreak',
                'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
                'insertdatetime', 'media', 'nonbreaking', 'table', 'directionality',
                'emoticons', 'codesample', 'help'
            ],
            skin: tinymce_skin,
            content_css: tinymce_content_css,
            mobile: {
                theme: 'silver',
                toolbar: 'undo | bold italic | link | image | font size select forecolor',
                menubar: false,
                height: 300,
                plugins: [ 'autosave', 'lists', 'autolink', 'code', 'fullscreen' ]
            }
        }

        tinymce.init(tinymce_comment_settings);

        $('#dark-mode-switcher-button').on('click', function(){

            if(localStorage.getItem("dark-mode") === "true"){
                tinymce_comment_settings.content_css = 'dark';
                tinymce_comment_settings.skin = 'oxide-dark';
            }
            else{
                tinymce_comment_settings.content_css = 'default';
                tinymce_comment_settings.skin = 'oxide';
            }
            tinymce.get('comment').remove();
            tinymce.init(tinymce_comment_settings);
        });

        $('#commentEditForm').submit(function(){
            $.ajax({
                url: comment_save_url,
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function () {
                    Swal.fire(
                        '{{__('general.saved')}}',
                        '{{__('comments.saved')}}',
                        'success'
                    )
                    window.location.reload()
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON.message
                    });
                }
            });
        });
    });

    // Prevent Bootstrap dialog from blocking focusin
    document.addEventListener('focusin', (e) => {
        if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            e.stopImmediatePropagation();
        }
    });
</script>

<div class="modal" id="encryption_key_modal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="encryptionForm" method="post" action="javascript:void(0)">
            <div class="modal-header">
                <h5 class="modal-title">@lang('notes.encryption_key')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <label for="encryption_key">@lang('notes.encryption_key')</label>
                        <input type="password" class="form-control" id="encryption_key" name="encryption_key" placeholder="@lang('notes.encryption_key')">
                    </div>
                </div>
            </div>
            @csrf
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('general.close')</button>
                <button type="submit" class="btn btn-primary">@lang('general.save')</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        @if(!session()->has('encryption_key'))
        $('#encryption_key_modal').modal('show');
        @endif
        $('#encryptionForm').submit(function(){
            $.ajax({
                url: '{{route('admin.notes.encryption')}}',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    $('#encryption_key_modal').modal('hide');
                    Swal.fire({
                        title: '@lang('general.saved')',
                        text: '@lang('notes.encryption_key_saved')',
                        icon: 'success',
                        confirmButtonText: '@lang('general.ok')',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    })
                }
            })
        });
    });
</script>

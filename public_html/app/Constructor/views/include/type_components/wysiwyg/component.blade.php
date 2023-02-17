@push('bottom')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#textarea_{{$name}}').summernote({
                height: ($(window).height() - 300),
                callbacks: {
                    onImageUpload: function (image) {
                        uploadImage{{$name}}(image[0]);
                    }
                }
            });

            function uploadImage{{$name}}(image) {
                var data = new FormData();
                data.append("userfile", image);
                $.ajax({
                    url: '{{BTBooster::mainpath("upload-summernote")}}',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: data,
                    type: "post",
                    success: function (url) {
                        var image = $('<img>').attr('src', url);
                        $('#textarea_{{$name}}').summernote("insertNode", image[0]);
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            }
        })
    </script>
@endpush
<li class='{{ @$stripe && @$loop->index %2 === 0 ? 'bg-gray-100' : '' }} px-4 py-2 border-b border-gray-200 sm:flex sm:items-center form-group' id='form-group-{{$name}}' style="{{@$form['style']}}">
    <label class='control-label text-xs leading-4 font-semibold uppercase tracking-wider text-gray-900 sm:w-3/12'>{{$form['label']}}</label>

    <div class="{{$col_width?:'col-sm-10'}} mt-1 text-sm leading-5 sm:mt-0 sm:w-9/12">
        <textarea id='textarea_{{$name}}' id="{{$name}}" {{$required}} {{$readonly}} {{$disabled}} name="{{$form['name']}}" class='form-control transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent border-gray-300 w-full'
                  rows='5'>{{ $value }}</textarea>
        <div class="text-danger">{{ $errors->first($name) }}</div>
        <p class='help-block'>{{ @$form['help'] }}</p>
    </div>
</li>

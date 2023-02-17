{{--
@push('bottom')
    <script src="https://unpkg.com/flowbite@1.5.3/dist/datepicker.js"></script>
@endpush
@push('bottom')

    @if (App::getLocale() != 'en')
        <script src="{{ asset ('vendor/crudbooster/assets/adminlte/plugins/datepicker/locales/bootstrap-datepicker.'.App::getLocale().'.js') }}"
                charset="UTF-8"></script>
    @endif
    <script type="text/javascript">
        var lang = '{{App::getLocale()}}';
        $(function () {
            $('.input_date').datepicker({
                format: 'yyyy-mm-dd',
                @if (in_array(App::getLocale(), ['ar', 'fa']))
                rtl: true,
                @endif
                language: lang
            });

            $('.open-datetimepicker').click(function () {
                $(this).next('.input_date').datepicker('show');
            });

        });

    </script>
@endpush
--}}
@push('bottom')
    <script type="text/javascript">
        function datetime_{{$name}}() {
            const datepickerEl = document.getElementById('datetime_{{$name}}');
            new Datepicker(datepickerEl, {
                // options
                format: "dd.mm.y",
                language: "ru",
            });
        }
    </script>
@endpush

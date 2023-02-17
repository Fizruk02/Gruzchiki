@push('bottom')
    <script type="text/javascript">
        function datetime_{{$name}}() {
            const datepickerEl = document.getElementById('datetime_{{$name}}');
            new Datepicker(datepickerEl, {
                // options
                format: "dd.mm.yyyy",
                language: "ru",
            });
        }
    </script>
@endpush

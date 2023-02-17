<style>
.help-block {
    color: #737373;
    font-size: 14px;
}
.btn-success {
    color:#212529;
}
</style>
<div>

<?php
//$action = (@$row) ? BTBooster::mainpath("edit-save/$row->id") : BTBooster::mainpath("add-save");
$action = '';
$return_url = !empty($return_url) ? $return_url : g('return_url');
?>
<form class='form-horizontal' method='post' id="form" enctype="multipart/form-data" action='{{$action}}'>
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type='hidden' name='return_url' value='{{ @$return_url }}'/>
    {{--
    <input type='hidden' name='ref_mainpath' value='{{ BTBooster::mainpath() }}'/>
    --}}
    <input type='hidden' name='ref_parameter' value='{{urldecode(http_build_query(@$_GET))}}'/>
    @if(@$hide_form)
        <input type="hidden" name="hide_form" value='{!! serialize($hide_form) !!}'>
    @endif
    <div class="box-body" id="parent-form-area">
        <div class="mb-4">
            <ul @if(@$ul_class && @$command != 'detail') class="{{ @$ul_class }}" @endif>
                @if(@$command == 'detail')
                    @include("crudbooster::include.form_detail")
                @else
                    @include("crudbooster::include.form_body")
                @endif
            </ul>
        </div>
    </div><!-- /.box-body -->

    <div class="box-footer">

        <div class="form-group">
            <label class="control-label col-sm-2"></label>
            <div class="col-sm-10">
                @if(@$button_cancel && BTBooster::getCurrentMethod() != 'getDetail')
                    {{--
                    @if(g('return_url'))
                        <a href='{{g("return_url")}}' class='btn btn-default'><i
                                    class='fa fa-chevron-circle-left'></i> {{cbLang("button_back")}}</a>
                    @else
                        <a href='{{BTBooster::mainpath("?".http_build_query(@$_GET)) }}' class='btn btn-default'><i
                                    class='fa fa-chevron-circle-left'></i> {{cbLang("button_back")}}</a>
                    @endif
                    --}}
                @endif

                @if(@$custom_component)
                    @livewire($custom_component['component'], $custom_component['params'], key('ous-'.$model->id))
                @endif

                @if(BTBooster::isCreate() || BTBooster::isUpdate())

                    @if(BTBooster::isCreate() && @$button_addmore==TRUE && $command == 'add')
                        <input type="submit" name="submit" value='{{cbLang("button_save_more")}}' class='btn btn-success'>
                    @endif

                    @if(@$button_save && @$command != 'detail')
                        <div class="md:flex items-center justify-end px-4 py-3 text-left sm:px-6">
                            @foreach($button_exts as $key => $btn)
                                <input @if(@$btn['id']) id="{{$btn['id']}}" @endif type="{{@$btn['type']}}" name="{{$btn['name']}}" value='{{$key}}' class='inline-flex items-center px-4 py-2 {{$btn['class']}} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition mb-1' @if (@$btn['modal']) type="button" data-modal-toggle="{{$btn['modal']['id']}}" @endif >&nbsp;
                                @if (@$btn['modal'])
                                    @include("components.send_time")
                                @endif
                            @endforeach
                            <input type="submit" name="submit" value='{{cbLang("button_save")}}' class='inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition'>
                        </div>
                    @endif

                @endif
            </div>
        </div>


    </div><!-- /.box-footer-->

</form>

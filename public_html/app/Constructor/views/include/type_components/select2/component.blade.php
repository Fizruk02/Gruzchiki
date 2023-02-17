
@if(@$form['datatable'])

    @if(@$form['relationship_table'])
        {{--
        @push('bottom')
            <script type="text/javascript">
                $(function () {
                    $('#{{$name}}').select2();
                })
            </script>
        @endpush
        --}}
    @else
        @if(@$form['datatable_ajax'] == true)

            <?php
            $datatable = @$form['datatable'];
            $where = @$form['datatable_where'];
            $format = @$form['datatable_format'];

            $raw = explode(',', $datatable);
            $url = BTBooster::mainpath("find-data");

            $table1 = $raw[0];
            $column1 = $raw[1];

            @$table2 = $raw[2];
            @$column2 = $raw[3];

            @$table3 = $raw[4];
            @$column3 = $raw[5];
            ?>

            @push('bottom')
                <script>
                    $(function () {
                        $('#{{$name}}').select2({
                            placeholder: {
                                id: '-1',
                                text: '{{cbLang('text_prefix_option')}} {{$form['label']}}'
                            },
                            allowClear: true,
                            ajax: {
                                url: '{!! $url !!}',
                                delay: 250,
                                data: function (params) {
                                    var query = {
                                        q: params.term,
                                        format: "{{$format}}",
                                        table1: "{{$table1}}",
                                        column1: "{{$column1}}",
                                        table2: "{{$table2}}",
                                        column2: "{{$column2}}",
                                        table3: "{{$table3}}",
                                        column3: "{{$column3}}",
                                        where: "{!! addslashes($where) !!}"
                                    }
                                    return query;
                                },
                                processResults: function (data) {
                                    return {
                                        results: data.items
                                    };
                                }
                            },
                            escapeMarkup: function (markup) {
                                return markup;
                            },
                            minimumInputLength: 1,
                            @if($value)
                            initSelection: function (element, callback) {
                                var id = $(element).val() ? $(element).val() : "{{$value}}";
                                if (id !== '') {
                                    $.ajax('{{$url}}', {
                                        data: {
                                            id: id,
                                            format: "{{$format}}",
                                            table1: "{{$table1}}",
                                            column1: "{{$column1}}",
                                            table2: "{{$table2}}",
                                            column2: "{{$column2}}",
                                            table3: "{{$table3}}",
                                            column3: "{{$column3}}"
                                        },
                                        dataType: "json"
                                    }).done(function (data) {
                                        callback(data.items[0]);
                                        $('#<?php echo $name?>').html("<option value='" + data.items[0].id + "' selected >" + data.items[0].text + "</option>");
                                    });
                                }
                            }

                            @endif
                        });
                    })
                </script>
            @endpush

        @else
            {{--
            @push('bottom')
                <script type="text/javascript">
                    $(function () {
                        $('#{{$name}}').select2();
                    })
                </script>
            @endpush
            --}}
        @endif
    @endif
@else
    {{--
    @push('bottom')
        <script type="text/javascript">
            $(function () {
                $('#{{$name}}').select2();
            })
        </script>
    @endpush
    --}}
@endif

<li class='{{ @$stripe && @$loop->index %2 === 0 ? 'bg-gray-100' : '' }} px-4 py-2 border-b border-gray-200 sm:flex sm:items-center form-group {{$header_group_class}} {{ ($errors->first($name))?"has-error":"" }}' id='form-group-{{$name}}' style="{{@$form['style']}}">
    <label class="text-xs leading-4 font-semibold uppercase tracking-wider text-gray-900 sm:w-3/12">
        {{$form['label']}}
        @if($required)
            <span class='text-danger' title='{!! cbLang('this_field_is_required') !!}'>*</span>
        @endif
    </label>

    <div class="mt-1 text-sm leading-5 sm:mt-0 sm:w-9/12">
        {{--
        @push('bottom')
            <script type="text/javascript">
                function select2Alpine{{$name}}() {
                    this.select2 = $('#{{$name}}').select2();
                    /*this.select2.on("select2:select", (event) => {
                        this.selectedCity = event.target.value;
                    });
                    this.$watch("selectedCity", (value) => {
                        this.select2.val(value).trigger("change");
                    });*/
                }
            </script>
        @endpush
        --}}
        <div x-data="{ selectedFields: '' }" x-init="$('#{{$name}}').select2();">
        <select style='width:100%' class='form-control transition-all duration-300 ease-in-out px-2 py-1 rounded cursor-pointer focus:outline-none hover:bg-white hover:border-gray-500 border border-transparent' id="{{$name}}"
                {{$required}} {{$readonly}} {!!$placeholder!!} {{$disabled}} name="{{$name}}{{(@$form['relationship_table'])?'[]':''}}" {{ (@$form['relationship_table'])?'multiple="multiple"':'' }}

        >
            @if(@$form['dataenum'])
                <option value=''>{{cbLang('text_prefix_option')}} {{$form['label']}}</option>
                <?php
                $dataenum = @$form['dataenum'];
                $dataenum = (is_array($dataenum)) ? $dataenum : explode(";", $dataenum);
                ?>
                @foreach($dataenum as $enum)
                    <?php
                    $val = $lab = '';
                    if (strpos($enum, '|') !== FALSE) {
                        $draw = explode("|", $enum);
                        $val = $draw[0];
                        $lab = $draw[1];
                    } else {
                        $val = $lab = $enum;
                    }

                    $select = ($value == $val) ? "selected" : "";
                    ?>
                    <option {{$select}} value='{{$val}}'>{{$lab}}</option>
                @endforeach
            @endif

            @if(@$form['datatable'])
                @if(@$form['relationship_table'])
                    <?php
                    $select_table = explode(',', $form['datatable'])[0];
                    $select_title = explode(',', $form['datatable'])[1];
                    $select_where = @$form['datatable_where'];
                    $pk = BTBooster::findPrimaryKey($select_table);

                    $result = DB::table($select_table)->select($pk, $select_title);
                    if ($select_where) {
                        $result->whereraw($select_where);
                    }
                    $result = $result->orderby($select_title, 'asc')->get();

                    if(@$form['datatable_orig'] != ''){
                        $params = explode("|", @$form['datatable_orig']);
                        if(!isset($params[2])) $params[2] = "id";
                        $value = DB::table($params[0])->where($params[2], $id)->first()->{$params[1]};
                        $value = explode(",", $value);
                    } else {
                        $foreignKey = BTBooster::getForeignKey($table, @$form['relationship_table']);
                        $foreignKey2 = BTBooster::getForeignKey($select_table, @$form['relationship_table']);
                        $value = DB::table(@$form['relationship_table'])->where($foreignKey, @$id);
                        $value = $value->pluck($foreignKey2)->toArray();
                    }

                    foreach ($result as $r) {
                        $option_label = $r->{$select_title};
                        $option_value = $r->id;
                        $selected = (is_array($value) && in_array($r->$pk, $value)) ? "selected" : "";
                        echo "<option $selected value='$option_value'>$option_label</option>";
                    }
                    ?>
                @else
                    @if(@$form['datatable_ajax'] == false)
                        <option value=''>{{cbLang('text_prefix_option')}} {{$form['label']}}</option>
                        <?php
                        $select_table = explode(',', $form['datatable'])[0];
                        $select_title = explode(',', $form['datatable'])[1];
                        $select_where = @$form['datatable_where'];
                        $datatable_format = @$form['datatable_format'];
                        $select_table_pk = BTBooster::findPrimaryKey($select_table);
                        $result = DB::table($select_table)->select($select_table_pk, $select_title);
                        if ($datatable_format) {
                            $result->addSelect(DB::raw("CONCAT(".$datatable_format.") as $select_title"));
                        }
                        if ($select_where) {
                            $result->whereraw($select_where);
                        }
                        if (BTBooster::isColumnExists($select_table, 'deleted_at')) {
                            $result->whereNull('deleted_at');
                        }
                        $result = $result->orderby($select_title, 'asc')->get();

                        foreach ($result as $r) {
                            $option_label = $r->{$select_title};
                            $option_value = $r->$select_table_pk;
                            $selected = ($option_value == $value) ? "selected" : "";
                            echo "<option $selected value='$option_value'>$option_label</option>";
                        }
                        ?>
                    <!--end-datatable-ajax-->
                    @endif

                <!--end-relationship-table-->
                @endif

            <!--end-datatable-->
            @endif
        </select>
        </div>
        <div class="text-danger">
            {!! $errors->first($name)?"<i class='fa fa-info-circle'></i> ".$errors->first($name):"" !!}
        </div><!--end-text-danger-->
        <p class='help-block'>{{ @$form['help'] }}</p>

    </div>
</li>

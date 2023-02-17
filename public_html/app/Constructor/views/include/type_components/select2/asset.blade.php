{{---
@push('head')
    {{---
    <link rel='stylesheet' href='<?php echo asset("vendor/crudbooster/assets/select2/dist/css/select2.min.css")?>'/>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'/>

    <link rel='stylesheet' href='<?php echo asset("vendor/crudbooster/assets/select2/dist/css/select2.min.css")?>'/>
    <style type="text/css">
        @tailwind base;

        @tailwind components;


        .select2-container {
            /* The container where the selectbox is housing*/
            @apply relative box-border align-middle inline-block m-0 mb-2;
        }
        .select2-container .select2-selection--single {
            /* Selection box itself */
            @apply box-border cursor-pointer block select-none shadow border rounded;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            /* DIV inside Selection box with text, clear button and arrow down*/
            @apply block h-6 pl-1 pr-6 truncate;
        }
        .select2-container .select2-selection--single .select2-selection__clear {
            /* The DIV where the X is housing to clear the chosen option */
            @apply relative -m-1;
        }
        .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
            /*@apply;*/
        }

        .select2-container .select2-selection--multiple {
            @apply box-border overflow-hidden h-4 cursor-pointer block select-none;
        }

        .select2-container .select2-selection--multiple .select2-selection__rendered {
            @apply inline-block pl-2 truncate whitespace-nowrap;
        }
        .select2-container .select2-search--inline {
            /* Search box*/
            @apply float-left;
        }
        .select2-container .select2-search--inline .select2-search__field {
            @apply box-border border dark:border-gray-600 pl-1 my-1 w-full text-base;
        }
        .select2-container .select2-search--inline .select2-search__field::-webkit-search-cancel-button {
            @apply appearance-none;
        }

        .select2-dropdown {
            /* Dropdown area after the arrow down is clicked */
            @apply absolute block w-auto box-border bg-white dark:bg-slate-700 border-solid border border-gray-200 z-50 float-left;
        }

        .select2-results {
            @apply block text-black dark:text-gray-300;
        }

        .select2-results__options {
            @apply list-none m-0 p-0;
        }

        .select2-results__option {
            /* The rows with results that you see after dropdown.
            Increase p-1 to p-2 to have more space between results */
            @apply p-1 select-none;
        }
        .select2-results__option[aria-selected] {
            @apply cursor-pointer;
        }

        .select2-container--open .select2-dropdown {
            /* Dropdown container opened and results are shown*/
            @apply mt-3 left-0;
        }

        .select2-container--open .select2-dropdown--above {
            /* The left and right borders of the option rows */
            @apply rounded border-gray-400 dark:border-gray-700 shadow;
        }

        .select2-container--open .select2-dropdown--below {
            /* The left and right borders of the option rows */
            @apply rounded border-gray-400 dark:border-gray-700 shadow;
        }

        .select2-search--dropdown {
            /* Search dropdown element*/
            @apply block p-2;
        }
        .select2-search--dropdown .select2-search__field {
            /* Search box itself where you can enter text*/
            @apply h-10 p-1 bg-white dark:bg-slate-500 box-border rounded border-2 border-blue-300 dark:border-gray-700 dark:text-gray-200 outline-none;
            width: 100%;
        }
        .select2-search--dropdown .select2-search__field::-webkit-search-cancel-button {
            @apply appearance-none;
        }
        .select2-search--dropdown.select2-search--hide {
            @apply hidden;
        }

        .select2-close-mask {
            @apply block w-12 min-w-full m-0 p-0;
            border: 0;
            position: fixed;
            left: 0;
            top: 0;
            min-height: 100%;
            height: auto;
            width: auto;
            opacity: 0;
            z-index: 99;
            background-color: #fff;
            filter: alpha(opacity=0);
        }

        .select2-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            -webkit-clip-path: inset(50%) !important;
            clip-path: inset(50%) !important;
            height: 1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
            white-space: nowrap !important; }


        /*
            Default template settings:
        */

        .select2-container--default .select2-selection--single {
            /* Selection bar - Self */
            @apply p-2 h-10 bg-white dark:bg-slate-700 border border-solid dark:border-gray-700;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            /* Selection bar - Text color of the item you selected from the results */
            @apply text-gray-700 dark:text-gray-200;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            /* Selection bar - Clear button - If this property is enabled*/
            @apply cursor-pointer float-right text-red-700;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            /* Selection bar - Color of the placeholder text before selection - If this property is enabled */
            @apply text-gray-600 dark:text-gray-300;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            /* Selection bar - DIV where the arrow pointing down is living*/
            @apply absolute right-0 top-0 h-10 w-8;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            /* Arrow is a CSS triangle that does not exists in Tailwind without a package */
            @apply absolute border-solid h-0 w-0 border-t-4 border-r-4 border-b-0 border-l-4;
            border-color: #000 transparent transparent transparent;
            left: 50%;
            margin-left: -4px;
            margin-top: -2px;
            top: 50%;
        }

        .select2-container--default[dir="rtl"] .select2-selection--single .select2-selection__clear {
            /* Selection bar - Clear button - If this property is enabled from right to left*/
            @apply float-left ml-4;
        }

        .select2-container--default[dir="rtl"] .select2-selection--single .select2-selection__arrow {
            /* Placement of the dropdown arrow when in rtl mode */
            @apply left-0 right-auto;
        }

        .select2-container--default.select2-container--disabled .select2-selection--single {
            /* Selection by property disabled*/
            @apply cursor-default bg-gray-300;
        }
        .select2-container--default.select2-container--disabled .select2-selection--single .select2-selection__clear {
            /* Selection bar - Hide the clear cross when selection bar is disabled*/
            @apply hidden;
        }

        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #888 transparent;
            border-width: 0 4px 5px 4px;
        }

        .select2-container--default .select2-selection--multiple {
            @apply p-1 min-h-full h-full border border-solid dark:border-gray-700 rounded shadow bg-white dark:bg-slate-700;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            @apply box-border list-none m-0 px-1 min-w-full;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered li {
            @apply list-none;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__clear {
            @apply float-right cursor-pointer mt-1 mr-2 p-1;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            @apply bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-200 border cursor-default rounded my-1 mr-1 px-2 float-left;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            @apply text-gray-700 dark:text-gray-200 cursor-pointer inline-block mr-1;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            @apply text-gray-700 dark:text-gray-200;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            @apply border-2 outline-none;
        }

        .select2-container--default.select2-container--disabled .select2-selection__choice__remove {
            @apply hidden;
        }
        .select2-container--classic .select2-selection--multiple .select2-selection__choice {
            @apply bg-gray-300 border-2 dark:border-gray-700 shadow rounded float-left cursor-default mt-1 mr-1 px-1;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            /* The border of the search textbox */
            @apply border-solid;
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            /* Search textbox */
            @apply border-none bg-transparent outline-none shadow-none select-text;
        }

        .select2-container--default .select2-results > .select2-results__options {
            /* Hight of the dropdown zone where the options or results are visible */
            @apply h-full max-h-32 overflow-y-auto;
        }

        .select2-container--default .select2-results__option[role=group] {
            /* No clue what this does */
            @apply p-0;
        }

        .select2-container--default .select2-results__option[aria-disabled=true] {
            @apply text-gray-700;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            /* The already selected option row color */
            @apply bg-gray-300 dark:text-gray-700;
        }
        .select2-results__option--selected {
            @apply hidden;
        }
        .select2-container--default .select2-results__option .select2-results__option {
            padding-left: 1em;
        }
        .select2-container--default .select2-results__option .select2-results__option .select2-results__group {
            padding-left: 0;
        }
        .select2-container--default .select2-results__option .select2-results__option .select2-results__option {
            margin-left: -1em;
            padding-left: 2em;
        }
        .select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option {
            margin-left: -2em;
            padding-left: 3em;
        }
        .select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option {
            margin-left: -3em;
            padding-left: 4em;
        }
        .select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option {
            margin-left: -4em;
            padding-left: 5em;
        }
        .select2-container--default .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option .select2-results__option {
            margin-left: -5em;
            padding-left: 6em;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            /* Background color and text color of the options rows when select is open */
            @apply bg-gray-100 dark:bg-gray-500 text-gray-700 dark:text-gray-200;
        }

        .select2-container--default .select2-results__group {
            /* Have no idea what this is for */
            @apply cursor-default block;
            padding: 6px; }


        @tailwind utilities;
    </style>
@endpush
@push('bottom')
    <script src='//code.jquery.com/jquery-3.6.1.min.js'></script>
@endpush
@push('bottom')
    <script src='<?php echo asset("vendor/crudbooster/assets/select2/dist/js/select2.full.min.js")?>'></script>
@endpush

@push('bottom')
<script>$('select').select2();</script>
@endpush
--}}


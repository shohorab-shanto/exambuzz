<!DOCTYPE html>
<html lang="zxx">

<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title') - {{ $company->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset($company->favicon) }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap1.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/themefy_icon/themify-icons.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/niceselect/css/nice-select.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/owl_carousel/css/owl.carousel.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/gijgo/gijgo.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/font_awesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/vendors/tagsinput/tagsinput.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/datepicker/date-picker.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/vendors/vectormap-home/vectormap-2.0.2.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/scroll/scrollable.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/datatable/css/jquery.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/vendors/datatable/css/responsive.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/vendors/datatable/css/buttons.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/vendors/scroll/scrollable.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/vendors/text_editor/summernote-bs4.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/vendors/morris/morris.css') }}">

    <link rel="stylesheet" href="{{ asset('backend/vendors/material_icon/material-icons.css') }}" />

    <link rel="stylesheet" href="{{ asset('backend/css/metisMenu.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .all_exam_list {
            display: none;
        }
        /* public/css/loader.css */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loader::after {
            content: '';
            width: 50px;
            height: 50px;
            border: 6px solid #3498db;
            border-radius: 50%;
            border-top: 6px solid #fff;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('backend/css/style1.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/css/colors/default.css') }}" id="colorSkinCSS">

    @yield('css')
</head>

<body class="crm_body_bg">
    <div id="loader" class="loader"></div>

    @include('backend.layouts.partials._sidebar')

    <section class="main_content dashboard_part large_header_bg">

        @include('backend.layouts.partials._header')

        <div class="main_content_iner overly_inner ">
            <div class="container-fluid p-0 ">

                @yield('content')

            </div>
        </div>

        @include('backend.layouts.partials._footer')
    </section>

    <div id="back-top" style="display: none;">
        <a title="Go to Top" href="#">
            <i class="ti-angle-up"></i>
        </a>
    </div>

    <script src="{{ asset('backend/js/jquery1-3.4.1.min.js') }}"></script>

    <script src="{{ asset('backend/js/popper1.min.js') }}"></script>

    <script src="{{ asset('backend/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('backend/js/metisMenu.js') }}"></script>

    <script src="{{ asset('backend/vendors/count_up/jquery.waypoints.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/chartlist/Chart.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/count_up/jquery.counterup.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/niceselect/js/jquery.nice-select.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/owl_carousel/js/owl.carousel.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/jszip.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/datatable/js/buttons.print.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/datepicker/datepicker.js') }}"></script>
    <script src="{{ asset('backend/vendors/datepicker/datepicker.en.js') }}"></script>
    <script src="{{ asset('backend/vendors/datepicker/datepicker.custom.js') }}"></script>
    <script src="{{ asset('backend/js/chart.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/chartjs/roundedBar.min.js') }}"></script>

    <script src="{{ asset('backend/vendors/progressbar/jquery.barfiller.js') }}"></script>

    <script src="{{ asset('backend/vendors/tagsinput/tagsinput.js') }}"></script>

    <script src="{{ asset('backend/vendors/text_editor/summernote-bs4.js') }}"></script>
    <script src="{{ asset('backend/vendors/am_chart/amcharts.js') }}"></script>

    <script src="{{ asset('backend/vendors/scroll/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/scroll/scrollable-custom.js') }}"></script>

    <script src="{{ asset('backend/vendors/vectormap-home/vectormap-2.0.2.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/vectormap-home/vectormap-world-mill-en.js') }}"></script>

    <script src="{{ asset('backend/vendors/apex_chart/apex-chart2.js') }}"></script>
    <script src="{{ asset('backend/vendors/apex_chart/apex_dashboard.js') }}"></script>
    <script src="{{ asset('backend/vendors/echart/echarts.min.js') }}"></script>
    <script src="{{ asset('backend/vendors/chart_am/core.js') }}"></script>
    <script src="{{ asset('backend/vendors/chart_am/charts.js') }}"></script>
    <script src="{{ asset('backend/vendors/chart_am/animated.js') }}"></script>
    <script src="{{ asset('backend/vendors/chart_am/kelly.js') }}"></script>
    <script src="{{ asset('backend/vendors/chart_am/chart-custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('sweetalert::alert')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // public/js/loader.js

        document.onreadystatechange = function() {
            if (document.readyState === 'complete') {
                // Hide the loader when the page is fully loaded
                document.getElementById('loader').style.display = 'none';
            }
        };
    </script>
    <script src="{{ asset('backend/js/dashboard_init.js') }}"></script>
    <script src="{{ asset('backend/js/custom.js') }}"></script>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $('.select2').select2({
            placeholder: 'Select an option',
        });

        $('.m_select2').select2({
            tokenSeparators: [',', ' ']
        });
        $('.summernote').summernote({});
    </script>
    @yield('js')
</body>


</html>

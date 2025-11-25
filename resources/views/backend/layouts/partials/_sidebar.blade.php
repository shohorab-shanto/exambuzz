{{-- <style>
    .sidebar {
        width: 350px;
    }

    .main_content {
        padding-left: 350px;
    }
</style> --}}

<nav class="sidebar vertical-scroll  ps-container ps-theme-default ps-active-y">
    <div class="logo d-flex justify-content-between">
        <a href="{{ route('dashboard') }}"><img src="{{ asset($company->logo) }}" style="height: 40px;
            width: 180px;"></a>
        <div class="sidebar_close_icon d-lg-none">
            <i class="ti-close"></i>
        </div>
    </div>
    <ul id="sidebar_menu">
        <li class="mm-active">
            <a href="{{ route('dashboard') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/dashboard.svg') }}" alt>
                </div>
                <span>Dashboard</span>
            </a>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Package Info</span>
            </a>
            <ul>
                <li><a href="{{ route('packages.index') }}">Package List</a></li>
                <li><a href="{{ route('packages.active_package') }}">Active Package</a></li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Users</span>
            </a>
            <ul>
                <li><a href="{{ route('admin.index') }}">Admin</a></li>
                <li>
                    <a href="{{ route('teacher.index') }}">Teacher</a>
                </li>
                <li>
                    <a href="{{ route('students') }}">Students</a>
                </li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Written Permission</span>
            </a>
            <ul>
                <li><a href="{{ route('teacher.written.index', ['ref' => 'BCS', 'type' => 'Written']) }}"
                       class="text-info">BCS</a></li>

                <li><a href="{{ route('teacher.written.index', ['ref' => 'Bank', 'type' => 'Written']) }}"
                       class="text-info">Bank</a></li>

                <li>
                    <a href="{{ route('teacher.written.index', ['ref' => 'Others', 'type' => 'Written', 'child' => '11 to 20 Grade']) }}"
                       class="text-info">
                        Teacher & Lecturer
                    </a>
                </li>

                <li>
                    <a href="{{ route('teacher.written.index', ['ref' => 'Others', 'type' => 'Written', 'child' => 'Job Solution']) }}"
                       class="text-info">Job
                        Solution</a></li>

                <li>
                    <a href="{{ route('teacher.written.index', ['ref' => 'Free', 'type' => 'Written', 'child' => 'Weekly']) }}"
                       class="text-info">Weekly</a></li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>BCS</span>
            </a>
            <ul>
                <li><a href="{{ route('exam.index', ['ref' => 'BCS', 'type' => 'Preliminary']) }}"
                       class="text-success">Preliminary Exam
                        List</a></li>
                <li><a href="{{ route('exam.written', ['ref' => 'BCS', 'type' => 'Written']) }}"
                       class="text-info">Written Exam
                        List</a></li>
                <hr>
                <li><a href="{{ route('exam.syllabus', ['ref' => 'BCS', 'type' => 'Preliminary']) }}"
                       class="text-success">Preliminary
                        Syllabus</a></li>
                <li><a href="{{ route('exam.syllabus', ['ref' => 'BCS', 'type' => 'Written']) }}"
                       class="text-info">Written
                        Syllabus</a></li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Bank</span>
            </a>
            <ul>
                <li><a href="{{ route('exam.index', ['ref' => 'Bank', 'type' => 'Preliminary']) }}"
                       class="text-success">Preliminary Exam
                        List</a></li>
                <li><a href="{{ route('exam.written', ['ref' => 'Bank', 'type' => 'Written']) }}"
                       class="text-info">Written Exam
                        List</a></li>
                <hr>
                <li><a href="{{ route('exam.syllabus', ['ref' => 'Bank', 'type' => 'Preliminary']) }}"
                       class="text-success">Preliminary
                        Syllabus</a></li>
                <li><a href="{{ route('exam.syllabus', ['ref' => 'Bank', 'type' => 'Written']) }}"
                       class="text-info">Written
                        Syllabus</a></li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Others</span>
            </a>
            <ul>
                <li>
                    <a href="{{ route('exam.index', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Primary']) }}"
                       class="text-success">
                        Primary Preliminary Exam
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.index', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => '11 to 20 Grade']) }}"
                       class="text-success">
                        Teacher & Lecturer Preliminary Exam
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.index', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Non-Cadre']) }}"
                       class="text-success">
                        Non-Cadre Preliminary Exam
                    </a>
                </li>

                 <li>
                    <a href="{{ route('exam.written', ['ref' => 'Others', 'type' => 'Written', 'child' => 'Non-Cadre']) }}"
                       class="text-success">
                        Non-Cadre Written Exam
                    </a>
                </li>

                <li>
                    <a href="{{ route('exam.index', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Job Solution']) }}"
                       class="text-success">
                        Job Solution Preliminary Exam
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.written', ['ref' => 'Others', 'type' => 'Written', 'child' => '11 to 20 Grade']) }}"
                       class="text-info">
                        Teacher & Lecturer Written Exam
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.written', ['ref' => 'Others', 'type' => 'Written', 'child' => 'Job Solution']) }}"
                       class="text-info">Job
                        Solution Written
                        Exam</a></li>



                <li>
                    <a href="{{ route('exam.index', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Petrobangla']) }}"
                       class="text-info">Petrobangla Preliminary Exam</a></li>

                <li>
                    <a href="{{ route('exam.written', ['ref' => 'Others', 'type' => 'Written', 'child' => 'Petrobangla']) }}"
                       class="text-info">Petrobangla Written Exam</a></li>

                <!-- <li>
                    <a href="{{ route('exam.written', ['ref' => 'Others', 'type' => 'Written', 'child' => 'si']) }}"
                       class="text-info">SI Written Exam</a></li> -->



                <hr>

                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Primary']) }}"
                       class="text-success">
                        Primary Preliminary Syllabus
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => '11 to 20 Grade']) }}"
                       class="text-success">
                        Teacher & Lecturer Preliminary Syllabus
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Non-Cadre']) }}"
                       class="text-success">
                        Non-Cadre Preliminary Syllabus
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Others', 'type' => 'Preliminary', 'child' => 'Job Solution']) }}"
                       class="text-success">
                        Job Solution Preliminary Syllabus
                    </a>
                </li>
                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Others', 'type' => 'Written', 'child' => 'Job Solution']) }}"
                       class="text-info">Job
                        Solution Written
                        Syllabus</a></li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Free</span>
            </a>
            <ul>
                <li><a href="{{ route('exam.index', ['ref' => 'Free', 'type' => 'Preliminary', 'child' => 'Weekly']) }}"
                       class="text-success">Weekly
                        Preliminary
                        Exam</a></li>
                <li><a href="{{ route('exam.index', ['ref' => 'Free', 'type' => 'Preliminary', 'child' => 'Daily']) }}"
                       class="text-success">Daily
                        Preliminary
                        Exam</a></li>
                <li><a href="{{ route('exam.written', ['ref' => 'Free', 'type' => 'Written', 'child' => 'Weekly']) }}"
                       class="text-info">Weekly
                        Written
                        Exam</a></li>
                <hr>
                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Free', 'type' => 'Preliminary', 'child' => 'Weekly']) }}"
                       class="text-success">Weekly
                        Preliminary
                        Syllabus</a></li>
                <li>
                    <a href="{{ route('exam.syllabus', ['ref' => 'Free', 'type' => 'Preliminary', 'child' => 'Daily']) }}"
                       class="text-success">Daily
                        Preliminary
                        Syllabus</a></li>
                <li><a href="{{ route('exam.syllabus', ['ref' => 'Free', 'type' => 'Written', 'child' => 'Weekly']) }}"
                       class="text-info">Weekly
                        Written
                        Syllabus</a></li>
            </ul>
        </li>

        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Material</span>
            </a>
            <ul>
                <li><a href="{{ route('material.index', ['ref' => 'BCS']) }}" class="text-success">BCS</a></li>
                <li><a href="{{ route('material.index', ['ref' => 'Bank']) }}" class="text-success">Bank</a></li>
                <li><a href="{{ route('material.index', ['ref' => 'Recent']) }}" class="text-success">Routine</a>
                </li>
                <li><a href="{{ route('material.index', ['ref' => 'Record Class']) }}" class="text-success">Record
                        Class</a></li>

                <li>
                    <a href="{{ route('material.folder.index') }}" class="text-success">
                        Material Folder
                    </a>
                </li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Withdrawal Request</span>
            </a>

            <ul>
                <li><a href="{{ route('teacher.withdrawalRequest', ['ref' => 'Pending']) }}"
                       class="text-success">Pending
                        ({{ DB::table('wallet_histories')->where('status', 'Pending')->count() }})</a></li>
                <li><a href="{{ route('teacher.withdrawalRequest', ['ref' => 'Paid']) }}"
                       class="text-success">Paid({{ DB::table('wallet_histories')->where('status', 'Paid')->count() }}
                        )</a>
                </li>
                <li><a href="{{ route('teacher.withdrawalRequest', ['ref' => 'Declined']) }}"
                       class="text-success">Declined({{ DB::table('wallet_histories')->where('status', 'Declined')->count() }}
                        )</a>
                </li>
            </ul>
        </li>

        <li class="mm-active">
            <a href="{{ route('studentRequest') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Student Request</span>
            </a>
        </li>

        <li class="mm-active">
            <a href="{{ route('notice-board.index') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Notice Board</span>
            </a>
        </li>

        <li class="mm-active">
            <a href="{{ route('class-routine.index') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Class Routine</span>
            </a>
        </li>

        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Topics Wise Revision</span>
            </a>
            <ul>
                <li><a href="{{ route('revision_subject.index') }}">Subject</a></li>
                <li><a href="{{ route('revision_topic.source.index') }}">Topic & Source</a></li>
            </ul>
        </li>

        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Settings</span>
            </a>
            <ul>
                <li><a href="{{ route('subject.index') }}">Subject</a></li>
                <li><a href="{{ route('topic.source.index') }}">Topic & Source</a></li>
            </ul>
        </li>
        <li class>
            <a class="has-arrow" href="#" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('backend/img/menu-icon/2.svg') }}" alt>
                </div>
                <span>Website Info</span>
            </a>
            <ul>
                <li><a href="{{ route('page.index') }}">Pages</a></li>
                <li><a href="{{ route('showCompanyInfo') }}">Company Info</a></li>
            </ul>
        </li>
    </ul>
</nav>

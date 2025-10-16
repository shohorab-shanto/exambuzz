<div class="container-fluid g-0">
    <div class="row">
        <div class="col-lg-12 p-0">
            <div class="header_iner d-flex justify-content-between align-items-center">
                <div class="sidebar_icon d-lg-none">
                    <i class="ti-menu"></i>
                </div>
                <div class="serach_field-area d-flex align-items-center">
                    <div class="search_inner">
                        {{-- <form action="#">
                            <div class="search_field">
                                <input type="text" placeholder="Search here...">
                            </div>
                            <button type="submit"> <img src="{{ asset('backend/img/icon/icon_search.svg') }}" alt>
                            </button>
                        </form> --}}
                    </div>
                    <span class="f_s_14 f_w_400 ml_25 white_text text_white">Apps</span>
                </div>
                <div class="header_right d-flex justify-content-between align-items-center">
                    <div class="profile_info">
                        <img src="{{ asset(auth()->guard('admin')->user()->image ?? '') }}" alt="#">
                        <div class="profile_info_iner" style="width: 270px;">
                            <div class="profile_author_name">
                                <h5>{{ auth()->guard('admin')->user()->name ?? '' }}</h5>
                                <small class="text-light">{{ auth()->user()->email }}</small>
                            </div>
                            <div class="profile_info_details" style="border: 1px solid #567aed;border-radius: 0 0 10px 15px;">
                                <form action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm"
                                        style="width: 100%;">Logout</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

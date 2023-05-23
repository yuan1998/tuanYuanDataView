@if($user)
    <li class="dropdown dropdown-user nav-item">
        <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
            <div class="user-nav d-sm-flex d-none">
                <span class="user-name text-bold-600">{{ $user->name }}</span>
                <span class="user-status">
                    <i class="fa fa-circle status-icon {{$user->online ? 'text-success': 'text-dark'}}"></i>
                    <span class="status-text">{{$user->online? '在线' : '离线'}}</span>
                </span>
            </div>

            <span>
            <img class="round" src="{{ $user->getAvatar() }}" alt="avatar" height="40" width="40"/>
        </span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ admin_url('auth/setting') }}" class="dropdown-item">
                <i class="feather icon-user"></i> {{ trans('admin.setting') }}
            </a>
            <a href="" class="switch-user-online dropdown-item">
                <i class="feather icon-user"></i> 切换登录状态 :
                <span class="status-text">{{!$user->online? '在线' : '离线'}}</span>
            </a>

            <div class="dropdown-divider"></div>

            <a class="dropdown-item" href="{{ admin_url('auth/logout') }}">
                <i class="feather icon-power"></i> {{ trans('admin.logout') }}
            </a>
        </div>
    </li>
    <script>
        Dcat.ready(() => {
            Dcat.init('.switch-user-online', function ($this, id) {
                $this.off('click').on('click', ($event) => {
                    $event.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: '{{admin_url('/auth/switch-online')}}',
                        data: {
                            _method: 'PUT'
                        }
                    })
                        .then((response) => {
                            if (response.status) {
                                let status = response?.data?.online_status;
                                $('.status-icon')
                                    .toggleClass('text-success', status === true)
                                    .toggleClass('text-dark', status === false);
                                $('.status-text').text(status ? '在线' : '离线')
                            }
                            Dcat.handleJsonResponse(response);
                            console.log('response :', response);
                        });
                });
            });
        })
    </script>
@endif

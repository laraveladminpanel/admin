<footer class="app-footer">
    <div class="site-footer-right">
        @if (rand(1,100) == 100)
            <i class="admin-rum-1"></i> {{ __('admin.theme.footer_copyright2') }}
        @else
            {!! __('admin.theme.footer_copyright') !!} <a href="http://laraveladminpanel.com" target="_blank">Laravel Admin Panel</a>
        @endif
        @php $version = Admin::getVersion(); @endphp
        @if (!empty($version))
            - {{ $version }}
        @endif
    </div>
</footer>

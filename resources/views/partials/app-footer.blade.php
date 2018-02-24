<footer class="app-footer">
    <div class="site-footer-right">
        {!! __('admin.theme.footer_copyright') !!} <a href="http://laraveladminpanel.com" target="_blank">Laravel Admin Panel</a>
        @php $version = Admin::getVersion(); @endphp
        @if (!empty($version))
            - {{ $version }}
        @endif
    </div>
</footer>

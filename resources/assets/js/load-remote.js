// src URL for news and other Admin Panel related stuff
var remote_src_url = 'https://s3.amazonaws.com/laraveladmin/admin.js';

loadAdminPanelRemoteJS(remote_src_url);

function loadAdminPanelRemoteJS(url)
{
    // dynamically Load the script if it exists
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.setAttribute("async", "");
    script.src = url;
    document.body.appendChild(script);
}
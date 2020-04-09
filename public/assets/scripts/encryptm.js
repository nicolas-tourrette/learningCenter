function cryptm(m, o, n) {
    if (!document.write) {
        return false;
    }
    else {
        var m; var o; var n;
        document.write('<a style="text-decoration: none;" href="' + 'mailto:' + m + '@' + o + '.' + n + '">' + m + '@' + o + '.' + n + ' <i class="fas fa-lock text-success"></i></a>');
    }
}
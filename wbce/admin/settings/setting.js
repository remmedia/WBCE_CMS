function change_os(type) {
    var visible = type === 'linux' ? 'block' : 'none';
    ['file_perms_box1', 'file_perms_box2', 'file_perms_box3'].forEach(function (id) {
        var element = document.getElementById(id);
        if (element) element.style.display = visible;
    });
}

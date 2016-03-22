/**
 * Created by 旭海 on 2016/3/11.
 */
function load_aside() {
    var is_fold = 0;
    if (window.localStorage.length > 0) {
        is_fold = window.localStorage.is_fold;
        if (is_fold == "1") {
            is_fold = 0;
            window.localStorage.is_fold = is_fold;
        }
        else {
            is_fold = 1;
            window.localStorage.is_fold = 1;
        }
    }
    else {
        is_fold = 0;
        window.localStorage.is_fold = 0;
    }
    //根据跟新后的is_fold值更新页面显示
    if (is_fold) {
        //若aside控件未折叠则使其折叠
        if (!($('#aside').hasClass('folded'))) {
            $('#aside').addClass('folded');
        }
    }
    else {
        //若aside控件折叠则使其不折叠
        if ($('#aside').hasClass('folded')) {
            $('#aside').removeClass('folded');
        }
    }
}
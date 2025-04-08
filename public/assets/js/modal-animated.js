// 


"use strict";
function testAnim(isEntrance) {
    if (isEntrance) {
        $('.modal .modal-dialog').attr('class', 'modal-dialog bounceIn animated');
    } else {
        $('.modal .modal-dialog').attr('class', 'modal-dialog flipOutY animated');
    }
};

var modal_animate_custom = {
    init: function() {
        $('#myModal').on('show.bs.modal', function (e) {
            testAnim(true); // Always apply "bounceIn" when showing
        });
        $('#myModal').on('hide.bs.modal', function (e) {
            testAnim(false); // Always apply "fadeOut" when hiding
        });
        $("a").tooltip();
    }
};

(function($) {
    "use strict";
    modal_animate_custom.init();
})(jQuery);

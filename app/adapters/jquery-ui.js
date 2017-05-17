import 'jquery-ui-dist/jquery-ui';

//no conflict with bootstrap
$.widget.bridge('uibutton', $.ui.button);
$.widget.bridge('uitooltip', $.ui.tooltip);

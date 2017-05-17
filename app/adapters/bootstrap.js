import "./jquery-ui";
import "bootstrap";
import "jstack";

//based on data-href (agnostic)
//based on data-jview jstack.mvc

jstack.onLoad('[data-toggle-jbs="modal"][data-jview]', function(){
	let el = $(this);
	
    let targetSelector = el.attr('data-target');
    let modal = $(targetSelector);

    el.on('click',function(){
        modal.one('show.bs.modal', function(e){
            let link = el;
            let view = link.attr('data-jview');
            let inherit = link.attr('data-jview-inherit') || link.hasAttr('data-jview-inherit');
            //console.log(inherit);

            let div = '<div class="modal-dialog modal-lg"><div class="modal-content"';
            if(inherit===true){
                div += ' j-view-inherit';
            }
            else if(inherit){
                div += ' j-view-inherit="'+inherit+'"';
            }
            div += '></div></div>';
			
			modal.empty();
            jstack.load($(div).appendTo(modal).find('.modal-content'),{
				component: view,
			});
        });
        modal.modal('show');

	});
});


let loadTab = function(el){
	let $el = $(el);
	let targetSelector = $el.attr('href');
	let view = $el.attr('data-jview');
	let inherit = $el.attr('data-jview-inherit') || $el.hasAttr('data-jview-inherit');
	let div = $('<div/>');
	if(inherit){
		div.attr('j-view-inherit', inherit===true?'':inherit );
	}
	let target = $(targetSelector);
	target.html(div);
	jstack.load(div,{
		component: view,
	});
};

$(document).on('shown.bs.tab', '[data-toggle="tab"][data-jview]', function(){
	loadTab(this);
});
//$(document).on('hide.bs.tab', '[data-toggle="tab"][data-jview]', function(e){
	//var targetSelector = $(this).attr('href');
	//var tab = $(targetSelector);
	//tab.empty();
//});
jstack.onLoad('[data-toggle="tab"][data-jview]', function(){
	if($(this).parent().hasClass('active')){
		loadTab(this);
	}
});


(function(){
    $('#addoutsourcing').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        var data = $(document.body).data('j-model').outsourcing;
        form.on('click','.resend-mail',function(){
            var requestId = $(this).attr('data-request-id');
            $serviceJSON('outsourcing/outsourcing','resendMail',[requestId],function(ok){
                if(ok){
                    $('.resend-ok',form).fadeIn();
                }
            });
        });
        $.ajax({
            url: 'outsourcing/outsourcing',
            type: 'post',
            data: {method: 'addOutsourcing', params: [data]},
            success: function(html){
                form.fadeOut(function(){
                    form.html(html);
                    form.fadeIn();
                });
            }
        });
        return false;
    });
})();
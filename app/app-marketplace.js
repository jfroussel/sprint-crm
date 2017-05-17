(function(){
    $('#addmarketplace').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        var data = $(document.body).data('j-model').marketplace;
        form.on('click','.resend-mail',function(){
            var requestId = $(this).attr('data-request-id');
            $serviceJSON('marketplace/marketplace','resendMail',[requestId],function(ok){
                if(ok){
                    $('.resend-ok',form).fadeIn();
                }
            });
        });
        $.ajax({
            url: 'marketplace/marketplace',
            type: 'post',
            data: {method: 'addMarketplace', params: [data]},
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
jQuery(document).ready(function($){
    function mec_get_url_parameters(url) {
        var index = (url+'').indexOf('?', 0);
        var sPageURL = url.substring(index+1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
        var data = {
            action: 'total-booking-get-reports'
        };
        
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');            
            data[sParameterName[0]] = sParameterName[1] === undefined ? '' : decodeURIComponent(sParameterName[1]);
            
        }
        
        return data;
    }

    var mec_sort_links = '.mec-chart-this-month a , .mec-chart-last-month a , .mec-chart-this-year a , .mec-chart-last-year a';

    function mec_mark_active_sort() {
        var current = mec_get_url_parameters(window.location.href);
        var sort = current.sort || 'this_month';
        $(mec_sort_links).removeClass('active').each(function(){
            var link = mec_get_url_parameters($(this).attr('href'));
            if (link.sort === sort) $(this).addClass('active');
        });
    }

    mec_mark_active_sort();

    $(mec_sort_links).on('click',function(e){
        $(e.currentTarget).parents('.w-box-content').find(' ul li').removeClass('active');
        $(e.currentTarget).parents('.w-box-content').find(' ul li a').removeClass('active');
        $(e.currentTarget).addClass('active');
        var url = $(e.currentTarget).attr('href');
        var data = mec_get_url_parameters(url);

        var form = $('.mec-sells-filter');
        var selector;
        $.each(data,function(i,v){
            selector = '[name="'+i+'"]';
            if($(selector,form).length){                
                if($(selector,form).is('SELECT')){
                    $(selector,form).val(v);
                }else{                    
                    $(selector,form).val(v);
                }
            }
        });
        $('#mec-total-booking-report').css('opacity','.5');
        $.post(
            mec_ajax_data.ajaxurl,
            data,
            function(r){
                $('#mec-total-booking-report').html(r);                
                $('#mec-total-booking-report').css('opacity','1');
            }
        );
        
        return false;
    });

    $('.mec-sells-filter').on('submit',function(){        
        var fdata = $(this).serializeArray();
        var data = {
            action: 'total-booking-get-reports'
        };
        $.each(fdata,function(i,v){        
            data[v['name']] = v['value'];
        });        
        
        $('#mec-total-booking-report').css('opacity','.5');
        $.post(
            mec_ajax_data.ajaxurl,
            data,
            function(r){
                $('#mec-total-booking-report').html(r);                
                $('#mec-total-booking-report').css('opacity','1');
            }
        );
        
        return false;
    });
});
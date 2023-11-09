$(document).ready(function(){
    
    var dopmat = {};
    
    // выделение активной ссылки в меню уроков
    $('.basic-part a[href="#video"], .advance-part a[href="#video"]').click(function(){        
        $('.basic-part a.active, .advance-part a.active').removeClass('active');
        $(this).addClass('active');
    });
    
    var prevNext = function(a){
        
        if(!a || typeof a != 'object'){
            return;
        }
        
        if(a.next('a[href="#video"]').length || a.next().next('a[href="#video"]').length){
            $('a.next-lesson').show();
        } else {
            $('a.next-lesson').hide();
        }
        
        if(a.prev('a[href="#video"]').length || a.prev().prev('a[href="#video"]').length){
            $('a.prev-lesson').show();
        } else {
            $('a.prev-lesson').hide();
        }
    }
    
    var volume = 1;
    var videoCode = '<video id="v-less" controls autoplay preload="none" width="widthVideo" height="heightVideo"><source src="rs/video/01.mp4" type=\'video/mp4; codecs="avc1.42E01E, mp4a.40.2"\' />your browser does not support the HTML 5 video tag</video><div class="nav-lessons" style="display: table;"><div class="nav-lessons-left"><a class="prev-lesson" href="#">Смотреть предыдущий урок</a></div><div class="dop-mat">dopmatHtml</div><div class="nav-lessons-right"><a class="next-lesson" href="#">Смотреть следующий урок</a></div></div>';
    $('.basic-part a[href="#video"], .advance-part a[href="#video"]').fancybox({
		hideOnContentClick: false,
        padding: 0,
        margin: [20, 20, 20, 20], // Increase left/right margin
        helpers: {
            overlay : {
                closeClick : false,
            }
        },
        beforeLoad: function(){
            
            var size = $(this.element).attr('data-size').split(':');
            var widthVideo = size[0]*1;
            var heightVideo = size[1]*1;
            var ratioVideo = widthVideo/heightVideo;
            var windowWidth = $(window).width()*1 - 2*20;
            var windowHeight = $(window).height()*1 - 2*20 - 80;
            
            //console.log('windowWidth: '+windowWidth+'  windowHeight: '+windowHeight);
            
            if(windowWidth < widthVideo || windowHeight <heightVideo){
                
                var currentRatioVideo = windowWidth/windowHeight;
                if(currentRatioVideo > ratioVideo){
                    
                    widthVideo = Math.round(windowHeight*ratioVideo);
                    heightVideo = windowHeight;
                    
                } else {
                    
                    widthVideo = windowWidth;
                    heightVideo = Math.round(windowWidth/ratioVideo);
                }
            }
            
            var path = $(this.element).attr('rel');
            var dm_key = $(this.element).attr('data-key');           
            var dopmatHtml = '';
            if(dm_key && dopmat[dm_key]){
                dopmatHtml = dopmat[dm_key];
            }
            
            $('#video').html(videoCode.replace(/01.mp4/g, path).replace(/widthVideo/g, widthVideo).replace(/heightVideo/g, heightVideo).replace(/dopmatHtml/g, dopmatHtml));
            
            prevNext($(this.element));
        },
        afterShow: function(){
            var video = $('#v-less').get(0);
            video.volume = volume;
            video.play();            
        },
        beforeClose: function(){
            volume = $('#v-less').get(0).volume;            
        },
        afterClose: function(){
            $('#v-less').get(0).pause();            
        } 
	});
    
    $(document).on('click', 'div.nav-lessons a[href="#"]', function(event){
        
        event.preventDefault();        
        var a = $('.advance-part a.active, .basic-part a.active');
        
        if($(this).attr('class') == 'next-lesson'){
            
            if(a.next('a[href="#video"]').length){
                a.next('a[href="#video"]').trigger('click');
            } else {
                a.next().next('a[href="#video"]').trigger('click');
            }
            
        } else {
            
            if(a.prev('a[href="#video"]').length){
                a.prev('a[href="#video"]').trigger('click');
            } else {
                a.prev().prev('a[href="#video"]').trigger('click');
            }
            
        }
    });
});
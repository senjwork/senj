var _this = this;

var senj ={};

var js = function() {

        var _this = this;

        _this.init = function() {
                $(document).on('click', 'a[data-pjax]', function() {
                        $(this).parents('ul').find('li').removeClass('selected');
                        $(this).parent('li').addClass('selected');
                        $.pjax({
                            url: $(this).attr('href'),
                            container: $(this).attr('data-pjax'),
                            success: function(data) {
                                history.pushState(null, $(data).filter('title').text(), $(this).attr('href'));
                            }
                        });
                        return false;
            });
        };
}

senj.js = new js();
        
$(document).ready(function(){
        senj.js.init();    
})

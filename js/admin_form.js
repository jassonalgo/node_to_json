(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      $(window).once().on('load', function () {
        //validate checked elements on load
        $( ".content-type" ).each(function( index ) {
          if($(this).prop("checked") == true){
            $contentType = $(this).data('content');
            //show or hide field set
            $('.content-type-fields.'+$contentType).show()
           }
        });

        //add event click
        $('.content-type').on('click', function () {
          $contentType = $(this).data('content');
          //show or hide field set
          $('.content-type-fields.'+$contentType).toggle()
        });
      });
    }
  };
})(jQuery, Drupal);

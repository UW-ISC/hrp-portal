jQuery(document).ready(function( $ ) {

  var frame;

  $('body').on('click', '.select-an-image', function(e){

      e.preventDefault();

      var $this    = $(this)
        , $preview = $this.siblings('.image-preview')
        , $inputs  = $this.siblings('input')

      frame = wp.media.frames.frame = wp.media({

          className: 'media-frame single-image-media-frame',

          frame: 'select',

          multiple: false,

          title: 'Select an image',

      });

      frame.on('select', function(){

          var media = frame.state().get('selection').first().toJSON()
            , $img  = $('img').attr({
              'src'   : media.url,
              'width' : '100%'
            })

          $inputs.filter('.wp-get-posts-imageID').val( media.id ).trigger('change')
          $inputs.filter('.wp-get-posts-image').val( media.url )

      });

      frame.open();
  });

/** Panels fix: preview of the image doesn't show otherwise **/
   $('body').on('mouseenter', '.widget-dialog-uw_widget_single_image, .widget-dialog-uw_widget_single_image_community_photo', function() {

    var $this = $(this)
      , $img  = $this.find('img')
      , src   = $this.find('input.site-panels-image-fix').val()

    $img.attr( 'src', src )

  })

})

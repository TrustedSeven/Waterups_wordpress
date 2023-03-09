(function($){
    console.log('Loaded custom js');

    $('#trade-app #insightly_LEAD_FIELD_6').change(function () {
        if ( $('#trade-app #insightly_LEAD_FIELD_6').val() == "Other" ) {
          $('#trade-app #insightly_LEAD_FIELD_6_Other').show();
        }
        else {
          $('#trade-app #insightly_LEAD_FIELD_6_Other').hide();
        }
    });
    $('#trade-app #insightly_LEAD_FIELD_8').change(function () {
        if ( $('#trade-app #insightly_LEAD_FIELD_8').val() == "Other" ) {
          $('#trade-app #insightly_LEAD_FIELD_8_Other').show();
        }
        else {
          $('#trade-app #insightly_LEAD_FIELD_8_Other').hide();
        }
    });

    $( "#insightly_web_to_lead" ).submit(function( event ) {
	   alert( "Thank you for your application and it has been submitted. Please allow 2 working days for us to process the application. You can contact us through 1300 205 550 or email sales@waterups.com.au" );
    });

    $('.single-product .woocommerce-message').append('<a href="/waterups-shop">Back to Shop</a>');	

    $('#home-blog a').attr('target', '_blank');

    document.addEventListener( 'wpcf7submit', function( event ) {
      if ( '461' == event.detail.contactFormId ) {
          window.location = 'https://www.waterups.com.au//wp-content/uploads/2018/02/WaterUps-Installation-Guide-21-Feb-18.pdf';
      }
    }, false );

    $('.blurbs-container .et_pb_button').hide();
    $('.blurbs-container .et_pb_column').hover(
      function () {
        $(this).closest('.et_pb_column ').find('.et_pb_button').show();
      },
      function () {
        $(this).closest('.et_pb_column ').find('.et_pb_button').hide();
      }
    );

    var isLoggedIn = $('body.logged-in').length;
    console.log(isLoggedIn);
    if (!isLoggedIn) {
        $('<div class=error-captcha"><span class="glyphicon glyphicon-remove" ></span> Please show you’re not a robot</div>" ').insertBefore(".g-recaptcha");
        return false;
    } else {
        return true;
    }

})(jQuery);

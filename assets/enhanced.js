jQuery( document ).ready( function($) {
  // Hide pickup date and time fields by default
  $( '#pickup_date_field, #pickup_time_field' ).hide();
  
  // Show pickup date and time fields if local pickup is selected
  $( 'form.checkout' ).on( 'change', 'input[name^="shipping_method"]', function(e) {
    // var shipping_method = $('input[name^="shipping_method"]:checked').val();
    var shipping_method = e.target.value;
    // console.log('shipping_method',e.target.value);
      if ( shipping_method == 'local_pickup:1' ) {
          $( '#pickup_date_field, #pickup_time_field' ).show();
      } else {
          $( '#pickup_date_field, #pickup_time_field' ).hide();
      }
  });
});

<?php
/**
 * Abandoned cart email CSS.
 */
?>
.fp-rac-email-product-info-table{
margin-bottom: 15px; 
font-family: 'Helvetica Neue', 'Helvetica', 'Roboto', 'Arial', 'sans-serif';
width:100%;
}

.fp-rac-email-product-td{
text-align:left;
}

.fp-rac-email-product-info-td{
text-align:left;
border-top-width:4px;
}

.fp-rac-preview-product-td{
text-align:left; 
vertical-align:middle; 
font-family: 'Helvetica Neue', 'Helvetica', 'Roboto', 'Arial', 'sans-serif'; 
word-wrap:break-word;
}

.fp-rac-email-cart-link{
color:#<?php echo esc_attr( get_option( 'rac_email_link_color' ) ) ; ?> 
}

.fp-rac-email-logo-wrapper{
margin-top:0;
}

.fp-rac-email-logo{
max-height:600px;
max-width:600px;
}

.fp-rac-unsubscribe-link{
color:#<?php echo esc_attr( get_option( 'rac_unsubscribe_link_color' ) ) ; ?> 
}

.fp-rac-cart-link-button-table{
margin-bottom: 15px;
}

.fp-rac-cart-link-button-wrapper{
-webkit-border-radius: 5px;
-moz-border-radius: 5px; 
border-radius: 5px; 
color: #ffffff; 
display: block; 
padding:0px 10px 0px 10px;
}

.fp-rac-cart-link-button{
text-decoration: none; 
width:100%;
display:inline-block;
line-height:40px;
}

.fp-rac-cart-link-button span{
color:#<?php echo esc_attr( get_option( 'rac_cart_button_link_color' ) ) ; ?> 
}

.fp-rac-tmcartepo-img{
max-width:32px;
max-height:32px;
}
<?php

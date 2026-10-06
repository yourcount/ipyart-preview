<?php
// Alleen lokale preview: geen lazy loading, zodat screenshots over de volle hoogte alle beelden tonen.
add_filter( 'wp_lazy_loading_enabled', '__return_false' );
add_filter( 'wp_img_tag_add_loading_attr', '__return_false' );

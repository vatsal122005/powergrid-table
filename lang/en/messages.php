<?php

return [
    // Product messages
    'product_created'        => 'Ta-da! Your product is live and ready to shine!',
    'product_create_failed'  => 'Oops, product creation failed! Don\'t worry, it\'s not you, it\'s us... or maybe it\'s you',
    'product_updated'        => 'Product updated! You must be a ninja, because that was fast!',
    'product_update_failed'  => 'Product update failed! It looks like our servers are having a bad hair day',
    'mail_failed'            => 'Email failed to send! Our email rocket crashed, but we\'ll try again',

    // Validation overrides (optional)
    'validation' => [
        'name_required'        => 'Give your product a name that\'s out of this world!',
        'description_required' => 'Tell us about your product! We want to know its secrets',
    ],

    // Delete & error messages
    'invalid_id'             => 'Invalid product ID.',
    'not_found'              => 'Product not found.',
    'delete_confirm_title'   => 'Are you sure?',
    'delete_confirm_message' => "Do you really want to delete the product :name?",
    'delete_success'         => "Product ':name' has been deleted successfully!",
    'delete_error'           => 'Error deleting product.',
    'image_not_found'        => 'Product image not found.',
    'image_missing'          => 'Image file does not exist.',
    'invalid_path'           => 'Invalid file path.',
    'confirm_yes'            => 'Yes, delete it!',
    'confirm_cancel'         => 'No, keep it.',
];

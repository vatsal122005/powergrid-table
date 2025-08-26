<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product Messages
    |--------------------------------------------------------------------------
    */
    'product_created' => '🎉 Boom! Your product is live and stealing the spotlight!',
    'product_create_failed' => '😅 Whoops! Product creation failed. Blame the coffee, not you!',
    'product_updated' => '⚡ Product updated in a flash! You’re basically a wizard.',
    'product_update_failed' => '💥 Update failed! Our servers are having a tantrum.',
    'product_deleted' => "🗑️ Product ':name' has been deleted successfully!",
    'product_delete_failed' => '🚫 Failed to delete product. It’s clinging to life!',
    'product_not_found' => '😢 Product vanished! Maybe it went on vacation?',
    'product_list_retrieved' => '📦 Products listed successfully!',
    'product_already_exists' => '🙃 This product already exists. Try being unique!',

    /*
    |--------------------------------------------------------------------------
    | Category Messages
    |--------------------------------------------------------------------------
    */
    'category_created' => '🎊 Category created! Your shelves just got cooler.',
    'category_create_failed' => '😬 Couldn’t create category. Try shaking the keyboard?',
    'category_updated' => '✨ Category updated! Fancy!',
    'category_update_failed' => '😵 Update failed! Server brains overloaded.',
    'category_deleted' => '🗑️ Category deleted! Bye-bye chaos.',
    'category_delete_failed' => '😕 Couldn’t delete category. IT elves are on strike!',
    'category_not_found' => '🔍 Category lost! It must be hiding.',
    'category_list_retrieved' => '📜 Categories listed! All accounted for.',
    'category_already_exists' => '🙃 Oops! This category already exists. Try originality!',

    /*
    |--------------------------------------------------------------------------
    | User / Auth Messages
    |--------------------------------------------------------------------------
    */
    'login_success' => '✅ Welcome back, :name! You’re in. 🚀',
    'login_failed' => '❌ Login failed. Wrong credentials, or maybe the keyboard betrayed you!',
    'logout_success' => '👋 You’ve been logged out. See you soon!',
    'unauthorized' => '🚫 You’re not allowed to do this. Nice try tho!',
    'account_created' => '🎉 Account created successfully. Welcome aboard!',
    'account_create_failed' => '😔 Could not create account. Tech gremlins at work.',
    'password_reset_sent' => '📧 Password reset email zooming to your inbox!',
    'password_reset_failed' => '❌ Failed to send reset email. Internet ghosts?',

    /*
    |--------------------------------------------------------------------------
    | Validation Overrides
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'name_required' => 'Give your product a name worthy of fame!',
        'description_required' => 'Share the juicy deets about your product, don’t hold back!',
        'email_required' => '📧 We need your email! Even spam bots have one.',
        'password_required' => '🔑 Password is required. Don’t go naked in cyberspace!',
        'invalid_format' => '⚠️ Oops! That format looks suspicious.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Orders / Transactions
    |--------------------------------------------------------------------------
    */
    'order_created' => '🛒 Order placed successfully! Shopping spree activated.',
    'order_create_failed' => '😵 Could not place order. Cart spilled everywhere!',
    'order_updated' => '🔄 Order updated! Delivery ninjas are on standby.',
    'order_update_failed' => '🚧 Update failed! Delivery truck broke down.',
    'order_deleted' => '❌ Order cancelled. Money refunded (hopefully).',
    'order_delete_failed' => '⚠️ Could not cancel order. It’s already flying!',
    'order_not_found' => '🔍 Order not found. Did it vanish in thin air?',
    'payment_success' => '💰 Payment successful! Money magic complete.',
    'payment_failed' => '💸 Payment failed. Wallet cried a little.',

    /*
    |--------------------------------------------------------------------------
    | File & Upload Messages
    |--------------------------------------------------------------------------
    */
    'file_uploaded' => '📂 File uploaded successfully! 🎉',
    'file_upload_failed' => '🚫 File upload failed. Blame the Wi-Fi!',
    'file_not_found' => '📁 File not found. Maybe it eloped?',
    'invalid_file_type' => '⚠️ Invalid file type. That doesn’t belong here!',
    'file_deleted' => '🗑️ File deleted successfully!',
    'file_delete_failed' => '❌ Could not delete file. It’s stuck!',

    /*
    |--------------------------------------------------------------------------
    | Generic CRUD Messages
    |--------------------------------------------------------------------------
    */
    'create_success' => '✅ Created successfully!',
    'create_failed' => '❌ Creation failed!',
    'update_success' => '🔄 Updated successfully!',
    'update_failed' => '⚠️ Update failed!',
    'delete_success' => '🗑️ Deleted successfully!',
    'delete_failed' => '🚫 Delete failed!',
    'restore_success' => '♻️ Restored successfully!',
    'restore_failed' => '😬 Restore failed!',
    'force_delete_success' => '💀 Permanently deleted!',
    'force_delete_failed' => '❌ Could not permanently delete.',

    /*
    |--------------------------------------------------------------------------
    | System / Errors / Cache / Queue
    |--------------------------------------------------------------------------
    */
    'invalid_id' => '❌ Whoops! That ID doesn’t exist in our universe.',
    'invalid_path' => '🛤️ That path is invalid. We checked twice!',
    'server_error' => '🔥 Something broke on our end. Please try again!',
    'service_unavailable' => '🛑 Service temporarily unavailable. Grab some chai ☕ and wait!',
    'cache_miss_fetching_db' => '📦 Cache miss - fetching from database...',
    'cache_hit_inside_lock' => '⚡ Cache hit (inside lock).',
    'lock_released' => '🔓 Lock released.',
    'another_process_waiting' => '⌛ Another process rebuilding cache - waiting...',
    'job_dispatched' => '🚀 Job dispatched successfully!',
    'job_failed' => '💥 Job failed! Queue gremlins at work.',

    /*
    |--------------------------------------------------------------------------
    | Confirm Dialogs
    |--------------------------------------------------------------------------
    */
    'delete_confirm_title' => '⚠️ Warning!',
    'delete_confirm_message' => 'Are you sure you want to erase :name from existence?',
    'confirm_yes' => '👍 Yes, do it!',
    'confirm_cancel' => '🙅‍♂️ No, keep it!',
];

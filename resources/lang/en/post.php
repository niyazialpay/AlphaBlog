<?php

return [
    'title' => 'Title',
    'slug' => 'URL',
    'content' => 'Content',
    'image' => 'Image',
    'category' => 'Category',
    'select_category' => 'Select Category',
    'tags' => 'Tags',
    'meta_description' => 'Meta Description',
    'meta_keywords' => 'Meta Keywords',

    'request' => [
        'title_required' => 'Title is required.',
        'slug_unique' => 'URL must be unique.',
        'content_required' => 'Content is required.',
        'meta_description_string' => 'Meta description must be a string.',
        'meta_description_max' => 'Meta description may not be greater than 255 characters.',
        'meta_keywords_string' => 'Meta keywords must be a string.',
        'category_id_required' => 'Category is required.',
        'category_id_array' => 'Category must be an array.',
        'category_id_exists' => 'Category not found.',
        'user_id_required' => 'User is required.',
        'user_id_exists' => 'User not found.',
        'is_published_required' => 'Publish status is required.',
        'is_published_boolean' => 'Publish status must be a boolean.',
        'tags_string' => 'Tags must be a string.',
        'image_file' => 'Image must be a file.',
        'image_image' => 'Image must be an image.',
        'image_max' => 'Image may not be greater than 50 MB.',
        'image_mimes' => 'Image must be a file of type: jpeg, png, jpg, gif, svg, webp.',
    ],

    'success' => 'Content added successfully',
    'success_update' => 'Content updated successfully',
    'success_delete' => 'Content deleted successfully',
    'error' => 'An error occurred',
    'success_image_delete' => 'Image deleted successfully',
    'error_image_delete' => 'An error occurred while deleting the image',

    'post' => [
        'success_delete' => 'Content deleted successfully',
        'error_delete' => 'An error occurred while deleting the content',
        'success_force_delete' => 'Content permanently deleted successfully',
        'error_force_delete' => 'An error occurred while permanently deleting the content',
        'success_restore' => 'Content restored successfully',
        'error_restore' => 'An error occurred while restoring the content',
        'success_update' => 'Content updated successfully',
    ],

    'status_active' => 'Active',
    'status_passive' => 'Passive',
    'is_published' => 'Publish Status',
    'author' => 'Author',
    'published_at' => 'Published At',
    'draft' => 'Draft',
    'no_posts_found' => 'No content found',

    'blogs' => 'Blogs',
    'blog' => 'Blog',
    'all_blogs' => 'All Blogs',

    'pages' => 'Pages',
    'page' => 'Page',
    'all_pages' => 'All Pages',

    'restore_sure' => 'Are you sure you want to restore the content?',

    'read_more' => 'Read More',
    'media' => 'Media',
    'delete_image' => 'Delete Image',
    'delete_image_text' => 'Are you sure you want to delete the image?',
    'delete_image_success_title' => 'Image Deleted',
    'delete_image_success' => 'Image deleted successfully.',
    'delete_image_error_title' => 'An Error Occurred',
    'delete_image_error' => 'An error occurred while deleting the image.',

    'history' => 'History',
    'current' => 'Current',
    'revert' => 'Revert',
    'reverted' => 'Reverted',
    'revert_sure' => 'Are you sure you want to revert the content?',
    'revert_success_title' => 'Content Reverted',
    'revert_success' => 'Content reverted successfully.',

    'most_read_posts' => 'Most Read Posts',
    'last_comments' => 'Last Comments',
    'search_results_for' => 'Search Results For',

    'leave_comment_too' => 'Leave a comment too',
    'leave_comment' => 'Leave a comment',
    'comments' => '{0} There are none comment|{1} 1 comment|[2,*] :count comments',

    'similar_posts' => 'Similar Posts',
    'you_may_also_want_to_read_these' => 'You may also want to read these',

    'search' => 'Search',
    'search_placeholder' => 'Search...',
    'search_title' => 'He/She who does not seek will not find',
    'search_input_empty' => 'You must enter a search term to search!',

    'comment_notes' => 'Your email address will not be published. Required fields are marked *',
    'no_result_found' => 'No results found for your search term. Please try again with different keywords.',

    'another_language' => 'This post has a version in a different language.',

    'copy_full_url' => 'Copy URL',
    'copy_url_path' => 'Copy URL Path',

    'comments_count' => 'Comments',
    'views' => 'Views',

    'new_post' => 'New Post',
];

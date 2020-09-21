<?php

return [
    'plugin' => [
        'name' => 'Membership',
        'description' => 'A plugin used to manage association members.'
    ],
    'membership' => [
      'members' => 'Members',
      'categories' => 'Categories',
      'documents' => 'Documents',
    ],
    'members' => [
    ],
    'categories' => [
    ],
    'documents' => [
    ],
    // Boilerplate attributes.
    'attribute' => [
      'title' => 'Title',
      'name' => 'Name',
      'slug' => 'Slug',
      'type' => 'Type',
      'code' => 'Code',
      'required' => 'Required',
      'yes' => 'Yes',
      'no' => 'No',
      'description' => 'Description',
      'title_placeholder' => 'New item title',
      'name_placeholder' => 'New item name',
      'code_placeholder' => 'New item code',
      'created_at' => 'Created at',
      'created_by' => 'Created by',
      'updated_at' => 'Updated at',
      'updated_by' => 'Updated by',
      'tab_edit' => 'Edit',
      'tab_manage' => 'Manage',
      'status' => 'Status',
      'published_up' => 'Start publishing',
      'published_down' => 'Finish publishing',
      'access' => 'Access',
      'viewing_access' => 'Viewing access',
      'category' => 'Category',
      'field_group' => 'Field group',
      'main_category' => 'Main category',
      'parent_category' => 'Parent category',
      'none' => 'None',
    ],
    'status' => [
      'published' => 'Published',
      'unpublished' => 'Unpublished',
      'trashed' => 'Trashed',
      'archived' => 'Archived',
      'pending' => 'Pending',
      'refused' => 'Refused',
      'pending_payment' => 'Pending payment',
      'member' => 'Member',
      'discarded' => 'Discarded',
    ],
    'action' => [
      'new' => 'New Article',
      'publish' => 'Publish',
      'unpublish' => 'Unpublish',
      'trash' => 'Trash',
      'archive' => 'Archive',
      'delete' => 'Delete',
      'save' => 'Save',
      'save_and_close' => 'Save and close',
      'create' => 'Create',
      'create_and_close' => 'Create and close',
      'cancel' => 'Cancel',
      'check_in' => 'Check-in',
      'select' => '- Select -',
      'publish_success' => ':count item(s) successfully published.',
      'unpublish_success' => ':count item(s) successfully unpublished.',
      'archive_success' => ':count item(s) successfully archived.',
      'trash_success' => ':count item(s) successfully trashed.',
      'delete_success' => ':count item(s) successfully deleted.',
      'check_in_success' => ':count item(s) successfully checked-in.',
      'parent_item_unpublished' => 'Cannot publish this item as its parent item is unpublished.',
      'previous' => 'Previous',
      'next' => 'Next',
      'deletion_confirmation' => 'Are you sure you want to delete the selected items ?',
      'cannot_reorder' => 'Cannot reorder items by category as none or more than 1 categories are selected. Please select only 1 category.',
      'checked_out_item' => 'The ":name" item cannot be modified as it is currently checked out by a user.',
      'check_out_do_not_match' => 'The user checking out doesn\'t match the user who checked out the item. You are not permitted to use that link to directly access that page.',
      'editing_not_allowed' => 'You are not allowed to edit this item.',
      'used_as_main_category' => 'The ":name" category cannot be deleted as it is used as main category in one or more articles.',
      'not_allowed_to_modify_item' => 'You are not allowed to modify the ":name" item.',
    ],
    'settings' => [
      'january' => 'January',
      'february' => 'February',
      'march' => 'March',
      'april' => 'April',
      'may' => 'May',
      'june' => 'June',
      'july' => 'July',
      'august' => 'August',
      'september' => 'September',
      'october' => 'October',
      'november' => 'November',
      'december' => 'December',
    ]
];


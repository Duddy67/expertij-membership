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
    'member' => [
      'tab_documents' => 'Documents',
      'tab_profile' => 'Profile',
      'tab_votes' => 'Votes',
      'tab_payments' => 'Payments',
      'tab_insurance' => 'Insurance',
      'first_name' => 'First name',
      'last_name' => 'Last name',
      'email' => 'Email',
      'appeal_court' => 'Appeal court',
    ],
    'members' => [
      'filter_status' => 'Status',
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
      'slug_placeholder' => 'New item slug',
      'created_at' => 'Created at',
      'created_by' => 'Created by',
      'updated_at' => 'Updated at',
      'updated_by' => 'Updated by',
      'tab_edit' => 'Edit',
      'tab_manage' => 'Manage',
      'tab_categories' => 'Categories',
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
      'pending_subscription' => 'Pending subscription',
      'cancelled' => 'Cancelled',
      'member' => 'Member',
      'pending_renewal' => 'Pending renewal',
      'revoked' => 'Revoked',
      'cancellation' => 'Cancellation',
    ],
    'action' => [
      'new_document' => 'New Document',
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
      'revocation_success' => ':count member(s) successfully revoked.',
      'update_success' => 'Data has been updated successfully.',
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
      'profile_update_success' => 'The member profile has been updated successfully.',
      'vote_success' => 'Your vote has been taken into account successfully.',
      'file_replace_success' => 'The file has been replaced successfully.',
      'cheque_payment_success' => 'Your cheque payment has been taken into account successfully.',
      'payment_update_success' => 'The payment has been updated successfully.',
      'status_changed_by_system' => 'The status as been changed by the system. You cannot save the form. Please refresh the page (F5 key) and then save it again.',
      'email_sendings' => 'Email sendings (:count)',
      'email_sendings_success' => 'The emails have been sent successfully to the decision makers.',
    ],
    'email' => [
      'your_application' => 'Your application',
      'new_application' => 'New application',
      'new_vote' => 'New vote',
      'new_member' => 'You are member !',
      'pending_renewal' => 'Subscription renewal',
      'pending_renewal_reminder' => 'Subscription renewal reminder',
      'pending_renewal_last_reminder' => 'Subscription renewal last reminder',
      'pending_subscription' => 'Application accepted',
      'refused' => 'Application refused',
      'renewal_subscription' => 'Membership renewed',
      'revoked' => 'Membership revoked',
      'cancelled' => 'Application cancelled',
      'cancellation' => 'Membership cancellation',
      'cheque_payment' => 'Cheque payment confirmation',
      'alert_cheque_payment' => 'Cheque payment notification',
      'payment_completed' => 'Payment completed',
      'payment_completed_admin' => 'Payment completed',
      'payment_error' => 'Payment error',
      'payment_error_admin' => 'Payment error',
      'payment_cancelled' => 'Payment cancelled',
      'payment_cancelled_admin' => 'Payment cancelled',
    ],
    'payment' => [
      'subscription' => 'Subscription',
      'subscription-insurance-f1' => 'Subscription + Insurance (Essential)',
      'subscription-insurance-f2' => 'Subscription + Insurance (Full)',
      'insurance-f1' => 'Insurance (Essential)',
      'insurance-f2' => 'Insurance (Full)',
    ],
    'global_settings' => [
      'tab_general' => 'General',
      'tab_prices' => 'Prices',
      'renewal_day' => 'Renewal day',
      'renewal_day_comment' => 'Renewal day',
      'renewal_month' => 'Renewal month',
      'renewal_month_comment' => 'Renewal month',
      'renewal_period' => 'Renewal period',
      'renewal_period_comment' => 'Sets a renewal period (in days) before the renewal date',
      'free_period' => 'Free period',
      'free_period_comment' => 'Sets a subscription period (in days) before the renewal date.',
      'reminder_renewal' => 'Reminder renewal',
      'reminder_renewal_comment' => 'Sets a reminder (in days) before the renewal date',
      'revocation' => 'Revocation',
      'revocation_comment' => 'Sets a delay (in days) after the renewal date',
      'subscription_fee' => 'Subscription fee',
      'subscription_fee_comment' => 'Currency: Euro',
      'insurance_fee_f1' => 'Insurance fee (Formula 1)',
      'insurance_fee_f1_comment' => 'Currency: Euro',
      'insurance_fee_f2' => 'Insurance fee (Formula 2)',
      'insurance_fee_f2_comment' => 'Currency: Euro',
      'insurance_f1' => 'AXA Essential',
      'insurance_f2' => 'AXA Full',
      'january' => 'January',
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
    ],
    // Variables used with the Profile plugin.
    'profile' => [
      'attestation' => 'Attestation',
      'appeal_court' => 'Appeal court',
      'categories' => 'Categories',
      'select' => '- Select -',
    ]
];


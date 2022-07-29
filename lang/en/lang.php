<?php

return [
    'plugin' => [
        'name' => 'Membership',
        'description' => 'A plugin used to manage association members.'
    ],
    'membership' => [
      'menu_label' => 'Membership',
      'members' => 'Members',
      'categories' => 'Categories',
      'documents' => 'Documents',
      'member_space' => 'Member space',
      'member_list' => 'Member list',
    ],
    'member' => [
      'tab_profile' => 'Profile',
      'tab_licences' => 'Licences',
      'tab_pro_status' => 'Professional status',
      'tab_votes' => 'Votes',
      'tab_payments' => 'Payments',
      'tab_insurance' => 'Insurance',
      'tab_informations' => 'Informations',
      'tab_invoices' => 'Invoices',
      'tab_payment' => 'Payment',
      'tab_documents' => 'Documents',
      'first_name' => 'First name',
      'last_name' => 'Last name',
      'email' => 'Email',
      'appeal_court' => 'Appeal court',
      'languages_expert' => 'Languages (Expert)',
      'languages_ceseda' => 'Languages (CESEDA)',
      'honorary_member_info_1' => 'The honorary members have no licences.',
      'honorary_member_info_2' => 'The honorary members have no professional status.',
      'no_longer_member' => 'You are no longer member.',
    ],
    'members' => [
      'filter_status' => 'Status',
    ],
    'categories' => [
    ],
    'documents' => [
      'recipients' => 'Recipients',
    ],
    'document' => [
      'tab_filters' => 'Filters',
      'appeal_courts' => 'Appeal courts',
      'courts' => 'Courts',
      'languages' => 'Languages',
      'licence_types' => 'Licence types',
      'last_email_sending' => 'Last email sending',
    ],
    // Boilerplate attributes.
    'attribute' => [
      'title' => 'Title',
      'name' => 'Name',
      'slug' => 'Slug',
      'type' => 'Type',
      'code' => 'Code',
      'date' => 'Date',
      'item' => 'Item',
      'required' => 'Required',
      'yes' => 'Yes',
      'no' => 'No',
      'all' => 'All',
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
      'information' => 'Information',
    ],
    'status' => [
      'published' => 'Published',
      'unpublished' => 'Unpublished',
      'trashed' => 'Trashed',
      'archived' => 'Archived',
      'pending' => 'Pending',
      'refused' => 'Refused',
      'pending_subscription' => 'Pending subscription',
      'completed' => 'Completed',
      'cancelled' => 'Cancelled',
      'error' => 'Error',
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
      'reset' => 'Reset',
      'update' => 'Update',
      'replace' => 'Replace',
      'validate' => 'Validate',
      'return' => 'Return',
      'save_and_close' => 'Save and close',
      'create' => 'Create',
      'create_and_close' => 'Create and close',
      'edit' => 'Edit',
      'cancel' => 'Cancel',
      'check_in' => 'Check-in',
      'check_renewal' => 'Check renewal',
      'select' => '- Select -',
      'all' => '- All -',
      'pay_now' => 'Pay Now',
      'pay_cheque_confirmation' => 'You have chosen to pay by cheque. You\'re going to receive an email with the needed informations. Do you wish to continue ?',
      'pay_paypal_confirmation' => 'You have chosen to pay with PayPal. You\'re going to get redirect to the PayPal platform. Do you wish to continue ?',
      'pay_sherlocks_confirmation' => 'You have chosen to pay with LCL. It will ask you to choose a credit card. Do you wish to continue ?',
      'pay_free_period_confirmation' => 'Due to your late subscription in the current year, the subscription for the next year is free. Do you wish to continue ?',
      'subscribe' => 'Subscribe',
      'export' => 'Export',
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
      'vote_confirmation' => 'Your vote is about to be validated. Are you sure ?',
      'payment_confirmation' => 'The payment is about to be taken into account. Are you sure ?',
      'file_replace_success' => 'The file has been replaced successfully.',
      'cheque_payment_success' => 'Your cheque payment has been taken into account successfully.',
      'bank_transfer_payment_success' => 'Your bank transfer payment has been taken into account successfully.',
      'free_period_privilege_success' => 'Your free period privilege has been taken into account successfully.',
      'payment_update_success' => 'The payment has been updated successfully.',
      'status_changed_by_system' => 'The status as been changed by the system. You cannot save the form. Please refresh the page (F5 key) and then save it again.',
      'status_change_confirmation' => 'The member status is about to be changed. Are you sure ?',
      'state_change_confirmation' => 'The item state is about to be changed. Are you sure ?',
      'email_sendings_count' => 'Email sendings (:count)',
      'email_sendings' => 'Email sendings',
      'email_sending_confirmation' => 'An email is about to be sent to one or more recipients. Are you sure ?',
      'email_sendings_success' => 'The emails have been sent successfully to the recipient(s).',
      'delete_file_success' => 'The file has been successfully deleted.',
      'no_file_selected' => 'No file selected.',
      'check_renewal_renewal_success' => 'The renewal period has started and an email has been sent to the members.',
      'check_renewal_reminder_success' => 'A reminder email has been sent to the members who haven\'t pay their subscription yet.',
      'check_renewal_delete_renewal_job_success' => 'The renewal job has been deleted.',
      'check_renewal_none_success' => 'No action has been performed.',
    ],
    'email' => [
      'your_application' => 'Your application',
      'new_application' => 'New application',
      'new_vote' => 'New vote',
      'new_document' => 'New document',
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
      'free_period_validated' => 'Free period validated',
      'free_period_validated_admin' => 'Free period validated',
      'payment_error' => 'Payment error',
      'payment_error_admin' => 'Payment error',
      'payment_cancelled' => 'Payment cancelled',
      'payment_cancelled_admin' => 'Payment cancelled',
    ],
    'payment' => [
      'subscription' => 'Subscription',
      'subscription-insurance-f1' => 'Adhésion + Assurance - Judiciaire option 1',
      'subscription-insurance-f2' => 'Adhésion + Assurance - Judiciaire option 2',
      'subscription-insurance-f3' => 'Adhésion + Assurance - Judiciaire option 3',
      'subscription-insurance-f4' => 'Adhésion + Assurance - Extrajudiciaire option 1',
      'subscription-insurance-f5' => 'Adhésion + Assurance - Extrajudiciaire option 2',
      'subscription-insurance-f6' => 'Adhésion + Assurance - Extrajudiciaire option 3',
      'subscription-insurance-f7' => 'Adhésion + Assurance - Judiciaire + Extrajudiciaire option 1',
      'subscription-insurance-f8' => 'Adhésion + Assurance - Judiciaire + Extrajudiciaire option 2',
      'subscription-insurance-f9' => 'Adhésion + Assurance - Judiciaire + Extrajudiciaire option 3',
      'insurance-f1' => 'Assurance - Judiciaire option 1',
      'insurance-f2' => 'Assurance - Judiciaire option 2',
      'insurance-f3' => 'Assurance - Judiciaire option 3',
      'insurance-f4' => 'Assurance - Extrajudiciaire option 1',
      'insurance-f5' => 'Assurance - Extrajudiciaire option 2',
      'insurance-f6' => 'Assurance - Extrajudiciaire option 3',
      'insurance-f7' => 'Assurance - Judiciaire + Extrajudiciaire option 1',
      'insurance-f8' => 'Assurance - Judiciaire + Extrajudiciaire option 2',
      'insurance-f9' => 'Assurance - Judiciaire + Extrajudiciaire option 3',
    ],
    'global_settings' => [
      'tab_general' => 'General',
      'tab_prices' => 'Prices',
      'tab_images' => 'Images',
      'renewal_day' => 'Renewal day',
      'renewal_day_comment' => 'Renewal day',
      'renewal_month' => 'Renewal month',
      'renewal_month_comment' => 'Renewal month',
      'renewal_period' => 'Renewal period',
      'renewal_period_comment' => 'Sets a renewal period (in days) before the renewal date',
      'free_period' => 'Free period',
      'free_period_comment' => 'Sets a subscription period (in days) before the renewal date.',
      'reminder_renewal' => 'Reminder renewal',
      'reminder_renewal_comment' => 'Sets a reminder (in days) before or after the renewal date. To set a reminder x days before the renewal date, please use the minus sign. ex: -15',
      'subscription_fee' => 'Subscription fee',
      'subscription_fee_comment' => 'Currency: Euro',
      'insurance_fee_f1' => 'Facture assurance: Judiciaire option 1',
      'insurance_fee_f1_comment' => 'Devise: Euro',
      'insurance_fee_f2' => 'Facture assurance: Judiciaire option 2',
      'insurance_fee_f2_comment' => 'Devise: Euro',
      'insurance_fee_f3' => 'Facture assurance: Judiciaire option 3',
      'insurance_fee_f3_comment' => 'Devise: Euro',
      'insurance_fee_f4' => 'Facture assurance: Extrajudiciaire option 1',
      'insurance_fee_f4_comment' => 'Devise: Euro',
      'insurance_fee_f5' => 'Facture assurance: Extrajudiciaire option 2',
      'insurance_fee_f5_comment' => 'Devise: Euro',
      'insurance_fee_f6' => 'Facture assurance: Extrajudiciaire option 3',
      'insurance_fee_f6_comment' => 'Devise: Euro',
      'insurance_fee_f7' => 'Facture assurance: Judiciaire + Extrajudiciaire option 1',
      'insurance_fee_f7_comment' => 'Devise: Euro',
      'insurance_fee_f8' => 'Facture assurance: Judiciaire + Extrajudiciaire option 2',
      'insurance_fee_f8_comment' => 'Devise: Euro',
      'insurance_fee_f9' => 'Facture assurance: Judiciaire + Extrajudiciaire option 3',
      'insurance_fee_f9_comment' => 'Devise: Euro',
      'insurance_f1' => 'Judiciaire option 1',
      'insurance_f2' => 'Judiciaire option 2',
      'insurance_f3' => 'Judiciaire option 3',
      'insurance_f4' => 'Extrajudiciaire option 1',
      'insurance_f5' => 'Extrajudiciaire option 2',
      'insurance_f6' => 'Extrajudiciaire option 3',
      'insurance_f7' => 'Judiciaire + Extrajudiciaire option 1',
      'insurance_f8' => 'Judiciaire + Extrajudiciaire option 2',
      'insurance_f9' => 'Judiciaire + Extrajudiciaire option 3',
      'photo_thumbnail' => 'Photo thumbnail',
      'photo_thumbnail_comment' => 'The size of the photo thumbnail in pixel. The width and height must be separated by a semicolon, (eg: 100:100).',
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
      'day_month_not_matching' => 'The renewal day doesn\'t match the renewal month. Ensure the chosen month has 31 days.',
      'reminder_renewal_too_high' => 'The number of reminder days is higher or equal to the number of the period days.',
      'settings_not_correctly_set' => 'Warning: One or more fields in the global settings are not correctly set.',
    ],
    'filter' => [
      'languages' => 'Languages',
      'type' => 'Type',
      'skill' => 'Skill',
      'appeal_courts' => 'Appeal courts',
      'courts' => 'Courts',
    ],
    'professional_status' => [
      'pro_status' => 'Status',
      'liberal_profession' => 'Liberal profession',
      'micro_entrepreneur' => 'Micro-entrepreneur',
      'company' => 'Company',
      'other' => 'Other',
      'pro_status_info' => 'Professional status info',
      'since' => 'Since',
      'siret_number' => 'SIRET number',
      'naf_code' => 'NAF code',
      'attestation' => 'Attestation',
      'linguistic_training' => 'Linguistic training',
      'extra_linguistic_training' => 'Extra linguistic training',
      'professional_experience' => 'Professional experience',
      'observations' => 'Observations',
      'why_expertij' => 'Why subscribing to Expertij',
      'code_of_ethics' => 'Code of ethics',
      'statuses' => 'Statuses',
      'internal_rules' => 'Internal rules',
    ],
    'votes' => [
      'voter' => 'Voter',
      'note' => 'Note',
      'choice' => 'Choice',
      'no_vote' => 'There is no vote to display',
    ],
    'payments' => [
      'payment_mode' => 'Payment mode',
      'amount' => 'Amount',
      'no_payment' => 'There is no payment to display',
      'pending_payment' => 'Your payment is currently pending.',
      'sherlocks' => 'LCI',
      'cheque' => 'Cheque',
      'free_period' => 'Free period',
      'item' => [
	'subscription' => 'Subscription',
	'insurance-f1' => 'Judiciaire option 1',
	'insurance-f2' => 'Judiciaire option 2',
	'insurance-f3' => 'Judiciaire option 3',
	'insurance-f4' => 'Extrajudiciaire option 1',
	'insurance-f5' => 'Extrajudiciaire option 2',
	'insurance-f6' => 'Extrajudiciaire option 3',
	'insurance-f7' => 'Judiciaire + Extrajudiciaire option 1',
	'insurance-f8' => 'Judiciaire + Extrajudiciaire option 2',
	'insurance-f9' => 'Judiciaire + Extrajudiciaire option 3',
	'subscription-insurance-f1' => 'Adhésion + Judiciaire option 1',
	'subscription-insurance-f2' => 'Adhésion + Judiciaire option 2',
	'subscription-insurance-f3' => 'Adhésion + Judiciaire option 3',
	'subscription-insurance-f4' => 'Adhésion + Extrajudiciaire option 1',
	'subscription-insurance-f5' => 'Adhésion + Extrajudiciaire option 2',
	'subscription-insurance-f6' => 'Adhésion + Extrajudiciaire option 3',
	'subscription-insurance-f7' => 'Adhésion + Judiciaire + Extrajudiciaire option 1',
	'subscription-insurance-f8' => 'Adhésion + Judiciaire + Extrajudiciaire option 2',
	'subscription-insurance-f9' => 'Adhésion + Judiciaire + Extrajudiciaire option 3',
      ]
    ],
    'insurance' => [
      'no_insurance' => 'This member has no insurance yet or their insurance is no longer running.',
      'running_insurance' => 'This member is running with the following insurance: ',
      'finishing_insurance' => 'This member\'s insurance is about to come to an end: ',
    ],
    'profile' => [
      'honorary_member' => 'Honorary Member',
      'appeal_court' => 'Appeal court',
      'court' => 'Court',
      'interpreter' => 'Interpreter',
      'translator' => 'Translator',
      'cassation' => 'Cassation',
    ]
];


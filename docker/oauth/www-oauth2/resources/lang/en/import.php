<?php

return [
	'import' => 'Import',

	'users' => [
	    'title' => 'Import users',
        'meta_title' => 'Import users',

        'action_submit' => 'Upload and import',
        'option_only_verified' => 'Only import verified email addresses',
        'description' => 'CSV must be in format:<br><kbd>email,is_admin,is_verified</kbd><br>without header.',
        'file_button' => 'Choose CSV file',

        'message_success' => 'The following users were created:',

        'errors' => [
            'already_present' => ':email is already present',
            'file_open' => 'The uploaded file could not be opened',
            'file_upload' => 'Error while uploading the CSV file',
            'line_not_csv' => 'Line number :line could not be interpreted as CSV',
            'line_wrong_count' => 'Number of values in line :line is :values, should be :expected',
            'line_invalid_email' => 'email :email on line :line is not a valid email',
            'line_invalid_is_admin' => 'is_admin :is_admin on line :line is not a valid boolean',
            'line_invalid_is_verified' => 'is_verified :is_verified on line :line is not a valid boolean',
            'not_verified' => ':email is not verified and was skipped',
            'save' => 'Cannot save :email to the database',
        ],
	],
];
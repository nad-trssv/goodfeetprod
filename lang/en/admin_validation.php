<?php

return [
    'profile'=>[
        'name.required'=>'Name is required.','name.string'=>'Name must be text.','name.max'=>'Name may not exceed 255 characters.',
        'phone.required'=>'Phone is required.','phone.string'=>'Phone must be text.','phone.max'=>'Phone may not exceed 15 characters.',
        'email.required'=>'Email is required.','email.email'=>'Enter a valid email address.','email.max'=>'Email may not exceed 25 characters.',
        'username.required'=>'Login is required.','username.string'=>'Login must be text.','username.min'=>'Login must contain at least 3 characters.','username.max'=>'Login may not exceed 30 characters.','username.regex'=>'Login must start with @ and contain only letters, numbers, dots and underscores.',
        'password.string'=>'New password must be text.','password.min'=>'New password must contain at least 8 characters.','password.max'=>'New password may not exceed 255 characters.','password_confirm.required_with'=>'Confirm the new password.','password_confirm.same'=>'The password confirmation does not match.','old_password.string'=>'Current password must be text.','password.required_with'=>'Enter a new password.','oldPassword.string'=>'Current password must be text.',
    ],
    'time_format'=>'Time must use the HH:MM format.','end_after_start'=>'The end time must be later than the start time.',
    'fixed'=>['required'=>'Specify at least one time.','array'=>'Time slots have an invalid format.','min'=>'Add at least one time slot.','item_required'=>'Time cannot be empty.'],
    'masters'=>['required'=>'Select at least one employee.','array'=>'The employee list has an invalid format.','exists'=>'The selected employee does not exist.'],
    'appointment'=>['title'=>'Enter a title.','name'=>'Enter the first name.','lastname'=>'Enter the last name.','phone'=>'Enter a phone number.','phone_format'=>'Use an international phone format, for example +372 55555555.','phone_min'=>'The phone number must contain at least 8 characters.','description_min'=>'The description must contain at least 8 characters.','end_after'=>'The end time must be later than the start time.','service_required'=>'Select a service.','service_exists'=>'Select a service from the list.'],
    'days'=>['integer'=>'The number of days must be numeric.','required'=>'Enter the number of days.'],
    'master_service'=>['price_required'=>'Enter an individual price.','price_numeric'=>'Price must be numeric.','price_min'=>'Price cannot be negative.','duration_required'=>'Enter an individual duration.','duration_min'=>'Duration must be at least one minute.','minimum_required'=>'Enter an individual minimum duration.','minimum_min'=>'Minimum duration must be at least one minute.','buffer_before_required'=>'Enter the buffer before the service.','buffer_before_min'=>'The buffer before cannot be negative.','buffer_after_required'=>'Enter the buffer after the service.','buffer_after_min'=>'The buffer after cannot be negative.','service_mismatch'=>'The selected service does not match the submitted form.','minimum_exceeds_duration'=>'Minimum duration cannot exceed the main duration.'],
    'site'=>['company_name'=>'Company name is required.','company_email'=>'Company email is required.','iframe'=>'The iframe is invalid.','facebook'=>'The Facebook URL is invalid.','youtube'=>'The YouTube URL is invalid.','instagram'=>'The Instagram URL is invalid.','twitter'=>'The Twitter / X URL is invalid.'],
    'lunch_invalid'=>'Check the lunch break times.',
];

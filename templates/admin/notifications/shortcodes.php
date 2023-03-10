<?php defined('ABSPATH') or die('No script kiddies please!'); // No direct access
$codes = array(
    array('code' => 'booking_number', 'description' => esc_html__('booking number', 'bookme')),
    array('code' => 'booking_date', 'description' => esc_html__('date of booking', 'bookme')),
    array('code' => 'booking_end_date', 'description' => esc_html__('end date of booking', 'bookme')),
    array('code' => 'booking_time', 'description' => esc_html__('time of booking', 'bookme')),
    array('code' => 'booking_end_time', 'description' => esc_html__('end time of booking', 'bookme')),
    array('code' => 'number_of_persons', 'description' => esc_html__('number of persons', 'bookme')),
    array('code' => 'total_price', 'description' => esc_html__('total price of booking (sum of all cart items after applying coupon)', 'bookme')),
    array('code' => 'approve_booking_url', 'description' => esc_html__('URL of approve booking link (to use inside <a> tag)', 'bookme')),
    array('code' => 'cancel_booking_url', 'description' => esc_html__('URL of cancel booking link (to use inside <a> tag)', 'bookme')),
    array('code' => 'cancellation_reason', 'description' => esc_html__('reason you mentioned while deleting booking', 'bookme')),
    array('code' => 'reject_booking_url', 'description' => esc_html__('URL of reject booking link (to use inside <a> tag)', 'bookme')),
    array('code' => 'payment_type', 'description' => esc_html__('payment type', 'bookme')),
    array('code' => 'customer_name', 'description' => esc_html__('full name of customer', 'bookme')),
    array('code' => 'customer_first_name', 'description' => esc_html__('first name of customer', 'bookme')),
    array('code' => 'customer_last_name', 'description' => esc_html__('last name of customer', 'bookme')),
    array('code' => 'customer_email', 'description' => esc_html__('email of customer', 'bookme')),
    array('code' => 'customer_phone', 'description' => esc_html__('phone of customer', 'bookme')),
    array('code' => 'category_name', 'description' => esc_html__('name of category', 'bookme')),
    array('code' => 'service_name', 'description' => esc_html__('name of service', 'bookme')),
    array('code' => 'service_price', 'description' => esc_html__('price of service', 'bookme')),
    array('code' => 'service_duration', 'description' => esc_html__('duration of service', 'bookme')),
    array('code' => 'service_info', 'description' => esc_html__('info of service', 'bookme')),
    array('code' => 'employee_name', 'description' => esc_html__('name of employee', 'bookme')),
    array('code' => 'employee_phone', 'description' => esc_html__('phone of employee', 'bookme')),
    array('code' => 'employee_email', 'description' => esc_html__('email of employee', 'bookme')),
    array('code' => 'employee_info', 'description' => esc_html__('info of employee', 'bookme')),
    array('code' => 'employee_photo', 'description' => esc_html__('photo of staff', 'bookme')),
    array('code' => 'company_name', 'description' => esc_html__('name of company', 'bookme')),
    array('code' => 'company_logo', 'description' => esc_html__('company logo', 'bookme')),
    array('code' => 'company_address', 'description' => esc_html__('address of company', 'bookme')),
    array('code' => 'company_phone', 'description' => esc_html__('company phone', 'bookme')),
    array('code' => 'company_website', 'description' => esc_html__('company web-site address', 'bookme')),
    array('code' => 'custom_fields', 'description' => esc_html__('combined values of all custom fields', 'bookme')),
    array('code' => 'custom_fields_2col', 'description' => esc_html__('combined values of all custom fields (formatted in 2 columns)', 'bookme')),
    array('code' => 'google_calendar_url', 'description' => esc_html__('URL for adding event to customer\'s Google Calendar (to use inside <a> tag)', 'bookme')),
    array('code' => 'online_meeting_host_url', 'description' => esc_html__('Online meeting url for staff', 'bookme')),
    array('code' => 'online_meeting_join_url', 'description' => esc_html__('Online meeting url for customers', 'bookme')),
    array('code' => 'online_meeting_password', 'description' => esc_html__('Online meeting password', 'bookme')),
);
\Bookme\Inc\Mains\Functions\System::shortcodes($codes);

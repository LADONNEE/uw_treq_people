<?php
/**
 * Configuration for School
 * Provides custom values from env file
 */

return [

    'scl_short_lowercase'=> env('SCL_SHORT_LOWCASE', 'school'),
    'scl_short_camelcase'=> env('SCL_SHORT_CAMELCASE', 'SchoolName'),
    'scl_long'=> env('SCL_LONG', 'Name of the School'),
    
    'scl_url_saml'=> env('SCL_URL_SAML', ''),
    'scl_url_prod'=> env('SCL_URL_PROD', 'example.com'),
    'scl_url_portal'=> env('SCL_URL_PORTAL', 'school.example.com'),
    
    'scl_email_general'=> env('SCL_EMAIL_GENERAL', 'mail@example.com'),
    'scl_email_treq'=> env('SCL_EMAIL_TREQ', 'mail@example.com'),
    'scl_email_domain'=> env('SCL_EMAIL_DOMAIN', 'example.com'),
    
];

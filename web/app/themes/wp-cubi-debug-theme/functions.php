<?php

require_once __DIR__ . '/src/schema.php';
require_once __DIR__ . '/src/registrations.php';


add_action('acf/save_post', 'getRegistrationFields', 10, 1);
function getRegistrationFields($post_id): void
{
    $message = "";

    if (get_field('registration_email', $post_id)) {
        $event = get_field('registration_event_id', $post_id);
        $message .= "<p>Date: ".get_field('event_date', $event)."</p>";
        $message .= "<p>Heure: ".get_field('event_time', $event)."</p>";

        $event_pdf_entrance_ticket = get_field('event_pdf_entrance_ticket', $event);

        wp_mail(
            get_field('registration_email', $post_id),
            'Confirmation d\'inscription',
            $message,
            array('Content-Type: text/html; charset=UTF-8'),
            get_attached_file($event_pdf_entrance_ticket)
        );
    }
}

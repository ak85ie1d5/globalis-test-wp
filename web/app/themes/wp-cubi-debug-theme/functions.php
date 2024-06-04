<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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


add_action('admin_post_export_to_excel', 'generateExcelFile');

function generateExcelFile() {
    global $wpdb;

    $event_id = intval($_GET['event_id']);

    $sql_query = $wpdb->prepare("SELECT `post_id` FROM %i WHERE `meta_key` = 'registration_event_id' AND `meta_value` = %d", $wpdb->postmeta, $event_id);
    $results = $wpdb->get_results($sql_query, ARRAY_A);

    // Include PhpSpreadsheet library
    require_once __DIR__ . '/../../../../vendor/autoload.php';

    // Create a new Spreadsheet object
    $spreadsheet = new Spreadsheet();

    // Add headers to the spreadsheet
    $spreadsheet->getActiveSheet()->setCellValue('A1', 'Nom');
    $spreadsheet->getActiveSheet()->setCellValue('B1', 'Prenom');
    $spreadsheet->getActiveSheet()->setCellValue('C1', 'Email');
    $spreadsheet->getActiveSheet()->setCellValue('D1', 'Téléphone');

    $row = 2;
    foreach ($results as $result) {
        $spreadsheet->getActiveSheet()->setCellValue('A' . $row, get_field('registration_last_name', $result['post_id']));
        $spreadsheet->getActiveSheet()->setCellValue('B' . $row, get_field('registration_first_name', $result['post_id']));
        $spreadsheet->getActiveSheet()->setCellValue('C' . $row, get_field('registration_email', $result['post_id']));
        $spreadsheet->getActiveSheet()->setCellValue('D' . $row, get_field('registration_phone', $result['post_id']));
        $row++;
    }

    // Output to browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="registration_data.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    exit;
}

<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';
$staffLabel = $shop ? $shop->resolved_staff_label : 'Stafi';

return [
  'ID' => 'ID',
  'Booking' => 'Rezervimi / Takimi',
  'Bookings' => 'Rezervimet & Takimet',
  'description' => 'Lista e rezervimeve dhe takimeve në kohë reale',
  'search_placeholder' => 'Kërko me ID, Shënime',
  'filter_shop_placeholder' => 'Filtro dyqanin',
  'filter_barber_placeholder' => 'Filtro stafin',
  'filter_service_placeholder' => 'Filtro shërbimin',
  'filter_customer_placeholder' => 'Filtro klientin',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Booking' => 'Shto Rezervim',
  'Edit Booking' => 'Ndrysho Rezervimin',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë rezervim.',
  'created' => 'Rezervimi u krye me sukses.',
  'updated' => 'Rezervimi u përditësua me sukses.',
  'deleted' => 'Rezervimi u fshi me sukses.',
  'not_found' => 'Rezervimi nuk u gjet.',
  'delete_error_referenced' => 'Rezervimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Barber Id' => $staffLabel,
  'Service Id' => 'Shërbimi',
  'Customer Id' => 'Klienti',
  'Appointment At' => 'Data & Ora',
  'Status' => 'Statusi',
  'Total Price' => 'Çmimi Total',
  'Notes' => 'Shënime',
  'Source' => 'Burimi',
];

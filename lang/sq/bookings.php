<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$staffLabel = $shop ? $shop->resolved_staff_label : 'Stafi';
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';

return [
  'ID' => 'ID',
  'Booking' => 'Rezervimi',
  'Bookings' => 'Rezervimet & Takimet',
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
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => 'Rezervimi u krijua me sukses.',
  'updated' => 'Rezervimi u përditësua me sukses.',
  'deleted' => 'Rezervimi u fshi me sukses.',
  'not_found' => 'Rezervimi nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Barber Id' => $staffLabel,
  'Service Id' => 'Shërbimi',
  'Customer Id' => 'Klienti',
  'Appointment At' => 'Orari i Takimit',
  'Status' => 'Statusi',
  'Total Price' => 'Çmimi Total (Lekë)',
  'Notes' => 'Shënime',
  'Source' => 'Burimi',
];

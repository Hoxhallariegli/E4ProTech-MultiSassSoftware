<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$staffLabel = $shop ? $shop->resolved_staff_label : 'Stafi';
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';

return [
  'ID' => 'ID',
  'Review' => 'Vlerësimi',
  'Reviews' => 'Vlerësimet e Klientëve',
  'Action' => 'Veprimet',
  'Reset' => 'Pastro',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Review' => 'Shto Vlerësim',
  'Edit Review' => 'Ndrysho Vlerësimin',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë vlerësim.',
  'created' => 'Vlerësimi u ruajt me sukses.',
  'updated' => 'Vlerësimi u përditësua me sukses.',
  'deleted' => 'Vlerësimi u fshi me sukses.',
  'not_found' => 'Vlerësimi nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Barber Id' => $staffLabel,
  'Customer Id' => 'Klienti',
  'Booking Id' => 'Takimi',
  'Rating' => 'Vlerësimi (Yjet 1-5)',
  'Comment' => 'Koment / Përshtypje',
];

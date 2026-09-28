<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';

return [
  'ID' => 'ID',
  'Payment' => 'Pagesa',
  'Payments' => 'Pagesat & Arkëtimet',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Payment' => 'Shto Pagesë',
  'Edit Payment' => 'Ndrysho Pagesën',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => 'Pagesa u regjistrua me sukses.',
  'updated' => 'Pagesa u përditësua me sukses.',
  'deleted' => 'Pagesa u fshi me sukses.',
  'not_found' => 'Pagesa nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Booking Id' => 'Rezervimi',
  'Amount' => 'Sasia (Lekë)',
  'Method' => 'Mënyra (Cash / Card)',
  'Status' => 'Statusi',
];

<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';

return [
  'ID' => 'ID',
  'Customer' => 'Klienti',
  'Customers' => 'Klientët',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Customer' => 'Shto Klient',
  'Edit Customer' => 'Ndrysho Klientin',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => 'Klienti u regjistrua me sukses.',
  'updated' => 'Klienti u përditësua me sukses.',
  'deleted' => 'Klienti u fshi me sukses.',
  'not_found' => 'Klienti nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Name' => 'Emri & Mbiemri',
  'Phone' => 'Telefoni',
  'Email' => 'Email',
  'Photo' => 'Foto',
  'Total Bookings' => 'Gjithsej Takime',
  'No Show Count' => 'Mosparaqitje',
];

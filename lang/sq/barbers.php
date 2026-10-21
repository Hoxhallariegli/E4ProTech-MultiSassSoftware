<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';
$staffLabel = $shop ? $shop->resolved_staff_label : 'Stafi';

return [
  'ID' => 'ID',
  'Barber' => $staffLabel,
  'Barbers' => 'Stafi & Parukierët',
  'description' => 'Lista e anëtarëve të stafit dhe berberëve',
  'search_placeholder' => 'Kërko me ID, Emër, Telefon, Bio',
  'filter_shop_placeholder' => 'Filtro sipas dyqanit',
  'filter_user_placeholder' => 'Filtro sipas përdoruesit',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Barber' => 'Shto Anëtar Stafi',
  'Edit Barber' => 'Ndrysho Anëtarin e Stafit',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë anëtar stafi.',
  'created' => 'Stafi u regjistrua me sukses.',
  'updated' => 'Stafi u përditësua me sukses.',
  'deleted' => 'Stafi u fshi me sukses.',
  'not_found' => 'Stafi nuk u gjet.',
  'delete_error_referenced' => 'Stafi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'User Id' => 'Llogaria e Përdoruesit',
  'Name' => 'Emri & Mbiemri',
  'Phone' => 'Telefoni',
  'Photo' => 'Foto',
  'Bio' => 'Biografia / Info',
  'Active' => 'Aktiv',
];

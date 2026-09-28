<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$staffLabel = $shop && !empty($shop->staff_label) ? $shop->staff_label : 'Punonjës';
$staffLabelPlural = $shop && !empty($shop->staff_label_plural) ? $shop->staff_label_plural : 'Punonjësit';
$shopLabel = $shop && !empty($shop->shop_label) ? $shop->shop_label : 'Dyqani';

return [
  'ID' => 'ID',
  'Barber' => $staffLabel,
  'Barbers' => $staffLabelPlural,
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Barber' => 'Shto ' . $staffLabel,
  'Edit Barber' => 'Ndrysho ' . $staffLabel,
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => $staffLabel . ' u krijua me sukses.',
  'updated' => $staffLabel . ' u përditësua me sukses.',
  'deleted' => $staffLabel . ' u fshi me sukses.',
  'not_found' => 'Regjistrimi nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera dhe nuk mund të fshihet.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'User Id' => 'Përdoruesi',
  'Name' => 'Emri & Mbiemri',
  'Phone' => 'Telefoni',
  'Photo' => 'Foto',
  'Bio' => 'Bio / Përshkrimi',
  'Active' => 'Aktiv',
];

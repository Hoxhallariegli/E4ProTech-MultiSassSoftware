<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop && !empty($shop->shop_label) ? $shop->shop_label : 'Dyqani';
$shopLabelPlural = $shopLabel === 'Dyqani' ? 'Dyqanet' : ($shopLabel === 'Salloni' ? 'Sallonet' : $shopLabel . 'e');

return [
  'ID' => 'ID',
  'BarberShop' => $shopLabel,
  'BarberShops' => $shopLabelPlural,
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add BarberShop' => 'Shto ' . $shopLabel,
  'Edit BarberShop' => 'Ndrysho ' . $shopLabel,
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => $shopLabel . ' u krijua me sukses.',
  'updated' => $shopLabel . ' u përditësua me sukses.',
  'deleted' => $shopLabel . ' u fshi me sukses.',
  'not_found' => 'Regjistrimi nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera dhe nuk mund të fshihet.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Owner Id' => 'Pronari',
  'Name' => 'Emri i Biznesit',
  'App Name' => 'Emri në Aplikacion',
  'Slug' => 'Slug (URL)',
  'Logo' => 'Logo',
  'Banner' => 'Banner',
  'Primary Color' => 'Ngjyra Kryesore',
  'Secondary Color' => 'Ngjyra Dytësore',
  'Trial Ends At' => 'Data e Përfundimit të Provës',
  'Expires At' => 'Data e Skadimit',
  'Active' => 'Aktiv',
  'Sms Enabled' => 'SMS Gateway Aktiv',
  'Timezone' => 'Zona Orare (Timezone)',
  'Max No Show Before Block' => 'Maks. Mosparaqitje para Bllokimit',
];

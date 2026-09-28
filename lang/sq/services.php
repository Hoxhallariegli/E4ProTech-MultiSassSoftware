<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';
$serviceLabel = $shop ? $shop->resolved_service_label : 'Shërbimi';

return [
  'ID' => 'ID',
  'Service' => $serviceLabel,
  'Services' => $serviceLabel === 'Shërbimi' ? 'Shërbimet' : $serviceLabel . 'et',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Service' => 'Shto ' . $serviceLabel,
  'Edit Service' => 'Ndrysho ' . $serviceLabel,
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => $serviceLabel . ' u krijua me sukses.',
  'updated' => $serviceLabel . ' u përditësua me sukses.',
  'deleted' => $serviceLabel . ' u fshi me sukses.',
  'not_found' => 'Regjistrimi nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Name' => 'Emri i Shërbimit',
  'Description' => 'Përshkrimi',
  'Price' => 'Çmimi (Lekë)',
  'Duration Minutes' => 'Kohëzgjatja (Minuta)',
  'Category' => 'Kategoria',
  'Active' => 'Aktiv',
];

<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';

return [
  'ID' => 'ID',
  'Service' => 'Shërbimi',
  'Services' => 'Shërbimet & Çmimet',
  'description' => 'Lista e shërbimeve dhe çmimeve të sallonit',
  'search_placeholder' => 'Kërko me ID, Emër, Përshkrim, Kategori',
  'filter_shop_placeholder' => 'Filtro dyqanin',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Service' => 'Shto Shërbim',
  'Edit Service' => 'Ndrysho Shërbimin',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë shërbim.',
  'created' => 'Shërbimi u krijua me sukses.',
  'updated' => 'Shërbimi u përditësua me sukses.',
  'deleted' => 'Shërbimi u fshi me sukses.',
  'not_found' => 'Shërbimi nuk u gjet.',
  'delete_error_referenced' => 'Shërbimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Name' => 'Emri i Shërbimit',
  'Description' => 'Përshkrimi',
  'Price' => 'Çmimi',
  'Duration Minutes' => 'Kohëzgjatja (Minuta)',
  'Category' => 'Kategoria',
  'Active' => 'Aktiv',
];

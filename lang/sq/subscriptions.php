<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$shopLabel = $shop ? $shop->resolved_shop_label : 'Salloni';

return [
  'ID' => 'ID',
  'Subscription' => 'Abonimi i Sallonit',
  'Subscriptions' => 'Abonimet e Salloneve',
  'description' => 'Lista e abonimeve të salloneve',
  'search_placeholder' => 'Kërko me ID',
  'filter_shop_placeholder' => 'Filtro dyqanin',
  'filter_plan_placeholder' => 'Filtro planin',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add Subscription' => 'Shto Abonim',
  'Edit Subscription' => 'Ndrysho Abonimin',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë abonim.',
  'created' => 'Abonimi u regjistrua me sukses.',
  'updated' => 'Abonimi u përditësua me sukses.',
  'deleted' => 'Abonimi u fshi me sukses.',
  'not_found' => 'Abonimi nuk u gjet.',
  'delete_error_referenced' => 'Abonimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Shop Id' => $shopLabel,
  'Plan Id' => 'Plani i Abonimit',
  'Starts At' => 'Fillon më',
  'Ends At' => 'Përfundon më',
  'Status' => 'Statusi',
  'Auto Renew' => 'Rinovim Automatik',
];

<?php

$shop = auth()->check() ? auth()->user()?->barberShop : null;
$staffLabel = $shop ? $shop->resolved_staff_label : 'Stafi';

return [
  'ID' => 'ID',
  'WorkingHour' => 'Orari i Punës',
  'WorkingHours' => 'Oraret e Punës & Pushimet',
  'Action' => 'Veprimet',
  'Reset' => 'Rifillo',
  'Filters' => 'Filtrat',
  'Search' => 'Kërko',
  'List of' => 'Lista e',
  'Save' => 'Ruaj',
  'Update' => 'Përditëso',
  'Add WorkingHour' => 'Shto Orar Pune',
  'Edit WorkingHour' => 'Ndrysho Orarin e Punës',
  'New record' => 'Regjistrim i ri',
  'Update info' => 'Përditëso informacionin',
  'No records found.' => 'Nuk u gjet asnjë regjistrim.',
  'created' => 'Orari u krijua me sukses.',
  'updated' => 'Orari u përditësua me sukses.',
  'deleted' => 'Orari u fshi me sukses.',
  'not_found' => 'Regjistrimi nuk u gjet.',
  'delete_error_referenced' => 'Regjistrimi po përdoret nga të dhëna të tjera.',
  'delete_error' => 'Nuk u realizua dot fshirja.',
  'Barber Id' => $staffLabel,
  'Day Of Week' => 'Dita e Javës',
  'Open Time' => 'Ora e Hapjes',
  'Close Time' => 'Ora e Mbylljes',
  'Lunch Start' => 'Pushim Dreke (Nga)',
  'Lunch End' => 'Pushim Dreke (Deri)',
  'Is Closed' => 'Pushim Javore',
];

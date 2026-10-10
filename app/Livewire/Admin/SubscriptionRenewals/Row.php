<?php

namespace App\Livewire\Admin\SubscriptionRenewals;

use App\Models\SubscriptionRenewal;
use Livewire\Component;

class Row extends Component { public SubscriptionRenewal $item; public function render() { return view('livewire.admin.subscription-renewals.row'); } }
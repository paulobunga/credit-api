<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Reseller.{id}', function ($m, $id) {
    return $m->id == $id;
});

Broadcast::channel('App.Models.Merchant.{id}', function ($m, $id) {
    return $m->id == $id;
});

Broadcast::channel('App.Models.Admin.{id}', function ($m, $id) {
  return $m->id == $id;
});
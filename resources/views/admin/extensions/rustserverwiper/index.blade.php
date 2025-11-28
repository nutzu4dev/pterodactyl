@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "rustserverwiper";
    $EXTENSION_NAME = stripslashes("Rust Server Wiper");
    $EXTENSION_VERSION = "v5.4";
    $EXTENSION_DESCRIPTION = stripslashes("With this Pterodactyl addon you can automatically schedule wipe your rust servers.");
    $EXTENSION_ICON = "/assets/extensions/rustserverwiper/icon.png";
    $EXTENSION_WEBSITE = "https://builtbybit.com/resources/pterodactyl-v1-addon-rust-server-wiper.25458/";
    $EXTENSION_WEBICON = "bi bi-tag-fill";
?>
@include('blueprint.admin.template')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    @yield('extension.header')
@endsection

@section('content')
    @yield('extension.config')
    @yield('extension.description')<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">Version</h3>
  </div>
  <div class="box-body">
    <p>
      You are currently using version <code>v5.4</code> of the <b>Rust Server Wiper</b>.
    </p>
  </div>
</div>
@endsection

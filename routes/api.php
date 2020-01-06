<?php

Route::post('saveCustomer','CustomerController@store');
Route::get('getCustomers','CustomerController@index');
Route::get('getCustomer/{id}','CustomerController@show');
Route::get('validarXML','CustomerController@validarXML');

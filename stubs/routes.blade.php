@if (in_array('C', $options))
Route::post('{{$entities}}', [{{$entity}}Controller::class, 'store'])->name('{{$entities}}.store');
@endif
@if (in_array('U', $options))
Route::put('{{$entities}}/{id}', [{{$entity}}Controller::class, 'update'])->name('{{$entities}}.update');
@endif
@if (in_array('D', $options))
Route::delete('{{$entities}}/{id}', [{{$entity}}Controller::class, 'delete'])->name('{{$entities}}.delete');
@endif
@if (in_array('R', $options))
Route::get('{{$entities}}/{id}', [{{$entity}}Controller::class, 'show'])->name('{{$entities}}.show');
Route::get('{{$entities}}', [{{$entity}}Controller::class, 'index'])->name('{{$entities}}.index');
Route::get('{{$entities}}', [{{$entity}}Controller::class, 'search'])->name('{{$entities}}.search');
@endif
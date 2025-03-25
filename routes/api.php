<?php

use App\Http\Controllers\NoteController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;


Route::apiResource('/notes', NoteController::class);

Route::get('/notes-with-users', [NoteController::class, 'notesWithUsers']);
Route::get('/users-with-note-count', [NoteController::class, 'usersWithNoteCount']);
Route::get('/search-notes', [NoteController::class, 'searchNotes']);

Route::get('/users-with-notes-count', [NoteController::class, 'usersWithNotesCount']);
Route::get('/longest-and-shortest-note', [NoteController::class, 'longestAndShortestNote']);
Route::get('/notes-last-week', [NoteController::class, 'notesLastWeek']);


Route::apiResource('categories', CategoryController::class);
Route::get('categories/name/{name}', [CategoryController::class, 'getByName']);
## About project
<p>Example of use FullCalendar in Laravel 9 framework with Bootstrap 5. </p>
<p>with:
<ul>
<li>ajax requests,</li>
<li>colored events,</li>
<li>drag and drop,</li> 
<li>resizing events,</li>
<li>...and more ...in the future</li>
</ul>

<img src="https://raw.githubusercontent.com/andrzejkar/andrzejkar.github.io/main/fc_l_b.png" width="480px">

1. Install Laravel
2. Clone this repository
3. Run migration to create Events table
4. Create Routes:
```
Route::get('/', [CalendarController::class, 'index'])->name('calendar.index');
Route::get('calendarAjax', [CalendarController::class, 'ajax'])->name('calendar.ajax');
Route::post('calendarAjax', [CalendarController::class, 'ajax']);
```
5. php artisan serve

## License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

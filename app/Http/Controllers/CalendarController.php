<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\View\View;


class CalendarController extends Controller
{
    public function index(): View
    {
        $color = [
            'one' => '#3A87AD',
            'two' => '#ff0000',
            'tree' => '#0F9111',
            'four' => '#b700d3',
        ];

        return view('calendar', compact('color'));
    }

    public function ajax(Request $request): JsonResponse
    {
        switch ($request->type) {
            case 'form':
                $allDay = 0;
                if ($request->allDay) {
                    $start = date('Y-m-d H:i:s', strtotime($request->start));
                    $end = date('Y-m-d H:i:s', strtotime($request->end.' +1 day'));
                    $allDay = 1;
                } else {
                    $start = $request->start;
                    $end = $request->end;
                }
                $event = Event::create([
                    'title' => $request->title,
                    'start' => $start,
                    'end' => $end,
                    'allDay' => $allDay,
                    'color' => $request->color,
                ]);
                return response()->json($event);

            case 'add':
                $request->allDay == 1 ? $allDay = 1 : $allDay = 0;

                $event = Event::create([
                    'title' => $request->title,
                    'start' => $request->start,
                    'end' => $request->end,
                    'allDay' => $allDay,
                ]);
                return response()->json($event);

            case 'update':
                if ($request->allDay === 'true' || $request->allDay === '1') {
                    $allDay = 1;
                } else {
                    $allDay = 0;
                }

                $event = Event::find($request->id);
                $event->update([
                    'title' => $request->title,
                    'start' => $request->start,
                    'end' => $request->end,
                    'allDay' => $allDay,
                    'color' => $request->color ? $request->color : $event->color,
                ]);

                return response()->json($event);

            case 'delete':
                $event = Event::find($request->id);
                $event->delete();
                return response()->json($event);

            default:
                $data = Event::all();
                return response()->json($data);
        }
    }
}

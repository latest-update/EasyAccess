<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Custom\ShortResponse;
use App\Models\Card;
use App\Models\Gate;
use App\Models\Record;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GateController extends Controller
{
    public function records (Request $request): JsonResponse
    {
        $records = $request->user()->records();
        $data['records'] = $records->get(['entry_time', 'exit_time', 'summary']);
        $data['summary_minute'] = collect($records->get())->map(function ($item) {
            return $item->summary;
        })->sum();

        return ShortResponse::json(true, 'All records retrieved', $data );
    }

    public function interval (Request $request): JsonResponse
    {
        $interval = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after:from'
        ]);

        $records = $request->user()->records()->whereBetween('entry_time', [$interval['from'], $interval['to']]);
        $data['records'] = $records->get(['entry_time', 'exit_time', 'summary']);
        $data['summary_minute'] = collect($records->get())->map(function ($item) {
            return $item->summary;
        })->sum();

        return ShortResponse::json(true, 'All records by interval retrieved', $data);
    }

    public function scan (Request $request): JsonResponse
    {
        $card = $request->validate([
            'serial' => 'required|integer',
            'token' => 'required|string'
        ]);

        $data = Card::query()->where('serial', $card['serial'])->with(['user:id,card_id,name'])->get()[0];

        if($card['token'] != $data['token'])
            return ShortResponse::json('false', 'Not valid ID card', ['Advice' => 'You should ask for help to WorkCenter or write email@gmail.com']);

        $user = User::find($data['user']['id']);

        if ($user->gate()->get()->isEmpty())
            return $this->entry($user);

        return $this->exit($user);
    }

    private function entry (User $user): JsonResponse
    {
        $gate['user_id'] = $user['id'];
        $gate['entry_time'] = now()->format('Y-m-d H:i:s');

        Gate::create($gate);

        return ShortResponse::json('true', 'Entry time has recorded', $gate);
    }

    private function exit (User $user): JsonResponse
    {
        $gate = $user->gate()->get()[0];
        $record['user_id'] = $user->id;
        $record['entry_time'] = $gate->entry_time;
        $record['exit_time'] = now()->format('Y-m-d H:i:s');

        // Difference
        $start = new Carbon($record['entry_time']);
        $end = new Carbon($record['exit_time']);
        $record['summary'] = $start->diffInMinutes($end);

        $record = Record::create($record);
        $gate->delete();

        return ShortResponse::json(true, 'Your time at the work has successfully recorded', $record);
    }
}

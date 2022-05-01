<?php

namespace App\Http\Controllers;

use App\Events\TcQR;
use App\Http\Controllers\Custom\ShortResponse;
use App\Models\Card;
use App\Models\Gate;
use App\Models\Record;
use App\Models\Tourniquet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GateController extends Controller
{

    public static function createTtId(Tourniquet $gate): Tourniquet
    {
        $gate->ttid = Str::random(15);
        $gate->save();
        return $gate;
    }

    public function qr_scan(Request $request, string $ttid): JsonResponse
    {
        $gate = Tourniquet::first();
        if($gate->ttid != $ttid) {
            event(new TcQR($gate->ttid, null, null, null));
            return ShortResponse::json(['message' => 'Not valid QR'], 400);
        }

        $gate = static::createTtId($gate);

        $user = $request->user();
        if ($user->gate()->get()->isEmpty())
            $time = $this->entry($user);
        else
            $time = $this->exit($user);

        event(new TcQR($gate->ttid, $user->name, $time['entry_time'], isset($time['exit_time']) ? $time['exit_time'] : null));

        return ShortResponse::json(['message' => "Time has recorded", 'entry_time' => $time['entry_time'], 'exit_time' => isset($time['exit_time']) ? $time['exit_time'] : null]);
    }

    public function records (Request $request): JsonResponse
    {
        $records = $request->user()->records();
        $data['records'] = $records->get(['entry_time', 'exit_time', 'summary']);
        $data['summary_minute'] = collect($records->get())->map(function ($item) {
            return $item->summary;
        })->sum();

        return ShortResponse::json($data);
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

        return ShortResponse::json($data);
    }

    public function scan (Request $request): JsonResponse
    {
        $card = $request->validate([
            'serial' => 'required|integer',
            'token' => 'required|string'
        ]);

        $data = Card::query()->where('serial', $card['serial'])->with(['user:id,card_id,name'])->get()[0];

        if($card['token'] != $data['token'])
            return ShortResponse::json(['message' => 'Invalid ID card', 'advice' => 'You should ask for help to WorkCenter or write email@gmail.com']);

        $user = User::find($data['user']['id']);

        if ($user->gate()->get()->isEmpty())
            return ShortResponse::json($this->entry($user));

        return ShortResponse::json($this->exit($user));
    }

    private function entry (User $user): array
    {
        $gate['message'] = 'Entry time has recorded';
        $gate['user_id'] = $user['id'];
        $gate['entry_time'] = now()->format('Y-m-d H:i:s');

        Gate::create($gate);

        $gate['user_name'] = $user['name'];
        return $gate;
    }

    private function exit (User $user): array
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

        unset($record['id']);
        $record['message'] = 'Your time at the work has successfully recorded';
        $record['user_name'] = $user['name'];
        return $record->toArray();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class activityController extends Controller
{
    public function show()
    {
        $res = Http::withToken('ODQ6OUpnTFlka2VObFl3aU1YQ3FFYksxRUVvbFc1R0Z2QnR6Wm1lVmJ6ejlPZz0')->get('https://api.slangapp.com/challenges/v1/activities')->json();
        $res = collect($res['activities']);
        $res = $res->sortByDesc('first_seen_at')->groupBy('user_id');
        $res = $res->map(function ($data){
            return $data->map(function ($item){
                $st = Carbon::parse($item['first_seen_at']);
                $en = Carbon::parse($item['answered_at']);
                $du = $en->diffInSeconds($st);
                $fin = [
                    'id'=>$item['id'],
                    'started_at'=>$item['first_seen_at'],
                    'end_at'=>$item['answered_at'],
                    'day'=>$st->format('d')
                ];
                return $fin;
            });
        })->map(function ($q){
            return collect($q)->groupBy('day')->map(function ($q){
                $st = $q->min('started_at');
                $en = $q->max('end_at');
                $du = Carbon::parse($en)->diffInSeconds($st);
                return ['started_at'=>$st,'ended_at'=>$en,'duration_seconds'=>$du,'activity_ids'=>$q->pluck('id')];
            })->values();
        });

//        $post = Http::withToken('ODQ6OUpnTFlka2VObFl3aU1YQ3FFYksxRUVvbFc1R0Z2QnR6Wm1lVmJ6ejlPZz0')->withHeaders(['Content-Type'=>'application/json'])
//            ->post('https://api.slangapp.com/challenges/v1/activities/sessions',["user_sessions"=>$res->toArray()]);
//        return response()->json($post->status());
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StockData extends Controller
{   
    public function __construct(){
        $this->api_key = 'Tpk_cf727bdaeabf459d898584729da812d5'; //Key para api SandBox, como é para sandbox free resolvi não colocar no ENV.
    }
    
    public function retrieve(String $cod){

        //Requisição
        $url = 'https://sandbox.iexapis.com/stable/tops?token='.$this->api_key.'&symbols='.$cod;
        $response = Http::get($url);
        $dateTime = Carbon::now()->format('d/m/Y H:i:s');

        //Resgata o valor da paridade BRL/USD em API gratuita
        $realDolar = Http::get('http://economia.awesomeapi.com.br/json/last/USD-BRL')->json()['USDBRL']['low'];

        //Response
        if(count($response->json()) > 0){
            $data = $response->json()[0];
            $data['bidPrice'] = round($data['bidPrice'] * $realDolar, 2);
            $data['askPrice'] = round($data['askPrice'] * $realDolar, 2);
            return response()->json([
                'response' => $data,
                'date' => $dateTime,
                'status'=> true
            ]);
        }else{
            return response()->json([
                'response' => 'Stock não encontrada!',
                'date' => $dateTime,
                'status'=> false
            ]);
        }
        
    }

    public function index(){
        $mainstocks = ['aapl', 'fb', 'twtr', 'ko'];

        $stocks = [];

        //Resgata o valor da paridade BRL/USD em API gratuita
        $realDolar = Http::get('http://economia.awesomeapi.com.br/json/last/USD-BRL')->json()['USDBRL']['low'];

        foreach($mainstocks as $stock){
            $url = 'https://sandbox.iexapis.com/stable/tops?token='.$this->api_key.'&symbols='.$stock;
            $response2 = Http::get($url);
            array_push($stocks, $response2->json()[0]);
        }
        //dd($stocks); debug
        return view('StockPrice', ['stocks'=>$stocks, 'realDolar' => $realDolar]);
    }
}

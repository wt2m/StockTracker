<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\Stock;

class StockData extends Controller
{   
    public function __construct(){
        $this->api_key = 'Tpk_cf727bdaeabf459d898584729da812d5'; //Key para api SandBox, como é para sandbox free resolvi não colocar no ENV.
    }
    
    public function retrieve(String $cod, $refresh){

        //Verifica se o ativo já está cadastrado no sistema
        $stock = Stock::where('symbol', $cod)->first();
        
        if(isset($stock) && $refresh == 'false'){//Caso a stock já esteja adicionada e não seja uma requisição para refresh, retorna os dados
            return response()->json([
                'response' => $stock,
                'date' => $stock->datetime,
                'status'=> true
            ]);
        } 
        
       

        //Requisição
        $url = 'https://sandbox.iexapis.com/stable/tops?token='.$this->api_key.'&symbols='.$cod;
        $response = Http::get($url);
        $dateTime = Carbon::now()->format('d/m/Y H:i:s');

        //Resgata o valor da paridade BRL/USD em API gratuita
        $realDolar = Http::get('http://economia.awesomeapi.com.br/json/last/USD-BRL')->json()['USDBRL']['low'];

        //Response
        if(count($response->json()) > 0){
            $data = $response->json()[0];
            
            if(!isset($stock)){
                $stock = new Stock(); //Cria novo objeto stock
            }
            //Define parametros do objeto stock
            $stock->symbol      = $data['symbol'];
            $stock->lastPrice   = round($data['lastSalePrice'] * $realDolar, 2);
            $stock->bidPrice    = round($data['bidPrice'] * $realDolar, 2);
            $stock->askPrice    = round($data['askPrice'] * $realDolar, 2);
            $stock->sector      = $data['sector'];
            $stock->volume      = $data['volume'];
            $stock->datetime    = $dateTime;
            $stock->save(); //insere registro

            return response()->json([
                'response' => $stock,
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

        //Resgata o valor da paridade BRL/USD em API gratuita
        $realDolar = Http::get('http://economia.awesomeapi.com.br/json/last/USD-BRL')->json()['USDBRL']['low'];

        //Caso existam stocks salvas no banco:
        $stocks = Stock::all()->take(6);
        //Caso não existam stocks registradas, resgata 4 das principais stocks
        if(count($stocks) == 0){
            $mainstocks = ['aapl', 'fb', 'twtr', 'ko'];

            $stocks = [];

            $dateTime = Carbon::now()->format('d/m/Y H:i:s');//para registrar no banco
            foreach($mainstocks as $stock){
                $url = 'https://sandbox.iexapis.com/stable/tops?token='.$this->api_key.'&symbols='.$stock;
                $response2 = Http::get($url);
                $data = $response2->json()[0];
                //Salva no banco
                $stock              = new Stock();
                $stock->symbol      = $data['symbol'];
                $stock->lastPrice   = round($data['lastSalePrice'] * $realDolar, 2);
                $stock->bidPrice    = round($data['bidPrice'] * $realDolar, 2);
                $stock->askPrice    = round($data['askPrice'] * $realDolar, 2);
                $stock->sector      = $data['sector'];
                $stock->volume      = $data['volume'];
                $stock->datetime    = $dateTime;
                $stock->save();
                array_push($stocks, $data);
            }
        }
        
        //dd($stocks); debug

        //Retorna a view index
        return view('StockPrice', ['stocks'=>$stocks, 'realDolar' => $realDolar]);
    }
}

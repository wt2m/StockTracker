<html lang="en">
<head>
    <style>
        .card {
            border: none;
            padding-bottom: 10px;
        }


        .btn {
            height: 50px
        }
    </style>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

</head>
<body class='bg-light'>
    
    <div class="container mt-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-9">
                <div class="card px-5 py-5" id="form">
                    <div class="form-data">
                        <div class="form-group row justify-content-end">
                            {{-- COMPONENTE PARA INSERIR O VALOR! --}}
                            <div class="input-group col-md-4">
                                <input type="text" id="cod" name="cod" placeholder="Código da stock" class="form-control"/>
                                <div class="input-group-append">
                                    <button id="btn-search" class="input-group-btn btn-primary">🔍</button> 
                                </div>
                            </div>
                        </div>
                        <hr>
                        {{--Alguns cards para preencher a tela inicial--}}
                        <div class="row justify-content-center" id="initial">
                            @foreach($stocks as $stock)
                                <div class="card col-md-5">
                                    <div class="card-body">
                                        <h5 class="card-title">{{$stock['symbol']}} <small>R${{isset($stock['lastPrice']) ? $stock['lastPrice'] : $stock['lastSalePrice']}}</small></h5>
                                        <div class="card-text">
                                            <p class="text-success">Compra: <b>R${{round($stock['bidPrice'] * $realDolar, 2)}}</b></p>
                                            <p class="text-danger">Venda: <b>R${{round($stock['bidPrice'] * $realDolar, 2)}}</b></p>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{--Dados de pesquisa de stock específica--}}
                        <div id="stock">
                                <div class="justify-content-end row ml-2">
                                    <button id="refresh" class="btn btn-primary col-md-3">⟲</button>
                                </div>
                                <div class="row align-items-end mb-3">
                                    <h1 id="symbol" class="col-md-3">
                                    <h1 id="price" class="text-success">
                                    
                                </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p id="bid-price"></p>
                                    <p id="ask-price"></p>
                                </div>
                                <div>
                                    <p id="sector"></p>
                                    <p id="volume"></p>
                                </div>
                            </div>
                            
                            <hr>
                            <div class="justify-content-end row ">
                                <div class="col-md-5 custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="self-refresh" id="self-refresh">
                                    <label class="custom-control-label" for="self-refresh">Atualizar automaticamente</label>
                                    <br>
                                    <small id="last-update"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</body>
</html>


    <script type="text/javascript">
        $( document ).ready(function() {
            $('#stock').hide();
            var stock = '';
            var retrieve = false;
            $("#btn-search").click(function(){ //Requisição para stock específica
                stock = $('#cod').val();
                retrieveStockData(stock);
                //esconde o componente inicial
                $('#initial').hide();
                //exibe o componente de ação unica
                $('#stock').show();
            });

            $('#refresh').click(function(){
                retrieveStockData(stock, true, true)
            });

            $('#self-refresh').click(function() { //Habilitando função de self-refresh
                if ($(this).is(':checked')) {
                    $('#refresh').hide();
                    retrieve = true;
                }else{
                    $('#refresh').show();
                    retrieve = false;
                }
            });

    
            window.setInterval(function(){
                retrieveStockData(stock, retrieve, true);
            }, 5000);; //função para atualizar a cada 5 segundos

        
        });
        function retrieveStockData(stock, retrieve = true /*variavel para permitir atualizacao autmatica*/, refresh = false){
            if(stock != '' && retrieve == true){
                $.ajax({
                    url       : "/StockData/"+stock+"/"+refresh,
                    type      : 'get',
                    async     : true,
                    success   : function(resp){
                        if (resp.status == true) {
                            //substitui os valores
                            $('#symbol').text(resp.response.symbol)
                            $('#price').text('R$'+resp.response.lastPrice)
                            $('#bid-price').text('Compra: R$'+resp.response.bidPrice);
                            $('#ask-price').text('Venda: R$'+resp.response.askPrice);
                            $('#sector').text('Setor: ' + resp.response.sector);
                            $('#volume').text('Volume: ' + resp.response.volume);
                            $('#last-update').text('Ultima atualização:'+resp.date)
                        } else {
                            alert(resp.response);
                            return false;
                        }
                    }
                });
            }
            
        }
    </script>

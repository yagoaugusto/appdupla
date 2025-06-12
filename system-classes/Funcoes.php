<?php

function resposta($resposta){
  echo
  '
  <div class="col-md-12">
  <div class="alert  '.$resposta[0].' alert-dismissible fade show" role="alert">
  '.$resposta[1].'
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  </div>
  ';
}

function bcolor($status,$estilo){
  if ($status=='ATIVO') {
    return 'bcolor-success-'.$estilo;
  }else{
    return 'bcolor-danger-'.$estilo;
  }
}

function checked($marcador,$status){
  if ($marcador == $status) {
    return 'checked';
  }else{
  }
}


function data($data){
  if($data=="0000-00-00" || $data==""){
    echo "AGUARDANDO";
  }else{
    $dataeditada = date('d/m/Y', strtotime($data));
    echo $dataeditada;
  }
}

function datat2($data){
  if($data==!""){
    $dataeditada = date('d/m', strtotime($data));
    echo $dataeditada;
  }else{
    echo "Aguardando";
  }
}

function data2($data){
  if($data==!""){
    $dataeditada = date('d/m/Y', strtotime($data));
    return $dataeditada;
  }else{
    return "Aguardando";
  }
}

function semana($data){
  if($data==!""){
    $dataeditada = date('W', strtotime($data));
    return $dataeditada;
  }else{
    return "Aguardando";
  }
}

function momento($data){
  $dataeditada = date('d/m/Y H:i', strtotime($data));
  return $dataeditada;
}

function data_prog_min(){
  $dataeditada = date('Y-m-d');
  $dataeditada = date('Y-m-d',strtotime("+0 days", strtotime($dataeditada)));
  return $dataeditada;
}

function data_prog_max(){
  $dataeditada = date('Y-m-d');
  $dataeditada = date('Y-m-d',strtotime("+30 days", strtotime($dataeditada)));
  return $dataeditada;
}

function prazo($data,$dias){
  $dataeditada = date($data);
  $dataeditada = date('d/m',strtotime("+ $dias days", strtotime($dataeditada)));
  return $dataeditada;
}

function diferenca_dias($inicio,$dias){
  $hoje = date('Y-m-d');
  $fim = date($inicio);
  $fim = date('Y-m-d',strtotime("+ $dias days", strtotime($fim)));
  $diferenca = strtotime($fim) - strtotime($hoje);
  $dias = floor($diferenca / (60 * 60 * 24));
  return $dias;
}

function dias_vencimento($fim){
  $hoje = date('Y-m-d');
  $fim = date($fim);
  $diferenca = strtotime($fim) - strtotime($hoje);
  $dias = floor($diferenca / (60 * 60 * 24));
  return $dias;
}

function valor($valor){
  $valorFormatado = str_replace("R$", "", $valor);
  $valorFormatado = str_replace(".", "", $valorFormatado);
  $valorFormatado = str_replace(",", ".", $valorFormatado);
  $valor = floatval($valorFormatado);
  return $valor;
}

function porcen($valor){
  $numeroporcentagem = number_format($valor, 2, ',', '.');
  echo $numeroporcentagem." %";
}

function fin($valor){
  $numeroporcentagem = number_format($valor, 2, ',', '.');
  echo "R$".$numeroporcentagem;
}

function mlog(){ // DIA E HORA ATUAL
  date_default_timezone_set('America/Sao_Paulo');
  $log = date('Y-m-d H:i:s');
  return $log;
}

function mlog3(){ // DIA E HORA ATUAL
  date_default_timezone_set('America/Sao_Paulo');
  $log = date('Y-m-d');
  return $log;
}

function dlog(){ // DIA E HORA ATUAL
  date_default_timezone_set('America/Sao_Paulo');
  $log = date('d/m/Y');
  return $log;
}

function dlog2(){ // DIA E HORA ATUAL
  date_default_timezone_set('America/Sao_Paulo');
  $log = date('dmy');
  return $log;
}

function mlog2(){ // DIA E HORA ATUAL
  date_default_timezone_set('America/Sao_Paulo');
  $log = date('d/m/Y H:i');
  return $log;
}


function risco($valor,$risco){
  if ($valor==$risco) {
    echo "style='text-decoration: line-through;'";
  }
}

function cor($valor){
  if ($valor==0) {
    echo "danger";
  }else{
    echo "success";
  }
}

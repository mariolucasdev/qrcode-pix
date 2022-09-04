<?php

require __DIR__ . '/../vendor/autoload.php';

use mariolucasdev\QrCodePix\QrCodePix;

$data = array(
    'key' => '', // Sua chave Pix
    'amount' => 0, // Valor em Float
    'description' => '', // Descrição do Pagamento
    'name' => '', // Nome do Recebedor
    'city' => '' // Cidade + UF (Araripina-PE)
);

$pix = (new QrCodePix)->generate($data);

echo $pix['pix'];
echo '<br><br>';
echo '<img src="'.$pix['image'].'"> <br>';

echo '<pre>';
print_r($pix);
echo '</pre>';
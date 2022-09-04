# QRCode Pix

Lib para geração de QrCode do Pix e Chave Copia e Cola.

## Instalação via composer
```php
composer require mariolucasdev/qrcode-pix
```

## Usando a lib
```php
require __DIR__ . '/vendor/autoload.php';

use mariolucasdev\QrCodePix;

$data = array(
    'key' => '', // Chave pix
    'amount' => 0, // Valor em Float
    'description' => '', // Descrição do Pagamento
    'name' => '', // Nome do Recebedor
    'city' => '' // Cidade + UF (Araripina-PE)
);

$pix = (new QrCodePix\QrCodePix)->generate($data);

print_r($pix);
```

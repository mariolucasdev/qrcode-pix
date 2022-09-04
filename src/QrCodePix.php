<?php

namespace mariolucasdev\QrCodePix;

require __DIR__.'/../vendor/autoload.php';

use chillerlan\QRCode\{QRCode};

/**
 * Class QrCodePix
 *
 * @filesource   QrCodePix.php
 * @created      03.09.2021
 * @package      mariolucasdev\QRCodePix
 * @author       Mário Lucas <mariolucasdev@gmail.com>
 * @copyright    2022 Mário Lucas
 * @license      MIT
 */
class QrCodePix {

    /**
     * Generate Pix Code and QrCode Image
     * @param array $dataPix $key | $description | $amount | $name | $city
     * @return array $pix[ code, qrcode ]
     */
    public function generate($dataPix)
    {
        extract($dataPix);

        if(!$key):
            die('Chave pix inexistente ou inválida!');
        endif;
        
        if(!$amount):
            die('Entre com um valor válido!');
        endif;
        
        if(!$name):
            die('Entre com o nome do beneficiário!');
        endif;

        $pix = array(
            00 => '01',
            01 => '12',
            26 => array(
                00 => 'BR.GOV.BCB.PIX',
                01 => $key,
                02 => $description,
            ),
            52 => '0000',
            53 => '986',
            54 => (float) $amount,
            58 => 'BR',
            59 => $name,
            60 => $city,
            62 => array(
                05 => '***'
            )
        );

        $pix = $this->mountPixCode($pix);
        $pix .= "6304";
        $pix .= $this->checkCrcSum($pix);

        $pix = array(
            'pix' => $pix,
            'image' => (new QRCode)->render($pix)
        );
        
		return $pix;
    }

    private function mountPixCode($pix)
    {
        $ret="";
        
        foreach ($pix as $key => $value) {
            if (!is_array($value)) {
                if ($key == 54) { $value=number_format($value,2,'.',''); } // Formata o campo valor com 2 digitos.
                else { $value=$this->removeCharSpecials($value); }
                $ret.=$this->addSecoundNumber($key).$this->checkLength($value).$value;
            } else {
                $conteudo=$this->mountPixCode($value);
                $ret.=$this->addSecoundNumber($key).$this->checkLength($conteudo).$conteudo;
            }
        }

        return $ret;
    }

    private function removeCharSpecials($value) :string
    {
        return preg_replace('/\W /','', $this->removeAccents($value));
    }

    private function removeAccents($code) :string
    {
        $search = explode(",","à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
        $replace =explode(",","a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");
        return $this->removeEmoji(str_replace($search, $replace, $code));
    }

    private function removeEmoji($code) :string
    {
        return preg_replace('%(?:
            \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )%xs', '  ', $code); 
    }

    private function checkLength($code)
    {
        if (strlen($code) > 99) {
            die("Tamanho máximo deve ser 99, inválido: $code possui " . strlen($code) . " caracteres.");
        }

        return $this->addSecoundNumber(strlen($code));
    }

    private function addSecoundNumber($number)
    {
        return str_pad($number, 2, "0", STR_PAD_LEFT);
    }

    public function checkCrcSum($pix)
    {
        $crc = 0xFFFF;
        $strlen = strlen($pix);
        for($c = 0; $c < $strlen; $c++) {
            $crc ^= $this->charCodeAt($pix, $c) << 8;
            for($i = 0; $i < 8; $i++) {
                if($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }

        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);
        $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);

        return $hex;
    }

    private function charCodeAt($str, $i)
    {
        return ord(substr($str, $i, 1));
    }
}
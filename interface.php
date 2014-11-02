<?php
    header('Content-type: text/html; charset=GBK');
    include 'aes.class.php';
    $aesMod = new aes();
    
    $url = "http://218.206.69.70/imarket/fc/saleCards";
    $key = '*rGsOVFkKTIA7tPm';
    
    $rand  = substr(microtime(),2,5);
    $channelid = '3000023';
    $transacttionid = $channelid.'00'.date('YmdHis').$rand;
    $data_array = array(
        'TransactionId' => $transacttionid,
        'ChannelId' => $channelid,
    );

    $xmlData = getSendXmlData('QuerySaleCardReq', $data_array);
    var_dump($xmlData);
    $xmlResult = sendXml($url, $xmlData);
    var_dump($xmlResult);
    function getSendXmlData($title, $data_array, $flag = 1) {
        //��������sign�����xml���ݸ�ʽ
        $xmlData = createXml($title, $data_array, $flag);
        //�滻���� <?xml version="1.0" encoding="GBK"  �ⲿ�������ڼ��ܵ�ʱ����Ҫ
        /*         * $Md5Str = md5(str_replace('<?xml version="1.0" encoding="GBK"?>', '', $xmlData)); */
        $signMd5Str = getsign($data_array);
        //��AES���� ��������Ҫ������ǩ��
        //�������Ҫ���͵�xml����
        $addSign = array('Sign' => $signMd5Str);
        $data_array1 = array_merge($data_array, $addSign);
        return createXml($title, $data_array1, $flag);
    }
    
    function createXml($title, $data_array, $flag = 1) {
        //  ����һ��XML�ĵ�������XML�汾�ͱ��롣��
        $dom = new DomDocument('1.0', 'GBK');
        $Request = $dom->createElement($title);
        $dom->appendchild($Request);
        if ($flag == 1) {
            //  �������ڵ�
            create_item1($dom, $Request, $data_array);
        } else {
            create_item2($dom, $Request, $data_array);
        }


        return $dom->saveXML();
    }
    
    function create_item1($dom, $Request, $data) {

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                //  ����Ԫ��
                $$key = $dom->createElement($key);
                $Request->appendchild($$key);
                //  ����Ԫ��ֵ
                $text = $dom->createTextNode($val);
                $$key->appendchild($text);
            }
        }
    }
    function create_item2($dom, $Request, $data) {

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    //  ����Ԫ��
                    $item = $dom->createElement($key);
                    $secxml = $Request->appendchild($item);
                    foreach ($val as $ke => $keval) {
                        $xmlItem = $dom->createElement($keval);
                        $secxml->appendchild($xmlItem);
                        $texts = $dom->createTextNode($ke);
                        $xmlItem->appendchild($texts);
                    }
                } else {
                    $$key = $dom->createElement($key);
                    $Request->appendchild($$key);
                    //  ����Ԫ��ֵ
                    $text = $dom->createTextNode($val);
                    $$key->appendchild($text);
                }
            }
        }
    }
    
    function sendXml($url, $xmlData) {

//$url = 'http://wang.net/xml/getXml.php';  //����xml���ݵ��ļ�

$header[] = "Content-type: text/xml; charset=GBK"; 
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch); //���ش���
        }
        curl_close($ch);
        return $response;
    }
    
    function getsign($data_array) {
        global $aesMod;
        $temp_data = array_flip($data_array);
        natsort($temp_data);
        $data_array = array_flip($temp_data);
        $urldata = http_build_query($data_array);
        //���ַ���ת��ΪGBK;
        $gbkstr = mb_convert_encoding($urldata, 'GBK');
        //��GBK�ַ���ת��Ϊ������
        //$binstr = md5(pack('i', $gbkstr));
        $binstr = md5(StrToBin($gbkstr));
        $signMd5Str = $aesMod->encryptString($binstr, $key);
        return StrToHex($signMd5Str);
        //pack ת���ɶ����ƻ���ʮ������
        //return pack('h', $signMd5Str);
       // return pack('s', $signMd5Str);
        //return bin2hex($signMd5Str);
    }
        
    function StrToBin($str){

        $bin = '';
        for($i = 0 ; $i < strlen($str) ; $i++) {
                $bin .= decbin(ord($str{$i}));
        }
        return $bin;
        /*
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        foreach($arr as &$v){
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }
        return join('',$arr);
         */
    }
       
    function StrToHex($str)
    { 
        $hex="";
        for($i=0;$i<strlen($str);$i++)
        $hex.=dechex(ord($str[$i]));
        $hex=strtoupper($hex);
        return $hex;
    }  
    
    




?>